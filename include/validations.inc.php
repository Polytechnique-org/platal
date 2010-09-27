<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

define('SIZE_MAX', 32768);

global $globals;


/** Virtual class to adapt for every possible implementation.
 */
abstract class Validate
{
    // {{{ properties

    public $user;

    public $stamp;
    public $unique;
    // Enable the refuse button.
    public $refuse = true;

    public $type;
    public $comments = Array();
    // Validations rules: comments for administrators.
    public $rules = 'Mieux vaut laisser une demande de validation à un autre administrateur que de valider une requête illégale ou que de refuser une demande légitime.';

    // }}}
    // {{{ constructor

    /** Constructor
     * @param $_user: user object that required the validation.
     * @param $_unique: set to false if a profile can have multiple requests of this type.
     * @param $_type: request's type.
     */
    public function __construct(User &$_user, $_unique, $_type)
    {
        $this->user   = &$_user;
        $this->stamp  = date('YmdHis');
        $this->unique = $_unique;
        $this->type   = $_type;
        $this->promo  = $this->user->promo();
    }

    // }}}
    // {{{ function submit()

    /** Sends data to validation.
     * It also deletes multiple requests for a couple (profile, type)
     * when $this->unique is set to true.
     */
    public function submit()
    {
        if ($this->unique) {
            XDB::execute('DELETE FROM  requests
                                WHERE  uid = {?} AND type = {?}',
                         $this->user->id(), $this->type);
        }

        $this->stamp = date('YmdHis');
        XDB::execute('INSERT INTO  requests (uid, type, data, stamp)
                           VALUES  ({?}, {?}, {?}, {?})',
                     $this->user->id(), $this->type, $this, $this->stamp);

        global $globals;
        $globals->updateNbValid();
        return true;
    }

    // }}}
    // {{{ function update()

    protected function update()
    {
        XDB::execute('UPDATE  requests
                         SET  data = {?}, stamp = stamp
                       WHERE  uid = {?} AND type = {?} AND stamp = {?}',
                     $this, $this->user->id(), $this->type, $this->stamp);
        return true;
    }

    // }}}
    // {{{ function clean()

    /** Deletes request from 'requests' table.
     * If $this->unique is set, it deletes every requests of this type.
     */
    public function clean()
    {
        global $globals;

        if ($this->unique) {
            $success = XDB::execute('DELETE FROM  requests
                                           WHERE  uid = {?} AND type = {?}',
                                    $this->user->id(), $this->type);
        } else {
            $success =  XDB::execute('DELETE FROM  requests
                                            WHERE  uid = {?} AND type = {?} AND stamp = {?}',
                                      $this->user->id(), $this->type, $this->stamp);
        }
        $globals->updateNbValid();
        return $success;
    }

    // }}}
    // {{{ function handle_formu()

    /** Handles form validation.
     */
    public function handle_formu()
    {
        if (Env::has('delete')) {
            $this->clean();
            $this->trigSuccess('Requête supprimée.');
            return true;
        }

        // Data updates.
        if (Env::has('edit')) {
            if ($this->handle_editor()) {
                $this->update();
                $this->trigSuccess('Requête mise à jour.');
                return true;
            }
            return false;
        }

        // Comment addition.
        if (Env::has('hold') && Env::has('comm')) {
            $formid = Env::i('formid');
            foreach ($this->comments as $comment) {
                if ($comment[2] === $formid) {
                    return true;
                }
            }
            if (!strlen(trim(Env::v('comm')))) {
                return true;
            }
            $this->comments[] = array(S::user()->login(), Env::v('comm'), $formid);

            // Sends email to our hotline.
            global $globals;
            $mailer = new PlMailer();
            $mailer->setSubject("Commentaires de validation {$this->type}");
            $mailer->setFrom("validation+{$this->type}@{$globals->mail->domain}");
            $mailer->addTo($globals->core->admin_email);

            $body = "Validation {$this->type} pour {$this->user->login()}\n\n"
              . S::user()->login() . " a ajouté le commentaire :\n\n"
              . Env::v('comm') . "\n\n"
              . "cf la discussion sur : " . $globals->baseurl . "/admin/validate";

            $mailer->setTxtBody(wordwrap($body));
            $mailer->send();

            $this->update();
            $this->trigSuccess('Commentaire ajouté.');
            return true;
        }

        if (Env::has('accept')) {
            if ($this->commit()) {
                $this->sendmail(true);
                $this->clean();
                $this->trigSuccess('Email de validation envoyé');
                return true;
            } else {
                $this->trigError('Erreur lors de la validation');
                return false;
            }
        }

        if (Env::has('refuse')) {
            if (Env::v('comm')) {
                $this->sendmail(false);
                $this->clean();
                $this->trigSuccess('Email de refus envoyé.');
                return true;
            } else {
                $this->trigError('Pas de motivation pour le refus&nbsp;!!!');
            }
        }

        return false;
    }

