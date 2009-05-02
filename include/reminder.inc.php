<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

// Base class for a reminder; it offers the factory for creating valid reminders
// tailored for a given user, as well as base methods for reminder impls.
// Sub-classes should define at least the abstract methods, and the static
// IsCandidate method (prototype: (User &$user)).
//
// Usage:
//   // Instantiates and returns a valid Reminder object for the user.
//   $reminder = Reminder::GetCandidateReminder($user);
//
//   // Returns the named Reminder object.
//   $reminder = Reminder::GetByName($user, 'ax_letter');
abstract class Reminder
{
    // Details about the reminder.
    public $name;
    protected $type_id;
    protected $weight;
    protected $remind_delay_yes;
    protected $remind_delay_no;
    protected $remind_delay_dismiss;

    // Details about the user.
    protected $user;
    protected $current_status;
    protected $last_ask;

    // Constructs the Reminder object from a mandatory User instance, a list of
    // key-value pairs from the `reminder_type` and `reminder` tables.
    function __construct(User &$user, array $type)
    {
        $this->user = &$user;

        $this->type_id              = $type['type_id'];
        $this->name                 = $type['name'];
        $this->weight               = $type['weight'];
        $this->remind_delay_yes     = $type['remind_delay_yes'];
        $this->remind_delay_no      = $type['remind_delay_no'];
        $this->remind_delay_dismiss = $type['remind_delay_dismiss'];

        if (isset($type['status'])) {
            $this->current_status = $type['status'];
        }
        if (isset($type['remind_last'])) {
            $this->last_ask = $type['remind_last'];
        }
    }

    // Updates (or creates) the reminder line for the pair (|user|, |reminder_id|)
    // using the |status| as status, and the |next_ask| as the delay between now
    // and the next ask (if any).
    private function UpdateStatus($status, $next_ask)
    {
        XDB::execute('REPLACE INTO  reminder
                               SET  uid = {?}, type_id = {?}, status = {?},
                                    remind_last = NOW(), remind_next = FROM_UNIXTIME({?})',
                     $this->user->id(), $this->type_id, $status,
                     ($next_ask > 0 ? time() + $next_ask * 24 * 60 * 60 : null));
    }

    // Updates the status of the reminder for the current user.
    protected function UpdateOnYes()
    {
        $this->UpdateStatus('yes', $this->remind_delay_yes);
    }
    protected function UpdateOnNo()
    {
        $this->UpdateStatus('no', $this->remind_delay_no);
    }
    protected function UpdateOnDismiss()
    {
        $this->UpdateStatus('dismiss', $this->remind_delay_dismiss);
    }

    // Display and http handling helpers --------------------------------------

    // Handles a hit on the reminder onebox (for links made using the GetBaseUrl
    // method below).
    abstract public function HandleAction($action);

    // Returns the content of the onebox reminder. Default implementation displays
    // a text and three links (yes, no, dismiss); it uses the text from method
    // GetDisplayText.
    public function Display(&$page)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('reminder/default.tpl', NO_SKIN);
        $page->assign('text', $this->GetDisplayText());
        $page->assign('baseurl', $this->GetBaseUrl());
    }

    // Helper for returning the content as a string, instead of using the existing
    // globale XorgPage instance.
    public function GetDisplayAsString()
    {
        $page = new XorgPage();
        $this->Display($page);
        return $page->raw();
    }

    // Returns the text to display in the onebox.
    abstract protected function GetDisplayText();

    // Returns the base url for the reminder module.
    protected function GetBaseUrl()
    {
        return 'ajax/reminder/' . $this->name;
    }

    // Static factories -------------------------------------------------------

    // Returns a chosen class using the user data from |user|, and from the database.
    public static function GetCandidateReminder(User &$user)
    {
        $res = XDB::query('SELECT  rt.*, r.status, r.remind_last
                             FROM  reminder_type AS rt
                        LEFT JOIN  reminder      AS r ON (rt.type_id = r.type_id AND r.uid = {?})
                            WHERE  r.uid IS NULL OR r.remind_next < NOW()
                         ORDER BY  RAND()',
                          $user->id());

        $candidates  = $res->fetchAllAssoc();
        $priority    = rand(1, 100);
        while (count($candidates) > 0 && $priority > 0) {
            foreach ($candidates as $key => $candidate) {
                if ($candidate['weight'] > $priority) {
                    $class = self::GetClassName($candidate['name']);
                    if ($class && call_user_func(array($class, 'IsCandidate'), $user)) {
                        return new $class($user, $candidate);
                    }
                    unset($candidates[$key]);
                }
            }
            $priority = (int) ($priority / 2);
        }

        return null;
    }

    // Returns an instantiation of the reminder class which name is |name|, using
    // user data from |user|, and from the database.
    public static function GetByName(User &$user, $name)
    {
        if (!($class = self::GetClassName($name))) {
            return null;
        }

        $res = XDB::query('SELECT  rt.*, r.status, r.remind_last
                             FROM  reminder_type AS rt
                        LEFT JOIN  reminder      AS r ON (rt.type_id = r.type_id AND r.uid = {?})
                            WHERE  rt.name = {?}',
                          $user->id(), $name);
        if ($res->numRows() > 0) {
            return new $class($user, $res->fetchOneAssoc());
        }

        return null;
    }

    // Computes the name of the class for reminder named |name|, and preloads
    // the class.
    private static function GetClassName($name)
    {
        @require_once "reminder/$name.inc.php";
        $class = 'Reminder' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        return (class_exists($class) ? $class : null);
    }
}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
