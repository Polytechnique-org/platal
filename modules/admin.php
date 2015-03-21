<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

class AdminModule extends PLModule
{
    function handlers()
    {
        return array(
            'phpinfo'                      => $this->make_hook('phpinfo',                AUTH_PASSWD, 'admin'),
            'get_rights'                   => $this->make_hook('get_rights',             AUTH_COOKIE, 'admin'),
            'set_skin'                     => $this->make_hook('set_skin',               AUTH_COOKIE, 'admin'),
            'admin'                        => $this->make_hook('default',                AUTH_PASSWD, 'admin'),
            'admin/dead-but-active'        => $this->make_hook('dead_but_active',        AUTH_PASSWD, 'admin'),
            'admin/deaths'                 => $this->make_hook('deaths',                 AUTH_PASSWD, 'admin'),
            'admin/downtime'               => $this->make_hook('downtime',               AUTH_PASSWD, 'admin'),
            'admin/homonyms'               => $this->make_hook('homonyms',               AUTH_PASSWD, 'admin'),
            'admin/logger'                 => $this->make_hook('logger',                 AUTH_PASSWD, 'admin'),
            'admin/logger/actions'         => $this->make_hook('logger_actions',         AUTH_PASSWD, 'admin'),
            'admin/postfix/blacklist'      => $this->make_hook('postfix_blacklist',      AUTH_PASSWD, 'admin'),
            'admin/postfix/delayed'        => $this->make_hook('postfix_delayed',        AUTH_PASSWD, 'admin'),
            'admin/postfix/regexp_bounces' => $this->make_hook('postfix_regexpsbounces', AUTH_PASSWD, 'admin'),
            'admin/postfix/whitelist'      => $this->make_hook('postfix_whitelist',      AUTH_PASSWD, 'admin'),
            'admin/mx/broken'              => $this->make_hook('mx_broken',              AUTH_PASSWD, 'admin'),
            'admin/skins'                  => $this->make_hook('skins',                  AUTH_PASSWD, 'admin'),
            'admin/user'                   => $this->make_hook('user',                   AUTH_PASSWD, 'admin'),
            'admin/add_accounts'           => $this->make_hook('add_accounts',           AUTH_PASSWD, 'admin'),
            'admin/validate'               => $this->make_hook('validate',               AUTH_PASSWD, 'admin,edit_directory'),
            'admin/validate/answers'       => $this->make_hook('validate_answers',       AUTH_PASSWD, 'admin'),
            'admin/wiki'                   => $this->make_hook('wiki',                   AUTH_PASSWD, 'admin'),
            'admin/ipwatch'                => $this->make_hook('ipwatch',                AUTH_PASSWD, 'admin'),
            'admin/icons'                  => $this->make_hook('icons',                  AUTH_PASSWD, 'admin'),
            'admin/geocoding'              => $this->make_hook('geocoding',              AUTH_PASSWD, 'admin'),
            'admin/accounts'               => $this->make_hook('accounts',               AUTH_PASSWD, 'admin'),
            'admin/account/watch'          => $this->make_hook('account_watch',          AUTH_PASSWD, 'admin'),
            'admin/account/types'          => $this->make_hook('account_types',          AUTH_PASSWD, 'admin'),
            'admin/xnet_without_group'     => $this->make_hook('xnet_without_group',     AUTH_PASSWD, 'admin'),
            'admin/jobs'                   => $this->make_hook('jobs',                   AUTH_PASSWD, 'admin,edit_directory'),
            'admin/profile'                => $this->make_hook('profile',                AUTH_PASSWD, 'admin,edit_directory'),
            'admin/phd'                    => $this->make_hook('phd',                    AUTH_PASSWD, 'admin'),
            'admin/name'                   => $this->make_hook('admin_name',             AUTH_PASSWD, 'admin'),
            'admin/add_secondary_edu'      => $this->make_hook('add_secondary_edu',      AUTH_PASSWD, 'admin')
        );
    }

    function handler_phpinfo($page)
    {
        phpinfo();
        exit;
    }

    function handler_get_rights($page)
    {
        if (S::suid()) {
            $page->kill('Déjà en SUID');
        }
        S::assert_xsrf_token();
        $level = Post::s('account_type');
        if ($level != 'admin') {
            $user = User::getSilentWithUID(S::user()->id());
            $user->is_admin = false;
            $types = DirEnum::getOptions(DirEnum::ACCOUNTTYPES);
            if (!empty($types[$level])) {
                $user->setPerms($types[$level]);
            }
            S::set('suid_startpage', $_SERVER['HTTP_REFERER']);
            Platal::session()->startSUID($user);
        }
        if (!empty($_SERVER['HTTP_REFERER'])) {
            http_redirect($_SERVER['HTTP_REFERER']);
        } else {
            pl_redirect('/');
        }
    }

    function handler_set_skin($page)
    {
        S::assert_xsrf_token();
        S::set('skin', Post::s('change_skin'));
        if (!empty($_SERVER['HTTP_REFERER'])) {
            http_redirect($_SERVER['HTTP_REFERER']);
        } else {
            pl_redirect('/');
        }
    }

    function handler_default($page)
    {
        $page->changeTpl('admin/index.tpl');
        $page->setTitle('Administration');
    }