    // }}}
    // {{{ function sendmail

    protected function sendmail($isok)
    {
        global $globals;
        $mailer = new PlMailer();
        $mailer->setSubject($this->_mail_subj());
        $mailer->setFrom("validation+{$this->type}@{$globals->mail->domain}");
        $mailer->addTo("\"{$this->user->fullName()}\" <{$this->user->bestEmail()}>");
        $mailer->addCc("validation+{$this->type}@{$globals->mail->domain}");

        $body = ($this->user->isFemale() ? "Chère camarade,\n\n" : "Cher camarade,\n\n")
              . $this->_mail_body($isok)
              . (Env::has('comm') ? "\n\n" . Env::v('comm') : '')
              . "\n\nCordialement,\n-- \nL'équipe de Polytechnique.org\n"
              . $this->_mail_ps($isok);

        $mailer->setTxtBody(wordwrap($body));
        $mailer->send();
    }

    // }}}
    // {{{ function trig()

    protected function trigError($msg)
    {
        Platal::page()->trigError($msg);
    }

    protected function trigWarning($msg)
    {
        Platal::page()->trigWarning($msg);
    }

    protected function trigSuccess($msg)
    {
        Platal::page()->trigSuccess($msg);
    }

    // }}}
    // {{{ function get_typed_request()

    /**
     * @param $pid: profile's pid
     * @param $type: request's type
     * @param $stamp: request's timestamp
     *
     * Should only be used to retrieve an object in the databse with Validate::get_typed_request(...)
     */
    static public function get_typed_request($uid, $type, $stamp = -1)
    {
        if ($stamp == -1) {
            $res = XDB::query('SELECT  data
                                 FROM  requests
                                WHERE  uid = {?} and type = {?}',
                              $uid, $type);
        } else {
            $res = XDB::query('SELECT  data, DATE_FORMAT(stamp, "%Y%m%d%H%i%s")
                                 FROM  requests
                                WHERE  uid = {?} AND type = {?} and stamp = {?}',
                              $uid, $type, $stamp);
        }
        if ($result = $res->fetchOneCell()) {
            $result = Validate::unserialize($result);
        } else {
            $result = false;
        }
        return($result);
    }

    // }}}
    // {{{ function get_request_by_id()

    static public function get_request_by_id($id)
    {
        list($uid, $type, $stamp) = explode('_', $id, 3);
        return Validate::get_typed_request($uid, $type, $stamp);
    }

    // }}}
    // {{{ function get_typed_requests()

    /** Same as get_typed_request() but return an array of objects.
     */
    static public function get_typed_requests($uid, $type)
    {
        $res = XDB::iterRow('SELECT  data
                               FROM  requests
                              WHERE  uid = {?} and type = {?}',
                            $uid, $type);
        $array = array();
        while (list($data) = $res->next()) {
            $array[] = Validate::unserialize($data);
        }
        return $array;
    }

    // }}}
    // {{{ function get_typed_requests_count()

    /** Same as get_typed_requests() but return the count of available requests.
     */
    static public function get_typed_requests_count($uid, $type)
    {
        $res = XDB::query('SELECT  COUNT(data)
                             FROM  requests
                            WHERE  uid = {?} and type = {?}',
                          $uid, $type);
        return $res->fetchOneCell();
    }

    // }}}
    // {{{ function _mail_body

    abstract protected function _mail_body($isok);

    // }}}
    // {{{ function _mail_subj

    abstract protected function _mail_subj();

    // }}}
    // {{{ function _mail_ps

    protected function _mail_ps($isok)
    {
        return '';
    }

    // }}}
    // {{{ function commit()

