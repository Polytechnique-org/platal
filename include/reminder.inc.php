<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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
// IsCandidate method (prototype: (User $user)).
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
    function __construct(User $user, array $type)
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
    private static function UpdateStatus($uid, $type_id, $status, $next_ask)
    {
        XDB::execute('INSERT INTO  reminder (uid, type_id, status, remind_last, remind_next)
                           VALUES  ({?}, {?}, {?}, NOW(), FROM_UNIXTIME({?}))
          ON DUPLICATE KEY UPDATE  status = VALUES(status), remind_last = VALUES(remind_last), remind_next = VALUES(remind_next)',
                     $uid, $type_id, $status,
                     ($next_ask > 0 ? time() + $next_ask * 24 * 60 * 60 : null));
    }

    // Updates the status of the reminder for the current user.
    protected function UpdateOnYes()
    {
        $this->UpdateStatus($this->user->id(), $this->type_id,
                            'yes', $this->remind_delay_yes);
    }
    protected function UpdateOnNo()
    {
        $this->UpdateStatus($this->user->id(), $this->type_id,
                            'no', $this->remind_delay_no);
    }
    protected function UpdateOnDismiss()
    {
        $this->UpdateStatus($this->user->id(), $this->type_id,
                            'dismiss', $this->remind_delay_dismiss);
    }

    // Display and http handling helpers --------------------------------------

    // Handles a hit on the reminder onebox (for links made using the GetBaseUrl
    // method below).
    abstract public function HandleAction($action);

    // Displays a reduced version of the reminder and notifies that the action
    // has been taken into account.
    public function NotifiesAction($page)
    {
        pl_content_headers("text/html");
        $page->changeTpl('reminder/notification.tpl', NO_SKIN);
        $page->assign('previous_reminder', $this->title());
    }

    // Displays the reminder as a standalone html snippet. It should be used
    // when the reminder is the only output of a page.
    public function DisplayStandalone($page, $previous_reminder = null)
    {
        pl_content_headers("text/html");
        $page->changeTpl('reminder/base.tpl', NO_SKIN);
        $this->Prepare($page);
        if ($previous_reminder) {
            $page->assign('previous_reminder', $previous_reminder);
        }
    }

    // Prepares the display by assigning template variables.
    public function Prepare($page)
    {
        $page->assign_by_ref('reminder', $this);
    }

    // Returns the name of the inner template, or null if a simple text obtained
    // from GetText should be printed.
    public function template() { return null; }

    // Returns the text to display in the onebox, or null if a
    public function text() { return ''; }

    // Returns the title of the onebox.
    public function title() { return ''; }

    // Should return true if this onebox needs to be considered as a warning and
    // not just as a subscription offer.
    public function warning() { return false; }

    // Returns the base url for the reminder module.
    public function baseurl()
    {
        return 'ajax/reminder/' . $this->name;
    }

    // Returns the url for the information page.
    public function info() { return ''; }

    // Static status update methods -------------------------------------------

    // Marks the candidate reminder as having been accepted for user |uid|.
    // It is intended to be used when a reminder box has been bypassed, and when
    // it should behave as if the user had clicked on 'yes'.
    protected static function MarkCandidateAsAccepted($uid, $candidate)
    {
        Reminder::UpdateStatus($uid, $candidate['type_id'],
                               'yes', $candidate['remind_delay_yes']);
    }

    // Static factories -------------------------------------------------------

    // Returns a chosen class using the user data from |user|, and from the database.
    public static function GetCandidateReminder(User $user)
    {
        $res = XDB::query('SELECT  rt.*, r.status, r.remind_last
                             FROM  reminder_type AS rt
                        LEFT JOIN  reminder      AS r ON (rt.type_id = r.type_id AND r.uid = {?})
                            WHERE  r.uid IS NULL OR r.remind_next < NOW()',
                          $user->id());
        $candidates  = $res->fetchAllAssoc();

        $weight_map = create_function('$a', 'return $a["weight"];');
        while (count($candidates) > 0) {
            $position = rand(1, array_sum(array_map($weight_map, $candidates)));
            foreach ($candidates as $key => $candidate) {
                $position -= $candidate['weight'];
                if ($position <= 0) {
                    $class = self::GetClassName($candidate['name']);
                    if ($class && call_user_func(array($class, 'IsCandidate'), $user, $candidate)) {
                        return new $class($user, $candidate);
                    }
                    unset($candidates[$key]);
                }
            }
        }

        return null;
    }

    // Returns an instantiation of the reminder class which name is |name|, using
    // user data from |user|, and from the database.
    public static function GetByName(User $user, $name)
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
        include_once "reminder/$name.inc.php";
        $class = 'Reminder' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        return (class_exists($class) ? $class : null);
    }
}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