    function handler_postfix_delayed($page)
    {
        $page->changeTpl('admin/postfix_delayed.tpl');
        $page->setTitle('Administration - Postfix : Retardés');

        if (Env::has('del')) {
            $crc = Env::v('crc');
            XDB::execute("UPDATE postfix_mailseen SET release = 'del' WHERE crc = {?}", $crc);
            $page->trigSuccess($crc . " verra tous ses emails supprimés&nbsp;!");
        } elseif (Env::has('ok')) {
            $crc = Env::v('crc');
            XDB::execute("UPDATE postfix_mailseen SET release = 'ok' WHERE crc = {?}", $crc);
            $page->trigSuccess($crc . " a le droit de passer&nbsp;!");
        }

        $sql = XDB::iterator(
                "SELECT  crc, nb, update_time, create_time,
                         FIND_IN_SET('del', p.release) AS del,
                         FIND_IN_SET('ok', p.release) AS ok
                   FROM  postfix_mailseen AS p
                  WHERE  nb >= 30
               ORDER BY  p.release != ''");

        $page->assign_by_ref('mails', $sql);
    }

    // {{{ logger view

    /** Retrieves the available days for a given year and month.
     * Obtain a list of days of the given month in the given year
     * that are within the range of dates that we have log entries for.
     *
     * @param integer year
     * @param integer month
     * @return array days in that month we have log entries covering.
     * @private
     */
    function _getDays($year, $month)
    {
        // give a 'no filter' option
        $days = array();
        $days[0] = "----";

        if ($year && $month) {
            $day_max = Array(-1, 31, checkdate(2, 29, $year) ? 29 : 28 , 31,
                             30, 31, 30, 31, 31, 30, 31, 30, 31);
            $res = XDB::query("SELECT YEAR (MAX(start)), YEAR (MIN(start)),
                                      MONTH(MAX(start)), MONTH(MIN(start)),
                                      DAYOFMONTH(MAX(start)),
                                      DAYOFMONTH(MIN(start))
                                 FROM log_sessions");
            list($ymax, $ymin, $mmax, $mmin, $dmax, $dmin) = $res->fetchOneRow();

            if (($year < $ymin) || ($year == $ymin && $month < $mmin)) {
                return array();
            }

            if (($year > $ymax) || ($year == $ymax && $month > $mmax)) {
                return array();
            }

            $min = ($year==$ymin && $month==$mmin) ? intval($dmin) : 1;
            $max = ($year==$ymax && $month==$mmax) ? intval($dmax) : $day_max[$month];

            for($i = $min; $i<=$max; $i++) {
                $days[$i] = $i;
            }
        }
        return $days;
    }


    /** Retrieves the available months for a given year.
     * Obtains a list of month numbers that are within the timeframe that
     * we have log entries for.
     *
     * @param integer year
     * @return array List of month numbers we have log info for.
     * @private
     */
    function _getMonths($year)
    {
        // give a 'no filter' option
        $months = array();
        $months[0] = "----";

        if ($year) {
            $res = XDB::query("SELECT YEAR (MAX(start)), YEAR (MIN(start)),
                                      MONTH(MAX(start)), MONTH(MIN(start))
                                 FROM log_sessions");
            list($ymax, $ymin, $mmax, $mmin) = $res->fetchOneRow();

            if (($year < $ymin) || ($year > $ymax)) {
                return array();
            }

            $min = $year == $ymin ? intval($mmin) : 1;
            $max = $year == $ymax ? intval($mmax) : 12;

            for($i = $min; $i<=$max; $i++) {
                $months[$i] = $i;
            }
        }
        return $months;
    }


    /** Retrieves the available years.
     * Obtains a list of years that we have log entries covering.
     *
     * @return array years we have log entries for.
     * @private
     */
    function _getYears()
    {
        // give a 'no filter' option
        $years = array();
        $years[0] = "----";

        // retrieve available years
        $res = XDB::query("select YEAR(MAX(start)), YEAR(MIN(start)) FROM log_sessions");
        list($max, $min) = $res->fetchOneRow();

        for($i = intval($min); $i<=$max; $i++) {
            $years[$i] = $i;
        }
        return $years;
    }

    private function _getActions()
    {
        $actions = XDB::fetchAllAssoc('id', 'SELECT  id, description
                                               FROM  log_actions');
        $actions[0] = '----';
        ksort($actions);

        return $actions;
    }

    /** Make a where clause to get a user's sessions.
     * Prepare the where clause request that will retrieve the sessions.
     *
     * @param $year INTEGER Only get log entries made during the given year.
     * @param $month INTEGER Only get log entries made during the given month.
     * @param $day INTEGER Only get log entries made during the given day.
     * @param $action INTEGER Only get log entries corresponding to this action.
     * @param $uid INTEGER Only get log entries referring to the given user ID.
     *
     * @return STRING the WHERE clause of a query, including the 'WHERE' keyword
     * @private
     */
    private function _makeWhere($year, $month, $day, $action, $uid)
    {
        // start constructing the "where" clause
        $where = array();

        if ($uid) {
            $where[] = XDB::format('ls.uid = {?}', $uid);
        }

        // we were given at least a year
        if ($year) {
            if ($day) {
                $dmin = mktime(0, 0, 0, $month, $day, $year);
                $dmax = mktime(0, 0, 0, $month, $day+1, $year);
            } elseif ($month) {
                $dmin = mktime(0, 0, 0, $month, 1, $year);
                $dmax = mktime(0, 0, 0, $month+1, 1, $year);
            } else {
                $dmin = mktime(0, 0, 0, 1, 1, $year);
                $dmax = mktime(0, 0, 0, 1, 1, $year+1);
            }
            $where[] = "ls.start >= " . date("Ymd000000", $dmin);
            $where[] = "ls.start < " . date("Ymd000000", $dmax);
        }

        if ($action != 0) {
            $where[] = XDB::format('la.id = {?}', $action);
        }

        if (!empty($where)) {
            return 'WHERE ' . implode($where, ' AND ');
        } else {
            return '';
        }
        // WE know it's totally reversed, so better use array_reverse than a SORT BY start DESC
    }

    // }}}

    function handler_logger($page, $action = null, $arg = null) {
        if ($action == 'session') {

            // we are viewing a session
            $res = XDB::query("SELECT  ls.*, a.hruid AS username, sa.hruid AS suer
                                 FROM  log_sessions AS ls
                           INNER JOIN  accounts   AS a  ON (a.uid = ls.uid)
                            LEFT JOIN  accounts   AS sa ON (sa.uid = ls.suid)
                                WHERE  ls.id = {?}", $arg);

            $page->assign('session', $a = $res->fetchOneAssoc());

            $res = XDB::iterator('SELECT  a.text, e.data, e.stamp
                                    FROM  log_events  AS e
                               LEFT JOIN  log_actions AS a ON e.action=a.id
                                   WHERE  e.session={?}', $arg);
            while ($myarr = $res->next()) {
               $page->append('events', $myarr);
            }

        } else {
            $loguser = $action == 'user' ? $arg : Env::v('loguser');

            if ($loguser) {
                $user = User::get($loguser);
                $loguid = $user->id();
            } else {
                $loguid = null;
            }

            if ($loguid) {
                $year  = Env::i('year');
                $month = Env::i('month');
                $day   = Env::i('day');
            } else {
                $year  = Env::i('year', intval(date('Y')));
                $month = Env::i('month', intval(date('m')));
                $day   = Env::i('day', intval(date('d')));
            }
            $action = Post::i('action');

            if (!$year)
                $month = 0;
            if (!$month)
                $day = 0;

            // smarty assignments
            // retrieve available years
            $page->assign('years', $this->_getYears());
            $page->assign('year', $year);

            // retrieve available months for the current year
            $page->assign('months', $this->_getMonths($year));
            $page->assign('month', $month);

            // retrieve available days for the current year and month
            $page->assign('days', $this->_getDays($year, $month));
            $page->assign('day', $day);

            // Retrieve available actions
            $page->assign('actions', $this->_getActions());
            $page->assign('action', $action);

            $page->assign('loguser', $loguser);
            // smarty assignments

            if ($loguid || $year) {

                // get the requested sessions
                $where  = $this->_makeWhere($year, $month, $day, $action, $loguid);
                if ($action != 0) {
                    $join = 'INNER JOIN  log_events   AS le ON (ls.id = le.session)
                             INNER JOIN  log_actions  AS la ON (le.action = la.id)';
                } else {
                    $join = '';
                }
                $select = 'SELECT  ls.id, ls.start, ls.uid, a.hruid as username
                             FROM  log_sessions AS ls
                       INNER JOIN  accounts     AS a  ON (a.uid = ls.uid)
                       ' . $join . '
                       ' . $where . '
                         GROUP BY  ls.id
                         ORDER BY  ls.start DESC';
                $res = XDB::iterator($select);

                $sessions = array();
                while ($mysess = $res->next()) {
                    $mysess['events'] = array();
                    $sessions[$mysess['id']] = $mysess;
                }
                array_reverse($sessions);

                // attach events
                $sql = 'SELECT  ls.id, la.text
                          FROM  log_sessions AS ls
                     LEFT JOIN  log_events   AS le ON (le.session = ls.id)
                    INNER JOIN  log_actions  AS la ON (la.id = le.action)
                    ' . $where;

                $res = XDB::iterator($sql);
                while ($event = $res->next()) {
                    array_push($sessions[$event['id']]['events'], $event['text']);
                }
                $page->assign_by_ref('sessions', $sessions);
            } else {
                $page->assign('msg_nofilters', "Sélectionner une année et/ou un utilisateur");
            }
        }

        $page->changeTpl('admin/logger-view.tpl');

        $page->setTitle('Administration - Logs des sessions');
    }

    function handler_user($page, $login = false)
    {
        global $globals;
        $page->changeTpl('admin/user.tpl');
        $page->setTitle('Administration - Compte');

        if (S::suid()) {
            $page->kill("Déjà en SUID&nbsp;!!!");
        }

        // Loads the user identity using the environment.
        if ($login) {
            $user = User::get($login);
        }
        if (empty($user)) {
            pl_redirect('admin/accounts');
        }

        $listClient = new MMList(S::user());
        $login = $user->login();
        $registered = ($user->state != 'pending');

        // Form processing
        if (!empty($_POST)) {
            S::assert_xsrf_token();
            if (Post::has('uid') && Post::i('uid') != $user->id()) {
                $page->kill('Une erreur s\'est produite');
            }
        }

        // Handles specific requests (AX sync, su, ...).
        if(Post::has('log_account')) {
            pl_redirect("admin/logger?loguser=$login&year=".date('Y')."&month=".date('m'));
        }

        if(Post::has('su_account') && $registered) {
            if (!Platal::session()->startSUID($user)) {
                $page->trigError('Impossible d\'effectuer un SUID sur ' . $user->login());
            } else {
                pl_redirect("");
            }
        }

        // Handles account deletion.
        if (Post::has('account_deletion_confirmation')) {
            $uid = $user->id();
            $name = $user->fullName();
            $profile = $user->profile();
            if ($profile && Post::b('clear_profile')) {
                $user->profile()->clear();
            }
            $user->clear(true);
            $page->trigSuccess("L'utilisateur $name ($uid) a bien été désinscrit.");
            if (Post::b('erase_account')) {
                XDB::execute('DELETE FROM  accounts
                                    WHERE  uid = {?}',
                             $uid);
                $page->trigSuccess("L'utilisateur $name ($uid) a été supprimé de la base de données");
            }
        }

        // Account Form {{{
        require_once 'emails.inc.php';
        $to_update = array();
        if (Post::has('disable_weak_access')) {
            $to_update['weak_password'] = null;
        } else if (Post::has('update_account')) {
            if (!$user->hasProfile()) {
                require_once 'name.func.inc.php';
                $name_update = false;
                $lastname = capitalize_name(Post::t('lastname'));
                $firstname = capitalize_name(Post::t('firstname'));
                if ($lastname != $user->lastname) {
                    $to_update['lastname'] = $lastname;
                    $name_update = true;
                }
                if (Post::s('type') != 'virtual' && $firstname != $user->firstname) {
                    $to_update['firstname'] = $firstname;
                    $name_update = true;
                }
                if ($name_update) {
                    if (Post::s('type') == 'virtual') {
                        $firstname = '';
                    }
                    $to_update['full_name'] = build_full_name($firstname, $lastname);
                    $to_update['directory_name'] = build_directory_name($firstname, $lastname);
                    $to_update['sort_name'] = build_sort_name($firstname, $lastname);
                }
                if (Post::s('display_name') != $user->displayName()) {
                    $to_update['display_name'] = Post::s('display_name');
                }
            }
            if (Post::s('sex') != ($user->isFemale() ? 'female' : 'male')) {
                $to_update['sex'] = Post::s('sex');
                if ($user->hasProfile()) {
                    XDB::execute('UPDATE  profiles
                                     SET  sex = {?}
                                   WHERE  pid = {?}',
                                 Post::s('sex'), $user->profile()->id());
                }
            }
            if (!Post::blank('pwhash')) {
                $to_update['password'] = Post::s('pwhash');
                require_once 'googleapps.inc.php';
                $account = new GoogleAppsAccount($user);
                if ($account->active() && $account->sync_password) {
                    $account->set_password(Post::s('pwhash'));
                }
            }
            if (!Post::blank('weak_password')) {
                $to_update['weak_password'] = Post::s('weak_password');
            }
            if (Post::i('token_access', 0) != ($user->token_access ? 1 : 0)) {
                $to_update['token'] = Post::i('token_access') ? rand_url_id(16) : null;
            }
            if (Post::i('skin') != $user->skin) {
                $to_update['skin'] = Post::i('skin');
                if ($to_update['skin'] == 0) {
                    $to_update['skin'] = null;
                }
            }
            if (Post::s('state') != $user->state) {
                $to_update['state'] = Post::s('state');
            }
            if (Post::i('is_admin', 0) != ($user->is_admin ? 1 : 0)) {
                $to_update['is_admin'] = Post::b('is_admin');
            }
            if (Post::s('type') != $user->type) {
                $to_update['type'] = Post::s('type');
            }
            if (Post::i('watch', 0) != ($user->watch ? 1 : 0)) {
                $to_update['flags'] = new PlFlagset();
                $to_update['flags']->addFlag('watch', Post::i('watch'));
            }
            if (Post::t('comment') != $user->comment) {
                $to_update['comment'] = Post::blank('comment') ? null : Post::t('comment');
            }
            $new_email = strtolower(Post::t('email'));
            if (require_email_update($user, $new_email)) {
                $to_update['email'] = $new_email;
                $listClient->change_user_email($user->forlifeEmail(), $new_email);
                update_alias_user($user->forlifeEmail(), $new_email);
            }
        }
        if (!empty($to_update)) {
            $res = XDB::query('SELECT  *
                                 FROM  accounts
                                WHERE  uid = {?}', $user->id());
            $oldValues = $res->fetchAllAssoc();
            $oldValues = $oldValues[0];

            $set = array();
            $diff = array();
            foreach ($to_update as $k => $value) {
                $value = XDB::format('{?}', $value);
                $set[] = $k . ' = ' . $value;
                $diff[$k] = array($oldValues[$k], trim($value, "'"));
                unset($oldValues[$k]);
            }
            XDB::rawExecute('UPDATE  accounts
                                SET  ' . implode(', ', $set) . '
                              WHERE  uid = ' . XDB::format('{?}', $user->id()));
            $page->trigSuccess('Données du compte mise à jour avec succès');
            $user = User::getWithUID($user->id());

            /* Formats the $diff and send it to the site administrators. The rules are the folowing:
             *  -formats: password, token, weak_password
             */
            foreach (array('password', 'token', 'weak_password') as $key) {
                if (isset($diff[$key])) {
                    $diff[$key] = array('old value', 'new value');
                } else {
                    $oldValues[$key] = 'old value';
                }
            }

            $mail = new PlMailer('admin/useredit.mail.tpl');
            $mail->assign('admin', S::user()->hruid);
            $mail->assign('hruid', $user->hruid);
            $mail->assign('diff', $diff);
            $mail->assign('oldValues', $oldValues);
            $mail->send();
        }
        // }}}

        // Profile form {{{
        if (Post::has('add_profile') || Post::has('del_profile') || Post::has('owner')) {
            if (Post::i('del_profile', 0) != 0) {
                XDB::execute('DELETE FROM  account_profiles
                                    WHERE  uid = {?} AND pid = {?}',
                                    $user->id(), Post::i('del_profile'));
                XDB::execute('DELETE FROM  profiles
                                    WHERE  pid = {?}',
                                    Post::i('del_profile'));
            } else if (!Post::blank('new_profile')) {
                $profile = Profile::get(Post::t('new_profile'));
                if (!$profile) {
                    $page->trigError('Le profil ' . Post::t('new_profile') . ' n\'existe pas');
                } else {
                    XDB::execute('INSERT IGNORE INTO  account_profiles (uid, pid)
                                              VALUES  ({?}, {?})',
                                 $user->id(), $profile->id());
                }
            }
            XDB::execute('UPDATE  account_profiles
                             SET  perms = IF(pid = {?}, CONCAT(perms, \',owner\'), REPLACE(perms, \'owner\', \'\'))
                           WHERE  uid = {?}',
                         Post::i('owner'), $user->id());
        }
        // }}}

        // Email forwards form {{{
        $redirect = ($registered ? new Redirect($user) : null);
        if (Post::has('add_fwd')) {
            $email = Post::t('email');
            if (!isvalid_email_redirection($email, $user)) {
                $page->trigError("Email non valide: $email");
            } else {
                $redirect->add_email($email);
                $page->trigSuccess("Ajout de $email effectué");
            }
        } else if (!Post::blank('del_fwd')) {
            $redirect->delete_email(Post::t('del_fwd'));
        } else if (!Post::blank('activate_fwd')) {
            $redirect->modify_one_email(Post::t('activate_fwd'), true);
        } else if (!Post::blank('deactivate_fwd')) {
            $redirect->modify_one_email(Post::t('deactivate_fwd'), false);
        } else if (Post::has('disable_fwd')) {
            $redirect->disable();
        } else if (Post::has('enable_fwd')) {
            $redirect->enable();
        } else if (!Post::blank('clean_fwd')) {
            $redirect->clean_errors(Post::t('clean_fwd'));
        }
        // }}}

        // Email alias form {{{
        if (Post::has('add_alias')) {
            // Splits new alias in user and fqdn.
            $alias = Env::t('email');
            if (strpos($alias, '@') !== false) {
                list($alias, $domain) = explode('@', $alias);
            } else {
                $domain = $user->mainEmailDomain();
            }

            // Checks for alias' user validity.
            if (!preg_match('/[-a-z0-9\.]+/s', $alias)) {
                $page->trigError("'$alias' n'est pas un alias valide");
            }

            // Eventually adds the alias to the right domain.
            if ($domain == $globals->mail->alias_dom || $domain == $globals->mail->alias_dom2) {
                $req = new AliasReq($user, $alias, 'Admin request', false);
                if ($req->commit()) {
                    $page->trigSuccess("Nouvel alias '$alias@$domain' attribué.");
                } else {
                    $page->trigError("Impossible d'ajouter l'alias '$alias@$domain', il est probablement déjà attribué.");
                }
            } elseif ($domain == $user->mainEmailDomain()) {
                XDB::execute('INSERT INTO  email_source_account (email, uid, domain, type, flags)
                                   SELECT  {?}, {?}, id, \'alias\', \'\'
                                     FROM  email_virtual_domains
                                    WHERE  name = {?}',
                              $alias, $user->id(), $domain);
                $page->trigSuccess("Nouvel alias '$alias' ajouté");
            } else {
                $page->trigError("Le domaine '$domain' n'est pas valide pour cet utilisateur.");
            }
        } else if (!Post::blank('del_alias')) {
            $delete_alias = Post::t('del_alias');
            list($email, $domain) = explode('@', $delete_alias);
            XDB::execute('DELETE  s
                            FROM  email_source_account  AS s
                      INNER JOIN  email_virtual_domains AS m ON (s.domain = m.id)
                      INNER JOIN  email_virtual_domains AS d ON (d.aliasing = m.id)
                           WHERE  s.email = {?} AND s.uid = {?} AND d.name = {?} AND type != \'forlife\'',
                          $email, $user->id(), $domain);
            XDB::execute('UPDATE  email_redirect_account AS r
                      INNER JOIN  email_virtual_domains  AS m ON (m.name = {?})
                      INNER JOIN  email_virtual_domains  AS d ON (d.aliasing = m.id)
                             SET  r.rewrite = \'\'
                           WHERE  r.uid = {?} AND r.rewrite = CONCAT({?}, \'@\', d.name)',
                         $domain, $user->id(), $email);
            fix_bestalias($user);
            $page->trigSuccess("L'alias '$delete_alias' a été supprimé");
        } else if (!Post::blank('best')) {
            $best_alias = Post::t('best');
            // First delete the bestalias flag from all this user's emails.
            XDB::execute("UPDATE  email_source_account
                             SET  flags = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', flags, ','), ',bestalias,', ','))
                           WHERE  uid = {?}", $user->id());
            // Then gives the bestalias flag to the given email.
            list($email, $domain) = explode('@', $best_alias);
            XDB::execute("UPDATE  email_source_account
                             SET  flags = CONCAT_WS(',', IF(flags = '', NULL, flags), 'bestalias')
                           WHERE  uid = {?} AND email = {?}", $user->id(), $email);

            // As having a non-null bestalias value is critical in
            // plat/al's code, we do an a posteriori check on the
            // validity of the bestalias.
            fix_bestalias($user);
        }
        // }}}

        // OpenId form {{{
        if (Post::has('del_openid')) {
            XDB::execute('DELETE FROM  account_auth_openid
                                WHERE  id = {?}', Post::i('del_openid'));
        }
        // }}}

        // Forum form {{{
        if (Post::has('b_edit')) {
            XDB::execute("DELETE FROM  forum_innd
                                WHERE  uid = {?}", $user->id());
            if (Env::v('write_perm') != "" || Env::v('read_perm') != ""  || Env::v('commentaire') != "" ) {
                XDB::execute("INSERT INTO  forum_innd
                                      SET  ipmin = '0', ipmax = '4294967295',
                                           write_perm = {?}, read_perm = {?},
                                           comment = {?}, priority = '200', uid = {?}",
                             Env::v('write_perm'), Env::v('read_perm'), Env::v('comment'), $user->id());
            }
        }
        // }}}


        $page->addJsLink('jquery.ui.xorg.js');

        // Displays last login and last host information.
        $res = XDB::query("SELECT  start, host
                             FROM  log_sessions
                            WHERE  uid = {?} AND suid IS NULL
                         ORDER BY  start DESC
                            LIMIT  1", $user->id());
        list($lastlogin,$host) = $res->fetchOneRow();
        $page->assign('lastlogin', $lastlogin);
        $page->assign('host', $host);

        // Display mailing lists
        $page->assign('mlists', $listClient->get_all_user_lists($user->forlifeEmail()));

        // Display active aliases.
        $page->assign('virtuals', $user->emailGroupAliases());
        $aliases = XDB::iterator("SELECT  CONCAT(s.email, '@', d.name) AS email, (s.type = 'forlife') AS forlife,
                                          (s.email REGEXP '\\\\.[0-9]{2}$') AS hundred_year,
                                          FIND_IN_SET('bestalias', s.flags) AS bestalias, s.expire,
                                          (s.type = 'alias_aux') AS alias
                                    FROM  email_source_account  AS s
                              INNER JOIN  email_virtual_domains AS d ON (s.domain = d.id)
                                   WHERE  s.uid = {?}
                                ORDER BY  !alias, s.email",
                                 $user->id());
        $page->assign('aliases', $aliases);
        $page->assign('account_types', XDB::iterator('SELECT * FROM account_types ORDER BY type'));
        $page->assign('skins', XDB::iterator('SELECT id, name FROM skins ORDER BY name'));
        $page->assign('profiles', XDB::iterator('SELECT  p.pid, p.hrpid, FIND_IN_SET(\'owner\', ap.perms) AS owner, p.ax_id
                                                   FROM  account_profiles AS ap
                                             INNER JOIN  profiles AS p ON (ap.pid = p.pid)
                                                  WHERE  ap.uid = {?}', $user->id()));
        $page->assign('openid', XDB::iterator('SELECT  id, url
                                                 FROM  account_auth_openid
                                                WHERE  uid = {?}', $user->id()));

        // Displays email redirection and the general profile.
        if ($registered && $redirect) {
            $page->assign('emails', $redirect->emails);
        }

        $page->assign('user', $user);
        $page->assign('hasProfile', $user->hasProfile());

        // Displays forum bans.
        $res = XDB::query("SELECT  write_perm, read_perm, comment
                             FROM  forum_innd
                            WHERE  uid = {?}", $user->id());
        $bans = $res->fetchOneAssoc();
        $page->assign('bans', $bans);
    }

    private static function getHrid($firstname, $lastname, $promo)
    {
        if ($firstname != null && $lastname != null && $promo != null) {
            return User::makeHrid($firstname, $lastname, $promo);
        }
        return null;
    }

    private static function formatNewUser($page, $infosLine, $separator, $promo, $size)
    {
        $infos = explode($separator, $infosLine);
        if (sizeof($infos) > $size || sizeof($infos) < 2) {
            $page->trigError("La ligne $infosLine n'a pas été ajoutée.");
            return false;
        }

        $infos = array_map('trim', $infos);
        $hrid = self::getHrid($infos[1], $infos[0], $promo);
        $res1 = XDB::query('SELECT  COUNT(*)
                              FROM  accounts
                             WHERE  hruid = {?}', $hrid);
        $res2 = XDB::query('SELECT  COUNT(*)
                              FROM  profiles
                             WHERE  hrpid = {?}', $hrid);
        if (is_null($hrid) || $res1->fetchOneCell() > 0 || $res2->fetchOneCell() > 0) {
            $page->trigError("La ligne $infosLine n'a pas été ajoutée: une entrée similaire existe déjà");
            return false;
        }
        $infos['hrid'] = $hrid;
        return $infos;
    }

    private static function formatSex($page, $sex, $line)
    {
        switch ($sex) {
          case 'F':
            return 'female';
          case 'M':
            return 'male';
          default:
            $page->trigError("La ligne $line n'a pas été ajoutée car le sexe $sex n'est pas pris en compte.");
            return null;
        }
    }

    private static function formatBirthDate($birthDate)
    {
        // strtotime believes dd/mm/yyyy to be an US date (i.e mm/dd/yyyy), and
        // dd-mm-yyyy to be a normal date (i.e dd-mm-yyyy)...
        return date("Y-m-d", strtotime(str_replace('/', '-', $birthDate)));
    }

    function handler_add_accounts($page, $action = null, $promo = null)
    {
        require_once 'name.func.inc.php';
        $page->changeTpl('admin/add_accounts.tpl');

        if (Env::has('add_type') && Env::has('people')) {
            static $titles = array('male' => 'M', 'female' => 'MLLE');
            $lines = explode("\n", Env::t('people'));
            $separator = Env::t('separator');
            $promotion = Env::i('promotion');

            if (Env::t('add_type') == 'promo') {
                $eduSchools = DirEnum::getOptions(DirEnum::EDUSCHOOLS);
                $eduSchools = array_flip($eduSchools);
                $eduDegrees = DirEnum::getOptions(DirEnum::EDUDEGREES);
                $eduDegrees = array_flip($eduDegrees);
                switch (Env::t('edu_type')) {
                  case 'X':
                    $degreeid = $eduDegrees[Profile::DEGREE_X];
                    $entry_year = $promotion;
                    $grad_year = $promotion + 3;
                    $promo = 'X' . $promotion;
                    $hrpromo = $promotion;
                    $type = 'x';
                    break;
                  case 'M':
                    $degreeid = $eduDegrees[Profile::DEGREE_M];
                    $grad_year = $promotion;
                    $entry_year = $promotion - 2;
                    $promo = 'M' . $promotion;
                    $hrpromo = $promo;
                    $type = 'master';
                    break;
                  case 'D':
                    $degreeid = $eduDegrees[Profile::DEGREE_D];
                    $grad_year = $promotion;
                    $entry_year = $promotion - 3;
                    $promo = 'D (en cours)';
                    $hrpromo = 'D' . $promotion;
                    $type = 'phd';
                    break;
                  default:
                    $page->killError("La formation n'est pas reconnue : " . Env::t('edu_type') . '.');
                }
                $best_domain = XDB::fetchOneCell('SELECT  id
                                                    FROM  email_virtual_domains
                                                   WHERE  name = {?}',
                                                 User::$sub_mail_domains[$type] . Platal::globals()->mail->domain);

                XDB::startTransaction();
                foreach ($lines as $line) {
                    if ($infos = self::formatNewUser($page, $line, $separator, $hrpromo, 6)) {
                        $sex = self::formatSex($page, $infos[3], $line);
                        $lastname = capitalize_name($infos[0]);
                        $firstname = capitalize_name($infos[1]);
                        if (!is_null($sex)) {
                            $fullName = build_full_name($firstname, $lastname);
                            $directoryName = build_directory_name($firstname, $lastname);
                            $sortName = build_sort_name($firstname, $lastname);
                            $birthDate = self::formatBirthDate($infos[2]);
                            if ($type == 'x') {
                                $xorgId = Profile::getXorgId($infos[4]);
                            } elseif (isset($infos[4])) {
                                $xorgId = trim($infos[4]);
                            } else {
                                $xorgId = 0;
                            }
                            if (is_null($xorgId)) {
                                $page->trigError("La ligne $line n'a pas été ajoutée car le matricule École est mal renseigné.");
                                continue;
                            }

                            XDB::execute('INSERT INTO  profiles (hrpid, xorg_id, ax_id, birthdate_ref, sex, title)
                                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?})',
                                         $infos['hrid'], $xorgId, (isset($infos[5]) ? $infos[5] : null),
                                         $birthDate, $sex, $titles[$sex]);
                            $pid = XDB::insertId();
                            XDB::execute('INSERT INTO  profile_public_names (pid, lastname_initial, lastname_main, firstname_initial, firstname_main)
                                               VALUES  ({?}, {?}, {?}, {?}, {?})',
                                         $pid, $lastname, $lastname, $firstname, $firstname);
                            XDB::execute('INSERT INTO  profile_display (pid, yourself, public_name, private_name,
                                                                        directory_name, short_name, sort_name, promo)
                                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                                         $pid, $firstname, $fullName, $fullName, $directoryName, $fullName, $sortName, $promo);
                            XDB::execute('INSERT INTO  profile_education (id, pid, eduid, degreeid, entry_year, grad_year, promo_year, flags)
                                               VALUES  (100, {?}, {?}, {?}, {?}, {?}, {?}, \'primary\')',
                                         $pid, $eduSchools[Profile::EDU_X], $degreeid, $entry_year, $grad_year, $promotion);
                            XDB::execute('INSERT INTO  accounts (hruid, type, is_admin, state, full_name, directory_name,
                                                                 sort_name, display_name, lastname, firstname, sex, best_domain)
                                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                                         $infos['hrid'], $type, 0, 'pending', $fullName, $directoryName, $sortName,
                                         $firstname, $lastname, $firstname, $sex, $best_domain);
                            $uid = XDB::insertId();
                            XDB::execute('INSERT INTO  account_profiles (uid, pid, perms)
                                               VALUES  ({?}, {?}, {?})',
                                         $uid, $pid, 'owner');
                            Profile::rebuildSearchTokens($pid, false);
                        }
                    }
                }
                XDB::commit();
            } else if (Env::t('add_type') == 'account') {
                $type = Env::t('type');
                $newAccounts = array();
                foreach ($lines as $line) {
                    if ($infos = self::formatNewUser($page, $line, $separator, $type, 4)) {
                        $sex = self::formatSex($page, $infos[3], $line);
                        if (!is_null($sex)) {
                            $lastname = capitalize_name($infos[0]);
                            $firstname = capitalize_name($infos[1]);
                            $fullName = build_full_name($firstname, $lastname);
                            $directoryName = build_directory_name($firstname, $lastname);
                            $sortName = build_sort_name($firstname, $lastname);
                            XDB::execute('INSERT INTO  accounts (hruid, type, is_admin, state, email, full_name, directory_name,
                                                                 sort_name, display_name, lastname, firstname, sex)
                                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                                         $infos['hrid'], $type, 0, 'pending', $infos[2], $fullName, $directoryName,
                                         $sortName ,$firstname, $lastname, $firstname, $sex);
                            $newAccounts[$infos['hrid']] = $fullName;
                        }
                    }
                }
                if (!empty($newAccounts)) {
                    $page->assign('newAccounts', $newAccounts);
                }
            } else if (Env::t('add_type') == 'ax_id') {
                $type = 'x';
                foreach ($lines as $line) {
                    $infos = explode($separator, $line);
                    if (sizeof($infos) > 3 || sizeof($infos) < 2) {
                        $page->trigError("La ligne $line n'a pas été ajoutée : mauvais nombre de champs.");
                        continue;
                    }
                    $infos = array_map('trim', $infos);
                    if (sizeof($infos) == 3) {
                        // Get human readable ID with first name and last name
                        $hrid = User::makeHrid($infos[1], $infos[0], $promotion);
                        $user = User::getSilent($hrid);
                        $axid = $infos[2];
                    } else {
                        // The first column is the hrid, possibly without the promotion
                        $user = User::getSilent($infos[0] . '.' . $promotion);
                        if (is_null($user)) {
                            $user = User::getSilent($infos[0]);
                        }
                        $axid = $infos[1];
                    }
                    if (!$axid) {
                        $page->trigError("La ligne $line n'a pas été ajoutée : matricule AX vide.");
                        continue;
                    }
                    if (is_null($user)) {
                        $page->trigError("La ligne $line n'a pas été ajoutée : aucun compte trouvé.");
                        continue;
                    }
                    $profile = $user->profile();
                    if ($profile->ax_id) {
                        $page->trigError("Le profil " . $profile->hrpid . " a déjà l'ID AX " . $profile->ax_id);
                        continue;
                    }
                    XDB::execute('UPDATE  profiles
                                     SET  ax_id = {?}
                                   WHERE  pid = {?}',
                                 $axid, $profile->id());

                }
            }

            $errors = $page->nb_errs();
            if ($errors == 0) {
                $page->trigSuccess("L'opération a été effectuée avec succès.");
            } else {
                $page->trigSuccess('L\'opération a été effectuée avec succès, sauf pour '
                                   . (($errors == 1) ? 'l\'erreur signalée' : "les $errors erreurs signalées") . ' ci-dessus.');
            }
        } else if (Env::has('add_type')) {
            $res = XDB::query('SELECT  type
                                 FROM  account_types');
            $page->assign('account_types', $res->fetchColumn());
            $page->assign('add_type', Env::s('add_type'));
        }
    }

    function handler_homonyms($page, $op = 'list', $target = null)
    {
        $page->changeTpl('admin/homonymes.tpl');
        $page->setTitle('Administration - Homonymes');
        $this->load("homonyms.inc.php");

        if ($target) {
            $user = User::getSilentWithUID($target);
            if (!$user || !($loginbis = select_if_homonym($user))) {
                $target = 0;
            } else {
                $page->assign('user', $user);
                $page->assign('loginbis',$loginbis);
            }
        }

        $page->assign('op', $op);
        $page->assign('target', $target);

        // When we have a valid target, prepare emails.
        if ($target) {
            // Examine what operation needs to be performed.
            switch ($op) {
                case 'mail':
                    S::assert_xsrf_token();

                    send_warning_homonym($user, $loginbis);
                    $op = 'list';
                    $page->trigSuccess('Email envoyé à ' . $user->forlifeEmail() . '.');
                    break;

                case 'correct':
                    S::assert_xsrf_token();

                    fix_homonym($user, $loginbis);
                    send_robot_homonym($user, $loginbis);
                    $op = 'list';
                    $page->trigSuccess('Email envoyé à ' . $user->forlifeEmail() . ', alias supprimé.');
                    break;
            }
        }

        if ($op == 'list') {
            // Retrieves homonyms that are already been fixed.
            $res = XDB::iterator('SELECT  o.email AS homonym, f.email AS forlife, o.expire, f.uid
                                    FROM  email_source_other    AS o
                              INNER JOIN  homonyms_list         AS h ON (o.hrmid = h.hrmid)
                              INNER JOIN  email_source_account  AS f ON (h.uid = f.uid AND f.type = \'forlife\')
                                   WHERE  o.expire IS NOT NULL
                                ORDER BY  homonym, forlife');
            $homonyms = array();
            while ($item = $res->next()) {
                $homonyms[$item['homonym']][] = $item;
            }
            $page->assign_by_ref('homonyms', $homonyms);

            // Retrieves homonyms that needs to be fixed.
            $res = XDB::iterator('SELECT  e.email AS homonym, f.email AS forlife, e.expire, e.uid, (e.expire < NOW()) AS urgent
                                    FROM  email_source_account  AS e
                              INNER JOIN  homonyms_list         AS l ON (e.uid = l.uid)
                              INNER JOIN  homonyms_list         AS h ON (l.hrmid = h.hrmid)
                              INNER JOIN  email_source_account  AS f ON (h.uid = f.uid AND f.type = \'forlife\')
                                   WHERE  e.expire IS NOT NULL
                                ORDER BY  homonym, forlife');
            $homonyms_to_fix = array();
            while ($item = $res->next()) {
                $homonyms_to_fix[$item['homonym']][] = $item;
            }
            $page->assign_by_ref('homonyms_to_fix', $homonyms_to_fix);
        }

        if ($op == 'correct-conf') {
            $page->assign('robot_mail_text', get_robot_mail_text($user, $loginbis));
        }

        if ($op == 'mail-conf') {
            $page->assign('warning_mail_text', get_warning_mail_text($user, $loginbis));
        }
    }

    function handler_deaths($page, $promo = 0, $validate = false)
    {
        $page->changeTpl('admin/deces_promo.tpl');
        $page->setTitle('Administration - Deces');

        if (!$promo) {
            $promo = Env::t('promo', 'X1923');
        }
        $page->assign('promo', $promo);
        if (!$promo) {
            return;
        }

        if ($validate) {
            S::assert_xsrf_token();

            $res = XDB::iterRow('SELECT  p.pid, pd.directory_name, p.deathdate
                                   FROM  profiles AS p
                             INNER JOIN  profile_display AS pd ON (p.pid = pd.pid)
                                  WHERE  pd.promo = {?}', $promo);
            while (list($pid, $name, $death) = $res->next()) {
                $val = Env::v('death_' . $pid);
                if ($val == $death) {
                    continue;
                }

                if (empty($val)) {
                    $val = null;
                }
                XDB::execute('UPDATE  profiles
                                 SET  deathdate = {?}, deathdate_rec = NOW()
                               WHERE  pid = {?}', $val, $pid);

                $page->trigSuccess('Édition du décès de ' . $name . ' (' . ($val ? $val : 'ressuscité') . ').');
                if ($val && ($death == '0000-00-00' || empty($death))) {
                    $profile = Profile::get($pid);
                    $profile->clear();
                    $profile->owner()->clear(false);
                }
            }
        }

        $res = XDB::iterator('SELECT  p.pid, pd.directory_name, p.deathdate
                                FROM  profiles AS p
                          INNER JOIN  profile_display AS pd ON (p.pid = pd.pid)
                               WHERE  pd.promo = {?}
                            ORDER BY  pd.sort_name', $promo);
        $page->assign('profileList', $res);
    }

    function handler_dead_but_active($page)
    {
        $page->changeTpl('admin/dead_but_active.tpl');
        $page->setTitle('Administration - Décédés');

        $res = XDB::iterator(
                "SELECT  a.hruid, pd.promo, p.ax_id, pd.directory_name, p.deathdate, DATE(MAX(s.start)) AS last
                   FROM  accounts         AS a
             INNER JOIN  account_profiles AS ap ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
             INNER JOIN  profiles         AS p ON (p.pid = ap.pid)
             INNER JOIN  profile_display  AS pd ON (pd.pid = p.pid)
              LEFT JOIN  log_sessions        AS s ON (s.uid = a.uid AND suid = 0)
                  WHERE  a.state = 'active' AND p.deathdate IS NOT NULL
               GROUP BY  a.uid
               ORDER BY  pd.promo, pd.sort_name");
        $page->assign('dead', $res);
    }

    function handler_validate($page, $action = 'list', $id = null)
    {
        $page->changeTpl('admin/validation.tpl');
        $page->setTitle('Administration - Valider une demande');
        $page->addCssLink('nl.Polytechnique.org.css');

        if ($action == 'edit' && !is_null($id)) {
            $page->assign('preview_id', $id);
        } else {
            $page->assign('preview_id', null);
        }

        if(Env::has('uid') && Env::has('type') && Env::has('stamp')) {
            S::assert_xsrf_token();

            $req = Validate::get_typed_request(Env::v('uid'), Env::v('type'), Env::v('stamp'));
            if ($req) {
                $req->handle_formu();
            } else {
                $page->trigWarning('La validation a déjà été effectuée.');
            }
        }

        $r = XDB::iterator('SHOW COLUMNS FROM requests_answers');
        while (($a = $r->next()) && $a['Field'] != 'category');
        $categories = explode(',', str_replace("'", '', substr($a['Type'], 5, -1)));
        sort($categories);
        $page->assign('categories', $categories);

        $hidden = array();
        $res = XDB::query('SELECT  hidden_requests
                             FROM  requests_hidden
                            WHERE  uid = {?}', S::v('uid'));
        $hide_requests = $res->fetchOneCell();
        if (Post::has('hide')) {
            $hide = array();
            foreach ($categories as $cat)
                if (!Post::v($cat)) {
                    $hidden[$cat] = 1;
                    $hide[] = $cat;
                }
            $hide_requests = join(',', $hide);
            XDB::query('INSERT INTO  requests_hidden (uid, hidden_requests)
                             VALUES  ({?}, {?})
            ON DUPLICATE KEY UPDATE  hidden_requests = VALUES(hidden_requests)',
                       S::v('uid'), $hide_requests);
        } elseif ($hide_requests)  {
            foreach (explode(',', $hide_requests) as $hide_type)
                $hidden[$hide_type] = true;
        }
        $page->assign('hide_requests', $hidden);

        // Update the count of item to validate here... useful in development configuration
        // where several copies of the site use the same DB, but not the same "dynamic configuration"
        global $globals;
        $globals->updateNbValid();
        $page->assign('vit', Validate::iterate());
        $page->assign('isAdmin', S::admin());
    }

    function handler_validate_answers($page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Réponses automatiques de validation');
        $page->assign('title', 'Gestion des réponses automatiques');
        $table_editor = new PLTableEditor('admin/validate/answers','requests_answers','id');
        $table_editor->describe('category','catégorie',true);
        $table_editor->describe('title','titre',true);
        $table_editor->describe('answer','texte',false, true);
        $table_editor->apply($page, $action, $id);
    }

    function handler_skins($page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Skins');
        $page->assign('title', 'Gestion des skins');
        $table_editor = new PLTableEditor('admin/skins','skins','id');
        $table_editor->describe('name','nom',true);
        $table_editor->describe('skin_tpl','nom du template',true);
        $table_editor->describe('auteur','auteur',false, true);
        $table_editor->describe('comment','commentaire',true);
        $table_editor->describe('date','date',false, true);
        $table_editor->describe('ext','extension du screenshot',false, true);
        $table_editor->apply($page, $action, $id);
    }

    function handler_postfix_blacklist($page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Postfix : Blacklist');
        $page->assign('title', 'Blacklist de postfix');
        $table_editor = new PLTableEditor('admin/postfix/blacklist','postfix_blacklist','email', true);
        $table_editor->describe('reject_text','Texte de rejet',true);
        $table_editor->describe('email','email',true);
        $table_editor->apply($page, $action, $id);
    }

    function handler_postfix_whitelist($page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Postfix : Whitelist');
        $page->assign('title', 'Whitelist de postfix');
        $table_editor = new PLTableEditor('admin/postfix/whitelist','postfix_whitelist','email', true);
        $table_editor->describe('email','email',true);
        $table_editor->apply($page, $action, $id);
    }

    function handler_mx_broken($page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - MX Défaillants');
        $page->assign('title', 'MX Défaillant');
        $table_editor = new PLTableEditor('admin/mx/broken', 'mx_watch', 'host', true);
        $table_editor->describe('host', 'Masque', true);
        $table_editor->describe('state', 'Niveau', true);
        $table_editor->describe('text', 'Description du problème', false, true);
        $table_editor->apply($page, $action, $id);
    }

    function handler_logger_actions($page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Actions');
        $page->assign('title', 'Gestion des actions de logger');
        $table_editor = new PLTableEditor('admin/logger/actions','log_actions','id');
        $table_editor->describe('text','intitulé',true);
        $table_editor->describe('description','description',true);
        $table_editor->apply($page, $action, $id);
    }

    function handler_downtime($page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Coupures');
        $page->assign('title', 'Gestion des coupures');
        $table_editor = new PLTableEditor('admin/downtime','downtimes','id');
        $table_editor->describe('debut','date',true);
        $table_editor->describe('duree','durée',false, true);
        $table_editor->describe('resume','résumé',true);
        $table_editor->describe('services','services affectés',true);
        $table_editor->describe('description','description',false, true);
        $table_editor->apply($page, $action, $id);
    }

    private static function isCountryIncomplete(array &$item)
    {
        $warning = false;
        foreach (array('worldRegion', 'country', 'capital', 'phonePrefix', 'licensePlate', 'countryPlain') as $field) {
            if ($item[$field] == '') {
                $item[$field . '_warning'] = true;
                $warning = true;
            }
        }
        if (is_null($item['belongsTo'])) {
            foreach (array('nationality', 'nationalityEn') as $field) {
                if ($item[$field] == '') {
                    $item[$field . '_warning'] = true;
                    $warning = true;
                }
            }
        }
        return $warning;
    }

    private static function updateCountry(array $item)
    {
        XDB::execute('UPDATE  geoloc_countries
                         SET  countryPlain = {?}
                       WHERE  iso_3166_1_a2 = {?}',
                     mb_strtoupper(replace_accent($item['country'])), $item['iso_3166_1_a2']);
    }

    private static function isLanguageIncomplete(array &$item)
    {
        if ($item['language'] == '') {
            $item['language_warning'] = true;
            return true;
        }
        return false;
    }

    private static function updateLanguage(array $item) {}

    function handler_geocoding($page, $category = null, $action = null, $id = null)
    {
        // Warning, this handler requires the following packages:
        //  * pkg-isocodes
        //  * isoquery

        static $properties = array(
            'country'  => array(
                'name'         => 'pays',
                'isocode'      => '3166',
                'table'        => 'geoloc_countries',
                'id'           => 'iso_3166_1_a2',
                'main_fields'  => array('iso_3166_1_a3', 'iso_3166_1_num', 'countryEn'),
                'other_fields' => array('worldRegion', 'country', 'capital', 'nationality', 'nationalityEn',
                                        'phonePrefix', 'phoneFormat', 'licensePlate', 'belongsTo')
            ),
            'language' => array(
                'name'         => 'langages',
                'isocode'      => '639',
                'table'        => 'profile_langskill_enum',
                'id'           => 'iso_639_2b',
                'main_fields'  => array('iso_639_2t', 'iso_639_1', 'language_en'),
                'other_fields' => array('language')

            )
        );

        if (is_null($category) || !array_key_exists($category, $properties)) {
            pl_redirect('admin');
        }

        $data = $properties[$category];

        if ($action == 'edit' || $action == 'add') {
            $main_fields = array_merge(array($data['id']), $data['main_fields']);
            $all_fields = array_merge($main_fields, $data['other_fields']);

            if (is_null($id)) {
                if (Post::has('new_id')) {
                    $id = Post::v('new_id');
                } else {
                    pl_redirect('admin/geocoding/' . $category);
                }
            }

            $list = array();
            exec('isoquery --iso=' . $data['isocode'] . ' ' . $id, $list);
            if (count($list) == 1) {
                $array = explode("\t", $list[0]);
                foreach ($main_fields as $i => $field) {
                    $iso[$field] = $array[$i];
                }
            } else {
                $iso = array();
            }

            if ($action == 'add') {
                if (Post::has('new_id')) {
                    S::assert_xsrf_token();
                }

                if (count($iso)) {
                    $item = $iso;
                } else {
                    $item = array($data['id'] => $id);
                }
                XDB::execute('INSERT INTO  ' . $data['table'] . '(' . implode(', ', array_keys($item)) . ')
                                   VALUES  ' . XDB::formatArray($item));
                $page->trigSuccess($id . ' a bien été ajouté à la base.');
            } elseif ($action == 'edit') {
                if (Post::has('edit')) {
                    S::assert_xsrf_token();

                    $item = array();
                    $set  = array();
                    foreach ($all_fields as $field) {
                        $item[$field] = Post::t($field);
                        $set[] = $field . XDB::format(' = {?}', ($item[$field] ? $item[$field] : null));
                    }
                    XDB::execute('UPDATE  ' . $data['table'] . '
                                     SET  ' . implode(', ', $set) . '
                                   WHERE  ' . $data['id'] . ' = {?}',
                                 $id);
                    call_user_func_array(array('self', 'update' . ucfirst($category)), array($item));
                    $page->trigSuccess($id . ' a bien été mis à jour.');
                } elseif (Post::has('del')) {
                    S::assert_xsrf_token();

                    XDB::execute('DELETE FROM  ' . $data['table'] . '
                                        WHERE  ' . $data['id'] . ' = {?}',
                                 $id);
                    $page->trigSuccessRedirect($id . ' a bien été supprimé.', 'admin/geocoding/' . $category);
                } else {
                    $item = XDB::fetchOneAssoc('SELECT  *
                                                  FROM  ' . $data['table'] . '
                                                 WHERE  ' . $data['id'] . ' = {?}',
                                               $id);
                }
            }

            $page->changeTpl('admin/geocoding_edit.tpl');
            $page->setTitle('Administration - ' . ucfirst($data['name']));
            $page->assign('category', $category);
            $page->assign('name', $data['name']);
            $page->assign('all_fields', $all_fields);
            $page->assign('id', $id);
            $page->assign('iso', $iso);
            $page->assign('item', $item);
            return;
        }

        $page->changeTpl('admin/geocoding.tpl');
        $page->setTitle('Administration - ' . ucfirst($data['name']));
        $page->assign('category', $category);
        $page->assign('name', $data['name']);
        $page->assign('id', $data['id']);
        $page->assign('main_fields', $data['main_fields']);
        $page->assign('all_fields', array_merge($data['main_fields'], $data['other_fields']));

        // First build the list provided by the iso codes.
        $list = array();
        exec('isoquery --iso=' . $data['isocode'], $list);

        foreach ($list as $key => $item) {
            $array = explode("\t", $item);
            unset($list[$key]);
            $list[$array[0]] = array();
            foreach ($data['main_fields'] as $i => $field) {
                $list[$array[0]][$field] = $array[$i + 1];
            }
        }
        ksort($list);

        // Retrieve all data from the database.
        $db_list = XDB::rawFetchAllAssoc('SELECT  *
                                            FROM  ' . $data['table'] . '
                                        ORDER BY  ' . $data['id'],
                                         $data['id']);

        // Sort both iso and database data into 5 categories:
        //  $missing: data from the iso list not in the database,
        //  $non_existing: data from the database not in the iso list,
        //  $erroneous: data that differ on main fields,
        //  $incomplete: data with empty fields in the data base,
        //  $remaining: remaining correct and complete data from the database.

        $missing = $non_existing = $erroneous = $incomplete = $remaining = array();
        foreach (array_keys($list) as $id) {
            if (!array_key_exists($id, $db_list)) {
                $missing[$id] = $list[$id];
            }
        }

        foreach ($db_list as $id => $item) {
            if (!array_key_exists($id, $list)) {
                $non_existing[$id] = $item;
            } else {
                $error = false;
                foreach ($data['main_fields'] as $field) {
                    if ($item[$field] != $list[$id][$field]) {
                        $item[$field . '_error'] = true;
                        $error = true;
                    }
                }
                if ($error == true) {
                    $erroneous[$id] = $item;
                } elseif (call_user_func_array(array('self', 'is' . ucfirst($category) . 'Incomplete'), array(&$item))) {
                    $incomplete[$id] = $item;
                } else {
                    $remaining[$id] = $item;
                }
            }
        }

        $page->assign('lists', array(
                'manquant'  => $missing,
                'disparu'   => $non_existing,
                'erroné'    => $erroneous,
                'incomplet' => $incomplete,
                'restant'   => $remaining
        ));
    }

    function handler_accounts(PlPage $page)
    {
        $page->changeTpl('admin/accounts.tpl');
        $page->setTitle('Administration - Comptes');

        if (Post::has('create_account')) {
            S::assert_xsrf_token();
            $firstname = Post::t('firstname');
            $lastname = mb_strtoupper(Post::t('lastname'));
            $sex = Post::s('sex');
            $email = Post::t('email');
            $type = Post::s('type');
            if (!$type) {
                $page->trigError("Empty account type");
            } elseif (!isvalid_email($email)) {
                $page->trigError("Invalid email address: $email");
            } elseif (strlen(Post::s('pwhash')) != 40) {
                $page->trigError("Invalid password hash");
            } else {
                $login = PlUser::makeHrid($firstname, $lastname, $type);
                $full_name = $firstname . ' ' . $lastname;
                $directory_name = $lastname . ' ' . $firstname;
                XDB::execute("INSERT INTO  accounts (hruid, type, state, password,
                                                     registration_date, email, full_name,
                                                     display_name, sex, directory_name,
                                                     lastname, firstname)
                                   VALUES  ({?}, {?}, 'active', {?}, NOW(), {?}, {?}, {?}, {?}, {?}, {?}, {?})",
                             $login, $type, Post::s('pwhash'), $email, $full_name, $full_name, $sex,
                             $directory_name, $lastname, $firstname);
            }
        }

        $uf = new UserFilter(new UFC_AccountType('ax', 'school', 'fx'));
        $page->assign('users', $uf->iterUsers());

    }

    function handler_account_types($page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Types de comptes');
        $page->assign('title', 'Gestion des types de comptes');
        $table_editor = new PLTableEditor('admin/account/types', 'account_types', 'type', true);
        $table_editor->describe('type', 'Catégorie', true);
        $table_editor->describe('perms', 'Permissions associées', true);
        $table_editor->apply($page, $action, $id);

        $page->trigWarning(
            'Le niveau de visibilité "ax", utilisé par la permission "directory_ax", ' .
            'correspond à la visibilité dans l\'annuaire papier.');
    }

    function handler_wiki($page, $action = 'list', $wikipage = null, $wikipage2 = null)
    {
        if (S::hasAuthToken()) {
           $page->setRssLink('Changement Récents',
                             '/Site/AllRecentChanges?action=rss&user=' . S::v('hruid') . '&hash=' . S::user()->token);
        }

        // update wiki perms
        if ($action == 'update') {
            S::assert_xsrf_token();

            $perms_read = Post::v('read');
            $perms_edit = Post::v('edit');
            if ($perms_read || $perms_edit) {
                foreach ($_POST as $wiki_page => $val) {
                    if ($val == 'on') {
                        $wp = new PlWikiPage(str_replace(array('_', '/'), '.', $wiki_page));
                        if ($wp->setPerms($perms_read ? $perms_read : $wp->readPerms(),
                                          $perms_edit ? $perms_edit : $wp->writePerms())) {
                            $page->trigSuccess("Permission de la page $wiki_page mises à jour");
                        } else {
                            $page->trigError("Impossible de mettre les permissions de la page $wiki_page à jour");
                        }
                    }
                }
            }
        } else if ($action != 'list' && !empty($wikipage)) {
            $wp = new PlWikiPage($wikipage);
            S::assert_xsrf_token();

            if ($action == 'delete') {
                if ($wp->delete()) {
                    $page->trigSuccess("La page ".$wikipage." a été supprimée.");
                } else {
                    $page->trigError("Impossible de supprimer la page ".$wikipage.".");
                }
            } else if ($action == 'rename' && !empty($wikipage2) && $wikipage != $wikipage2) {
                if ($changedLinks = $wp->rename($wikipage2)) {
                    $s = 'La page <em>'.$wikipage.'</em> a été déplacée en <em>'.$wikipage2.'</em>.';
                    if (is_numeric($changedLinks)) {
                        $s .= $changedLinks.' lien'.(($changedLinks>1)?'s ont été modifiés.':' a été modifié.');
                    }
                    $page->trigSuccess($s);
                } else {
                    $page->trigError("Impossible de déplacer la page ".$wikipage);
                }
            }
        }

        $perms = PlWikiPage::permOptions();

        // list wiki pages and their perms
        $wiki_pages = PlWikiPage::listPages();
        ksort($wiki_pages);
        $wiki_tree = array();
        foreach ($wiki_pages as $file => $desc) {
            list($cat, $name) = explode('.', $file);
            if (!isset($wiki_tree[$cat])) {
                $wiki_tree[$cat] = array();
            }
            $wiki_tree[$cat][$name] = $desc;
        }

        $page->changeTpl('admin/wiki.tpl');
        $page->assign('wiki_pages', $wiki_tree);
        $page->assign('perms_opts', $perms);
    }

    function handler_ipwatch($page, $action = 'list', $ip = null)
    {
        $page->changeTpl('admin/ipwatcher.tpl');

        $states = array('safe'      => 'Ne pas surveiller',
                        'unsafe'    => 'Surveiller les inscriptions',
                        'dangerous' => 'Surveiller tous les accès',
                        'ban'       => 'Bannir cette adresse');
        $page->assign('states', $states);

        switch (Post::v('action')) {
          case 'create':
            if (trim(Post::v('ipN')) != '') {
                S::assert_xsrf_token();
                Xdb::execute('INSERT IGNORE INTO ip_watch (ip, mask, state, detection, last, uid, description)
                                          VALUES ({?}, {?}, {?}, CURDATE(), NOW(), {?}, {?})',
                             ip_to_uint(trim(Post::v('ipN'))), ip_to_uint(trim(Post::v('maskN'))),
                             Post::v('stateN'), S::i('uid'), Post::v('descriptionN'));
            };
            break;

          case 'edit':
            S::assert_xsrf_token();
            Xdb::execute('UPDATE ip_watch
                             SET state = {?}, last = NOW(), uid = {?}, description = {?}, mask = {?}
                           WHERE ip = {?}', Post::v('stateN'), S::i('uid'), Post::v('descriptionN'),
                          ip_to_uint(Post::v('maskN')), ip_to_uint(Post::v('ipN')));
            break;

          default:
            if ($action == 'delete' && !is_null($ip)) {
                S::assert_xsrf_token();
                Xdb::execute('DELETE FROM ip_watch WHERE ip = {?}', ip_to_uint($ip));
            }
        }
        if ($action != 'create' && $action != 'edit') {
            $action = 'list';
        }
        $page->assign('action', $action);

        if ($action == 'list') {
            $sql = "SELECT  w.ip, IF(s.ip IS NULL,
                                     IF(w.ip = s2.ip, s2.host, s2.forward_host),
                                     IF(w.ip = s.ip, s.host, s.forward_host)),
                            w.mask, w.detection, w.state, a.hruid
                      FROM  ip_watch  AS w
                 LEFT JOIN  log_sessions AS s  ON (s.ip = w.ip)
                 LEFT JOIN  log_sessions AS s2 ON (s2.forward_ip = w.ip)
                 LEFT JOIN  accounts  AS a  ON (a.uid = s.uid)
                  GROUP BY  w.ip, a.hruid
                  ORDER BY  w.state, w.ip, a.hruid";
            $it = Xdb::iterRow($sql);

            $table = array();
            $props = array();
            while (list($ip, $host, $mask, $date, $state, $hruid) = $it->next()) {
                $ip = uint_to_ip($ip);
                $mask = uint_to_ip($mask);
                if (count($props) == 0 || $props['ip'] != $ip) {
                    if (count($props) > 0) {
                        $table[] = $props;
                    }
                    $props = array('ip'        => $ip,
                                   'mask'      => $mask,
                                   'host'      => $host,
                                   'detection' => $date,
                                   'state'     => $state,
                                   'users'     => array($hruid));
                } else {
                    $props['users'][] = $hruid;
                }
            }
            if (count($props) > 0) {
                $table[] = $props;
            }
            $page->assign('table', $table);
        } elseif ($action == 'edit') {
            $sql = "SELECT  w.detection, w.state, w.last, w.description, w.mask,
                            a1.hruid AS edit, a2.hruid AS hruid, s.host
                      FROM  ip_watch  AS w
                 LEFT JOIN  accounts  AS a1 ON (a1.uid = w.uid)
                 LEFT JOIN  log_sessions AS s  ON (w.ip = s.ip)
                 LEFT JOIN  accounts  AS a2 ON (a2.uid = s.uid)
                     WHERE  w.ip = {?}
                  GROUP BY  a2.hruid
                  ORDER BY  a2.hruid";
            $it = Xdb::iterRow($sql, ip_to_uint($ip));

            $props = array();
            while (list($detection, $state, $last, $description, $mask, $edit, $hruid, $host) = $it->next()) {
                if (count($props) == 0) {
                    $props = array('ip'          => $ip,
                                   'mask'        => uint_to_ip($mask),
                                   'host'        => $host,
                                   'detection'   => $detection,
                                   'state'       => $state,
                                   'last'        => $last,
                                   'description' => $description,
                                   'edit'        => $edit,
                                   'users'       => array($hruid));
                } else {
                    $props['users'][] = $hruid;
                }
            }
            $page->assign('ip', $props);
        }
    }

    function handler_icons($page)
    {
        $page->changeTpl('admin/icons.tpl');
        $dh = opendir('../htdocs/images/icons');
        if (!$dh) {
            $page->trigError('Dossier des icones introuvables.');
        }
        $icons = array();
        while (($file = readdir($dh)) !== false) {
            if (strlen($file) > 4 && substr($file,-4) == '.gif') {
                array_push($icons, substr($file, 0, -4));
            }
        }
        sort($icons);
        $page->assign('icons', $icons);
    }

    function handler_account_watch($page)
    {
        $page->changeTpl('admin/accounts.tpl');
        $page->assign('disabled', XDB::iterator('SELECT  a.hruid, FIND_IN_SET(\'watch\', a.flags) AS watch,
                                                         a.state = \'disabled\' AS disabled, a.comment
                                                   FROM  accounts AS a
                                                  WHERE  a.state = \'disabled\' OR FIND_IN_SET(\'watch\', a.flags)
                                               ORDER BY  a.hruid'));
        $page->assign('admins', XDB::iterator('SELECT  a.hruid
                                                 FROM  accounts AS a
                                                WHERE  a.is_admin
                                             ORDER BY  a.hruid'));
    }

    function handler_xnet_without_group($page)
    {
        $page->changeTpl('admin/xnet_without_group.tpl');
        $page->assign('accounts', XDB::iterator('SELECT  a.hruid, a.state
                                                   FROM  accounts      AS a
                                              LEFT JOIN  group_members AS m ON (a.uid = m.uid)
                                                  WHERE  a.type = \'xnet\' AND m.uid IS NULL
                                               ORDER BY  a.state, a.hruid'));
    }

    function handler_jobs($page, $id = -1)
    {
        $page->changeTpl('admin/jobs.tpl');

        if (Env::has('search')) {
            $res = XDB::query("SELECT  id, name, acronym
                                 FROM  profile_job_enum
                                WHERE  name LIKE CONCAT('%', {?}, '%') OR acronym LIKE CONCAT('%', {?}, '%')",
                              Env::t('job'), Env::t('job'));

            if ($res->numRows() <= 20) {
                $page->assign('jobs', $res->fetchAllAssoc());
            } else {
                $page->trigError("Il y a trop d'entreprises correspondant à ton choix. Affine-le !");
            }

            $page->assign('askedJob', Env::v('job'));
            return;
        }

        if (Env::has('edit')) {
            S::assert_xsrf_token();
            $selectedJob = Env::has('selectedJob');

            Phone::deletePhones(0, Phone::LINK_COMPANY, $id);
            Address::deleteAddresses(null, Address::LINK_COMPANY, $id);
            if (Env::has('change')) {
                if (Env::has('newJobId') && Env::i('newJobId') > 0) {
                    XDB::execute('UPDATE  profile_job
                                     SET  jobid = {?}
                                   WHERE  jobid = {?}',
                                 Env::i('newJobId'), $id);
                    XDB::execute('DELETE FROM  profile_job_enum
                                        WHERE  id = {?}',
                                 $id);

                    $page->trigSuccess("L'entreprise a bien été remplacée.");
                } else {
                    $page->trigError("L'entreprise n'a pas été remplacée car l'identifiant fourni n'est pas valide.");
                }
            } else {
                XDB::execute('UPDATE  profile_job_enum
                                 SET  name = {?}, acronym = {?}, url = {?}, email = {?},
                                      SIREN_code = {?}, NAF_code = {?}, AX_code = {?}, holdingid = {?}
                               WHERE  id = {?}',
                             Env::t('name'), Env::t('acronym'), Env::t('url'), Env::t('email'),
                             (Env::t('SIREN') == 0 ? null : Env::t('SIREN')),
                             (Env::t('NAF_code') == 0 ? null : Env::t('NAF_code')),
                             (Env::i('AX_code') == 0 ? null : Env::t('AX_code')),
                             (Env::i('holdingId') == 0 ? null : Env::t('holdingId')), $id);

                $phone = new Phone(array('display' => Env::v('tel'), 'link_id' => $id, 'id' => 0, 'type' => 'fixed',
                                         'link_type' => Phone::LINK_COMPANY, 'pub' => 'public'));
                $fax = new Phone(array('display' => Env::v('fax'), 'link_id' => $id, 'id' => 1, 'type' => 'fax',
                                         'link_type' => Phone::LINK_COMPANY, 'pub' => 'public'));
                $address = new Address(array('jobid' => $id, 'type' => Address::LINK_COMPANY, 'text' => Env::t('address')));
                $phone->save();
                $fax->save();
                $address->save();

                $page->trigSuccess("L'entreprise a bien été mise à jour.");
            }
        }

        if (!Env::has('change') && $id != -1) {
            $res = XDB::query("SELECT  e.id, e.name, e.acronym, e.url, e.email, e.SIREN_code AS SIREN, e.NAF_code, e.AX_code,
                                       h.id AS holdingId, h.name AS holdingName, h.acronym AS holdingAcronym,
                                       t.display_tel AS tel, f.display_tel AS fax, a.text AS address
                                 FROM  profile_job_enum  AS e
                            LEFT JOIN  profile_job_enum  AS h ON (e.holdingid = h.id)
                            LEFT JOIN  profile_phones    AS t ON (t.pid = e.id AND t.link_type = 'hq' AND t.tel_id = 0)
                            LEFT JOIN  profile_phones    AS f ON (f.pid = e.id AND f.link_type = 'hq' AND f.tel_id = 1)
                            LEFT JOIN  profile_addresses AS a ON (a.jobid = e.id AND a.type = 'hq')
                                WHERE  e.id = {?}",
                              $id);

            if ($res->numRows() == 0) {
                $page->trigError('Auncune entreprise ne correspond à cet identifiant.');
            } else {
                $page->assign('selectedJob', $res->fetchOneAssoc());
            }
        }
    }

    function handler_profile($page)
    {
        $page->changeTpl('admin/profile.tpl');

        if (Post::has('checked')) {
            S::assert_xsrf_token();
            $res = XDB::iterator('SELECT  DISTINCT(pm.pid), pd.public_name
                                    FROM  profile_modifications AS pm
                              INNER JOIN  profile_display       AS pd ON (pm.pid = pd.pid)
                                   WHERE  pm.type = \'self\'');

            while ($profile = $res->next()) {
                if (Post::has('checked_' . $profile['pid'])) {
                    XDB::execute('DELETE FROM  profile_modifications
                                        WHERE  type = \'self\' AND pid = {?}', $profile['pid']);

                    $page->trigSuccess('Profil de ' . $profile['public_name'] . ' vérifié.');
                }
            }
        }

        $res = XDB::iterator('SELECT  p.hrpid, pm.pid, pd.directory_name, GROUP_CONCAT(pm.field SEPARATOR \', \') AS field
                                FROM  profile_modifications AS pm
                          INNER JOIN  profiles              AS p  ON (pm.pid = p.pid)
                          INNER JOIN  profile_display       AS pd ON (pm.pid = pd.pid)
                               WHERE  pm.type = \'self\'
                            GROUP BY  pd.directory_name
                            ORDER BY  pm.timestamp DESC, pd.directory_name');
        $page->assign('updates', $res);
    }

    function handler_phd($page, $promo = null, $validate = false)
    {
        $page->changeTpl('admin/phd.tpl');
        $eduDegrees = DirEnum::getOptions(DirEnum::EDUDEGREES);
        $eduDegrees = array_flip($eduDegrees);

        // get the list of the years when phd students are supposed to finish but have not yet been flagged as completed
        $promo_list = XDB::fetchColumn('SELECT  DISTINCT(grad_year)
                                          FROM  profile_education
                                         WHERE  FIND_IN_SET(\'primary\', flags) AND NOT FIND_IN_SET(\'completed\', flags) AND degreeid = {?}
                                      ORDER BY  grad_year',
                                    $eduDegrees[Profile::DEGREE_D]);

        // case when no promo was selected that is the admin/phd page
        if (is_null($promo)) {
            $page->assign('promo_list', $promo_list);
            $page->assign('nothing', count($promo_list) == 0);
            return;
        }

        // case when we want to add a list and we have data, that is admin/phd/bulk/validate
        if ($promo == "bulk" && Post::has('people')) {
            S::assert_xsrf_token();
            $lines = explode("\n", Post::t('people'));
            $separator = Env::t('separator');
            foreach ($lines as $line) {
                $infos = explode($separator, $line);
                if (sizeof($infos) !== 2) {
                    $page->trigError("La ligne $line n'a pas été ajoutée : mauvais nombre de champs.");
                    continue;
                }
                $infos = array_map('trim', $infos);
                // $info[0] is prenom.nom or hrid. We first try the hrid case, then we try over the possible promos.
                // We trigger an error if the search was unsuccessful.
                $user = User::getSilent($infos[0]);
                if (is_null($user)) {
                    foreach($promo_list as $promo_possible) {
                        $user = User::getSilent($infos[0] . '.d' . $promo_possible);
                        if (!is_null($user)) {
                            break;
                        }
                    }
                    if (is_null($user)) {
                        $page->trigError("La ligne $line n'a pas été ajoutée : aucun compte trouvé.");
                        continue;
                    }
                }
                if ($user->type !== 'phd') {
                    $page->trigError("La ligne $line n'a pas été ajoutée : le compte n'est pas celui d'un doctorant.");
                    continue;
                }

                $grad_year = $infos[1];
                if (!$grad_year) {
                    $page->trigError("La ligne $line n'a pas été ajoutée : année de soutenance vide.");
                    continue;
                }
                $profile = $user->profile();
                // We have the pid, we now need the id that completes the PK in profile_education.
                $res = XDB::fetchOneCell('SELECT  pe.id
                                            FROM  profile_education AS pe
                                           WHERE  FIND_IN_SET(\'primary\', pe.flags) AND NOT FIND_IN_SET(\'completed\', pe.flags)
                                                  AND pe.pid = {?}',
                    $profile->id());
                if (!$res) {
                    $page->trigError("Le profil " . $profile->hrid() . " a déjà une année de soutenance indiquée.");
                    continue;
                }
                // When we are here, we have the pid, id for profile_education table, and $grad_year. Time to UPDATE !
                XDB::execute('UPDATE  profile_education
                                 SET  flags = CONCAT(flags, \',completed\'), grad_year = {?}
                               WHERE  pid = {?} AND id = {?}',
                    $grad_year, $profile->id(), $res);
                XDB::execute('UPDATE  profile_display
                                 SET  promo = {?}
                               WHERE  pid = {?}',
                               'D' . $grad_year, $profile->id());
                $page->trigSuccess("Promotion de " . $profile->fullName() . " validée.");
            }

            $errors = $page->nb_errs();
            if ($errors == 0) {
                $page->trigSuccess("L'opération a été effectuée avec succès.");
            } else {
                $page->trigSuccess('L\'opération a été effectuée avec succès, sauf pour '
                    . (($errors == 1) ? 'l\'erreur signalée' : "les $errors erreurs signalées") . ' ci-dessus.');
            }
        }
        // case when we are on a graduation year and we have data to update, e.g. admin/phd/2007/validate
        elseif ($validate) {
            S::assert_xsrf_token();

            $list = XDB::iterator('SELECT  pe.pid, pd.directory_name
                                     FROM  profile_education AS pe
                               INNER JOIN  profile_display   AS pd ON (pe.pid = pd.pid)
                                    WHERE  FIND_IN_SET(\'primary\', pe.flags) AND NOT FIND_IN_SET(\'completed\', pe.flags)
                                           AND pe.degreeid = {?} AND pe.grad_year = {?}',
                                  $eduDegrees[Profile::DEGREE_D], $promo);
            while ($res = $list->next()) {
                $pid = $res['pid'];
                $name = $res['directory_name'];
                if (Post::b('completed_' . $pid)) {
                    $grad_year = Post::t('grad_year_' . $pid);
                    XDB::execute('UPDATE  profile_education
                                     SET  flags = CONCAT(flags, \',completed\'), grad_year = {?}
                                   WHERE  FIND_IN_SET(\'primary\', flags) AND pid = {?}',
                                 $grad_year, $pid);
                    XDB::execute('UPDATE  profile_display
                                     SET  promo = {?}
                                   WHERE  pid = {?}',
                                 'D' . $grad_year, $pid);
                    $page->trigSuccess("Promotion de $name validée.");
                }
            }
        }

        // case we are on a graduation year page, e.g. admin/phd/2007 or admin/phd/2007/validate
        $list = XDB::iterator('SELECT  pe.pid, pd.directory_name
                                 FROM  profile_education AS pe
                           INNER JOIN  profile_display   AS pd ON (pe.pid = pd.pid)
                                WHERE  FIND_IN_SET(\'primary\', pe.flags) AND NOT FIND_IN_SET(\'completed\', pe.flags)
                                       AND pe.degreeid = {?} AND pe.grad_year = {?}
                             ORDER BY  pd.directory_name',
                              $eduDegrees[Profile::DEGREE_D], $promo);
        $page->assign('list', $list);
        $page->assign('promo', $promo);
    }

    function handler_add_secondary_edu($page)
    {
        $page->changeTpl('admin/add_secondary_edu.tpl');

        if (!(Post::has('verify') || Post::has('add'))) {
            return;
        } elseif (!Post::has('people')) {
            $page->trigWarning("Aucune information n'a été fournie.");
            return;
        }

        require_once 'name.func.inc.php';
        $lines = explode("\n", Post::t('people'));
        $separator = Post::t('separator');
        $degree = Post::v('degree');
        $promotion = Post::i('promotion');
        $schoolsList = array_flip(DirEnum::getOptions(DirEnum::EDUSCHOOLS));
        $degreesList = array_flip(DirEnum::getOptions(DirEnum::EDUDEGREES));
        $edu_id = $schoolsList[Profile::EDU_X];
        $degree_id = $degreesList[$degree];

        $res = array(
            'incomplete' => array(),
            'empty'      => array(),
            'multiple'   => array(),
            'already'    => array(),
            'new'        => array()
        );
        $old_pids = array();
        $new_pids = array();
        foreach ($lines as $line) {
            $line = trim($line);
            $line_array = explode($separator, $line);
            array_walk($line_array, 'trim');
            if (count($line_array) != 3) {
                $page->trigError("La ligne « $line » est incomplète.");
                $res['incomplete'][] = $line;
                continue;
            }
            $cond = new PFC_And(new UFC_NameTokens(split_name_for_search($line_array[0]), array(), false, false, Profile::LASTNAME));
            $cond->addChild(new UFC_NameTokens(split_name_for_search($line_array[1]), array(), false, false, Profile::FIRSTNAME));
            $cond->addChild(new UFC_Promo('=', UserFilter::DISPLAY, $line_array[2]));
            $uf = new UserFilter($cond);
            $pid = $uf->getPIDs();
            $count = count($pid);
            if ($count == 0) {
                $page->trigError("La ligne « $line » ne correspond à aucun profil existant.");
                $res['empty'][] = $line;
                continue;
            } elseif ($count > 1) {
                $page->trigError("La ligne « $line » correspond à plusieurs profils existant.");
                $res['multiple'][] = $line;
                continue;
            } else {
                $count = XDB::fetchOneCell('SELECT  COUNT(*) AS count
                                              FROM  profile_education
                                             WHERE  pid = {?} AND eduid = {?} AND degreeid = {?}',
                                      $pid, $edu_id, $degree_id);
                if ($count == 1) {
                    $res['already'][] = $line;
                    $old_pids[] = $pid[0];
                } else {
                    $res['new'][] = $line;
                    $new_pids[] = $pid[0];
                }
            }
        }

        $display = array();
        foreach ($res as $type => $res_type) {
            if (count($res_type) > 0) {
                $display = array_merge($display, array('--------------------' . $type . ':'), $res_type);
            }
        }
        $page->assign('people', implode("\n", $display));
        $page->assign('promotion', $promotion);
        $page->assign('degree', $degree);

        if (Post::has('add')) {
            $entry_year = $promotion - Profile::educationDuration($degree);

            if (Post::b('force_addition')) {
                $pids = array_unique(array_merge($old_pids, $new_pids));
            } else {
                $pids = array_unique($new_pids);

                // Updates years.
                if (count($old_pids)) {
                    XDB::execute('UPDATE  profile_education
                                     SET  entry_year = {?}, grad_year = {?}, promo_year = {?}
                                   WHERE  pid IN {?} AND eduid = {?} AND degreeid = {?}',
                                 $entry_year, $promotion, $promotion, $old_pids, $edu_id, $degree_id);
                }
            }

            // Precomputes values common to all users.
            $select = XDB::format('MAX(id) + 1, pid, {?}, {?}, {?}, {?}, {?}, \'secondary\'',
                                  $edu_id, $degree_id, $entry_year, $promotion, $promotion );
            XDB::startTransaction();
            foreach ($pids as $pid) {
                XDB::execute('INSERT INTO  profile_education (id, pid, eduid, degreeid, entry_year, grad_year, promo_year, flags)
                                   SELECT  ' . $select . '
                                     FROM  profile_education
                                    WHERE  pid = {?}
                                 GROUP BY  pid',
                             $pid);
            }
            XDB::commit();
        }

    }

    function handler_admin_name($page, $hruid = null)
    {
        $page->changeTpl('admin/admin_name.tpl');

        if (Post::has('id')) {
            $user = User::get(Post::t('id'));
            if (is_null($user)) {
                $page->trigError("L'identifiant donné ne correspond à personne ou est ambigu.");
                exit();
            }
            pl_redirect('admin/name/' . $user->hruid);
        }

        $user = User::getSilent($hruid);
        if (!is_null($user)) {
            require_once 'name.func.inc.php';

            if ($user->hasProfile()) {
                $name_types = array(
                    'lastname_main'      => 'Nom patronymique',
                    'lastname_marital'   => 'Nom marital',
                    'lastname_ordinary'  => 'Nom usuel',
                    'firstname_main'     => 'Prénom',
                    'firstname_ordinary' => 'Prénom usuel',
                    'pseudonym'          => 'Pseudonyme'
                );
                $names = XDB::fetchOneAssoc('SELECT  lastname_main, lastname_marital, lastname_ordinary,
                                                     firstname_main, firstname_ordinary, pseudonym
                                               FROM  profile_public_names
                                              WHERE  pid = {?}',
                                            $user->profile()->id());
            } else {
                $name_types = array(
                    'lastname'  => 'Nom',
                    'firstname' => 'Prénom'
                );
                $names = XDB::fetchOneAssoc('SELECT  lastname, firstname
                                               FROM  accounts
                                              WHERE  uid = {?}',
                                            $user->id());
            }

            if (Post::has('correct')) {
                $new_names = array();
                $update = true;
                foreach ($name_types as $key => $fullname) {
                    $new_names[$key] = Post::t($key);
                    if (mb_strtolower($new_names[$key]) != mb_strtolower($names[$key])) {
                        $update = false;
                    }
                }

                if ($update) {
                    if ($user->hasProfile()) {
                        update_public_names($user->profile()->id(), $new_names);
                        update_display_names($user->profile(), $new_names);
                    } else {
                        $new_names['full_name'] = build_full_name($new_names['firstname'], $new_names['lastname']);
                        $new_names['directory_name'] = build_directory_name($new_names['firstname'], $new_names['lastname']);
                        $new_names['sort_name'] = build_sort_name($new_names['firstname'], $new_names['lastname']);
                        XDB::execute('UPDATE  accounts
                                         SET  lastname = {?}, firstname = {?}, full_name = {?},
                                              directory_name = {?}, sort_name = {?}
                                       WHERE  uid = {?}',
                                     $new_names['lastname'], $new_names['firstname'], $new_names['full_name'],
                                     $new_names['directory_name'], $new_names['sort_name'], $user->id());
                    }
                    $page->trigSuccess('Mise à jour réussie.');
                } else {
                    $page->trigError('Seuls des changements de casse sont autorisés ici.');
                }
            }

            if ($user->hasProfile()) {
                $names = XDB::fetchOneAssoc('SELECT  lastname_main, lastname_marital, lastname_ordinary,
                                                     firstname_main, firstname_ordinary, pseudonym
                                               FROM  profile_public_names
                                              WHERE  pid = {?}',
                                            $user->profile()->id());
            } else {
                $names = XDB::fetchOneAssoc('SELECT  lastname, firstname
                                               FROM  accounts
                                              WHERE  uid = {?}',
                                            $user->id());
            }

            foreach ($names as $key => $name) {
                $names[$key] = array(
                    'value'    => $name,
                    'standard' => capitalize_name($name)
                );
                $names[$key]['different'] = ($names[$key]['value'] != $names[$key]['standard']);
            }

            $page->assign('uid', $user->id());
            $page->assign('hruid', $user->hruid);
            $page->assign('names', $names);
            $page->assign('name_types', $name_types);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