    /** Inserts data in database.
     */
    abstract public function commit();

    // }}}
    // {{{ function formu()

    /** Retunrs the name of the form's template. */
    abstract public function formu();

    // }}}
    // {{{ function editor()

    /** Returns the name of the edition form's template. */
    public function editor()
    {
        return null;
    }

    // }}}
    // {{{ function answers()

    /** Automatic answers table for this type of validation. */
    public function answers()
    {
        static $answers_table;
        if (!isset($answers_table[$this->type])) {
            $r = XDB::query('SELECT  id, title, answer
                               FROM  requests_answers
                              WHERE  category = {?}',
                            $this->type);
            $answers_table[$this->type] = $r->fetchAllAssoc();
        }
        return $answers_table[$this->type];
    }

    // }}}
    // {{{ function id()

    public function id()
    {
        return $this->user->id() . '_' . $this->type . '_' . $this->stamp;
    }

    // }}}
    // {{{ function ruleText()

    public function ruleText()
    {
        return str_replace('\'', '\\\'', $this->rules);
    }

    // }}}
    // {{{ function unserialize()

    public static function unserialize($data)
    {
        return unserialize($data);
    }

    // }}}

    /** Return an iterator over the validation concerning the given type
     * and the given user.
     *
     * @param type The type of the validations to fetch, null mean "any type"
     * @param applyTo A User or a Profile object the validation applies to.
     */
    public static function iterate($type = null, $applyTo = null)
    {
        function toValidation($elt)
        {
            list($result, $stamp) = $elt;
            $result = Validate::unserialize($result);
            $result->stamp = $stamp;
            return $result;
        }

        $where = array();
        if ($type) {
            $where[] = XDB::format('type = {?}', $type);
        }
        if ($applyTo) {
            if ($applyTo instanceof User) {
                $where[] = XDB::format('uid = {?}', $applyTo->id());
            } else if ($applyTo instanceof Profile) {
                $where[] = XDB::format('pid = {?}', $applyTo->id());
            }
        }
        if (!empty($where)) {
            $where = 'WHERE ' . implode('AND', $where);
        }
        $it = XDB::iterRow('SELECT  data, DATE_FORMAT(stamp, "%Y%m%d%H%i%s")
                              FROM  requests
                                 ' . $where . '
                          ORDER BY  stamp');
        return PlIteratorUtils::map($it, 'toValidation');
    }
}

/** Virtual class for profile related validation.
 */
abstract class ProfileValidate extends Validate
{
    // {{{ properties

    public $profile;
    public $profileOwner;
    public $userIsProfileOwner;
    public $ownerIsRegistered;

    // }}}
    // {{{ constructor

    /** Constructor
     * @param $_user: user object that required the validation.
     * @param $_profile: profile object that is to be modified,
     *                   its owner (if exists) can differ from $_user.
     * @param $_unique: set to false if a profile can have multiple requests of this type.
     * @param $_type: request's type.
     */
    public function __construct(User &$_user, Profile &$_profile, $_unique, $_type)
    {
        parent::__construct($_user, $_unique, $_type);
        $this->profile = &$_profile;
        $this->profileOwner = $this->profile->owner();
        $this->userIsProfileOwner = (!is_null($this->profileOwner)
                                     && $this->profileOwner->id() == $this->user->id());
        $this->ownerIsRegistered = $this->profile->isActive();
    }

    // }}}
    // {{{ function submit()

    /** Sends data to validation.
     * It also deletes multiple requests for a couple (profile, type)
     * when $this->unique is set to true.
     */
    public function submit()
    {
        if ($this->unique) {
            XDB::execute('DELETE FROM  requests
                                WHERE  pid = {?} AND type = {?}',
                         $this->profile->id(), $this->type);
        }

        $this->stamp = date('YmdHis');
        XDB::execute('INSERT INTO  requests (uid, pid, type, data, stamp)
                           VALUES  ({?}, {?}, {?}, {?}, {?})',
                     $this->user->id(), $this->profile->id(), $this->type, $this, $this->stamp);

        global $globals;
        $globals->updateNbValid();
        return true;
    }

    // }}}
    // {{{ function update()

    protected function update()
    {
        XDB::execute('UPDATE  requests
                         SET  data = {?}, stamp = stamp
                       WHERE  pid = {?} AND type = {?} AND stamp = {?}',
                     $this, $this->profile->id(), $this->type, $this->stamp);
        return true;
    }

    // }}}
    // {{{ function clean()

    /** Deletes request from 'requests' table.
     * If $this->unique is set, it deletes every requests of this type.
     */
    public function clean()
    {
        global $globals;

        if ($this->unique) {
            $success = XDB::execute('DELETE FROM  requests
                                           WHERE  pid = {?} AND type = {?}',
                                    $this->profile->id(), $this->type);
        } else {
            $success =  XDB::execute('DELETE FROM  requests
                                            WHERE  pid = {?} AND type = {?} AND stamp = {?}',
                                      $this->profile->id(), $this->type, $this->stamp);
        }
        $globals->updateNbValid();
        return $success;
    }

    // }}}
    // {{{ function sendmail

    protected function sendmail($isok)
    {
        // Only sends email if the profile's owner exists and is registered.
        if ($this->ownerIsRegistered) {
            global $globals;

            $mailer = new PlMailer();
            $mailer->setSubject($this->_mail_subj());
            $mailer->setFrom("validation+{$this->type}@{$globals->mail->domain}");
            $mailer->addTo("\"{$this->profile->fullName()}\" <{$this->profileOwner->bestEmail()}>");
            $mailer->addCc("validation+{$this->type}@{$globals->mail->domain}");
            $body = ($this->profile->isFemale() ? "Chère camarade,\n\n" : "Cher camarade,\n\n")
                  . $this->_mail_body($isok)
                  . (Env::has('comm') ? "\n\n" . Env::v('comm') : '')
                  . "\n\nCordialement,\n-- \nL'équipe de Polytechnique.org\n"
                  . $this->_mail_ps($isok);
            $mailer->setTxtBody(wordwrap($body));
            $mailer->send();
        }
    }

    // }}}
    // {{{ function get_typed_request()

    /**
     * @param $pid: profile's pid
     * @param $type: request's type
     * @param $stamp: request's timestamp
     *
     * Should only be used to retrieve an object in the databse with Validate::get_typed_request(...)
     */
    static public function get_typed_request($pid, $type, $stamp = -1)
    {
        if ($stamp == -1) {
            $res = XDB::query('SELECT  data
                                 FROM  requests
                                WHERE  pid = {?} and type = {?}',
                              $pid, $type);
        } else {
            $res = XDB::query('SELECT  data, DATE_FORMAT(stamp, "%Y%m%d%H%i%s")
                                 FROM  requests
                                WHERE  pid = {?} AND type = {?} and stamp = {?}',
                              $pid, $type, $stamp);
        }
        if ($result = $res->fetchOneCell()) {
            $result = Validate::unserialize($result);
        } else {
            $result = false;
        }
        return $result;
    }

    // }}}
    // {{{ function get_request_by_id()

    static public function get_request_by_id($id)
    {
        list($pid, $type, $stamp) = explode('_', $id, 3);
        return Validate::get_typed_request($pid, $type, $stamp);
    }

    // }}}
    // {{{ function get_typed_requests()

    /** Same as get_typed_request() but return an array of objects.
     */
    static public function get_typed_requests($pid, $type)
    {
        $res = XDB::iterRow('SELECT  data
                               FROM  requests
                              WHERE  pid = {?} and type = {?}',
                            $pid, $type);
        $array = array();
        while (list($data) = $res->next()) {
            $array[] = Validate::unserialize($data);
        }
        return $array;
    }

    // }}}
    // {{{ function get_typed_requests_count()

    /** Same as get_typed_requests() but returns the count of available requests.
     */
    static public function get_typed_requests_count($pid, $type)
    {
        $res = XDB::query('SELECT  COUNT(data)
                             FROM  requests
                            WHERE  pid = {?} and type = {?}',
                          $pid, $type);
        return $res->fetchOneCell();
    }

    // }}}
    // {{{ function id()

    public function id()
    {
        return $this->profile->id() . '_' . $this->type . '_' . $this->stamp;
    }

    // }}}
}

foreach (glob(dirname(__FILE__) . '/validations/*.inc.php') as $file) {
    require_once $file;
}

/* vim: set expandtab shiftwidth=4 tabstop=4 softtabstop=4 foldmethod=marker enc=utf-8: */
?>
