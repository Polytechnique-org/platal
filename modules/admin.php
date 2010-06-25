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

class AdminModule extends PLModule
{
    function handlers()
    {
        return array(
            'phpinfo'                      => $this->make_hook('phpinfo',                AUTH_MDP, 'admin'),
            'get_rights'                   => $this->make_hook('get_rights',             AUTH_MDP, 'admin'),
            'admin'                        => $this->make_hook('default',                AUTH_MDP, 'admin'),
            'admin/dead-but-active'        => $this->make_hook('dead_but_active',        AUTH_MDP, 'admin'),
            'admin/deaths'                 => $this->make_hook('deaths',                 AUTH_MDP, 'admin'),
            'admin/downtime'               => $this->make_hook('downtime',               AUTH_MDP, 'admin'),
            'admin/homonyms'               => $this->make_hook('homonyms',               AUTH_MDP, 'admin'),
            'admin/logger'                 => $this->make_hook('logger',                 AUTH_MDP, 'admin'),
            'admin/logger/actions'         => $this->make_hook('logger_actions',         AUTH_MDP, 'admin'),
            'admin/postfix/blacklist'      => $this->make_hook('postfix_blacklist',      AUTH_MDP, 'admin'),
            'admin/postfix/delayed'        => $this->make_hook('postfix_delayed',        AUTH_MDP, 'admin'),
            'admin/postfix/regexp_bounces' => $this->make_hook('postfix_regexpsbounces', AUTH_MDP, 'admin'),
            'admin/postfix/whitelist'      => $this->make_hook('postfix_whitelist',      AUTH_MDP, 'admin'),
            'admin/mx/broken'              => $this->make_hook('mx_broken',              AUTH_MDP, 'admin'),
            'admin/skins'                  => $this->make_hook('skins',                  AUTH_MDP, 'admin'),
            'admin/user'                   => $this->make_hook('user',                   AUTH_MDP, 'admin'),
            'admin/add_accounts'           => $this->make_hook('add_accounts',           AUTH_MDP, 'admin'),
            'admin/validate'               => $this->make_hook('validate',               AUTH_MDP, 'admin'),
            'admin/validate/answers'       => $this->make_hook('validate_answers',       AUTH_MDP, 'admin'),
            'admin/wiki'                   => $this->make_hook('wiki',                   AUTH_MDP, 'admin'),
            'admin/ipwatch'                => $this->make_hook('ipwatch',                AUTH_MDP, 'admin'),
            'admin/icons'                  => $this->make_hook('icons',                  AUTH_MDP, 'admin'),
            'admin/accounts'               => $this->make_hook('accounts',               AUTH_MDP, 'admin'),
            'admin/account/types'          => $this->make_hook('account_types',          AUTH_MDP, 'admin'),
            'admin/jobs'                   => $this->make_hook('jobs',                   AUTH_MDP, 'admin'),
        );
    }

    function handler_phpinfo(&$page)
    {
        phpinfo();
        exit;
    }

    function handler_get_rights(&$page, $level)
    {
        if (S::suid()) {
            $page->kill('Déjà en SUID');
        }
        $user =& S::user();
        Platal::session()->startSUID($user, $level);

        pl_redirect('/');
    }

    function handler_default(&$page)
    {
        $page->changeTpl('admin/index.tpl');
        $page->setTitle('Administration');
    }

    function handler_postfix_delayed(&$page)
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

    function handler_postfix_regexpsbounces(&$page, $new = null) {
        $page->changeTpl('admin/emails_bounces_re.tpl');
        $page->setTitle('Administration - Postfix : Regexps Bounces');
        $page->assign('new', $new);

        if (Post::has('submit')) {
            foreach (Env::v('lvl') as $id=>$val) {
                XDB::query(
                        "REPLACE INTO emails_bounces_re (id,pos,lvl,re,text) VALUES ({?}, {?}, {?}, {?}, {?})",
                        $id, $_POST['pos'][$id], $_POST['lvl'][$id], $_POST['re'][$id], $_POST['text'][$id]
                );
            }
        }

        $page->assign('bre', XDB::iterator("SELECT * FROM emails_bounces_re ORDER BY pos"));
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


    /** Make a where clause to get a user's sessions.
     * Prepare the where clause request that will retrieve the sessions.
     *
     * @param $year INTEGER Only get log entries made during the given year.
     * @param $month INTEGER Only get log entries made during the given month.
     * @param $day INTEGER Only get log entries made during the given day.
     * @param $uid INTEGER Only get log entries referring to the given user ID.
     *
     * @return STRING the WHERE clause of a query, including the 'WHERE' keyword
     * @private
     */
    function _makeWhere($year, $month, $day, $uid)
    {
        // start constructing the "where" clause
        $where = array();

        if ($uid)
            array_push($where, "s.uid='$uid'");

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
            $where[] = "start >= " . date("Ymd000000", $dmin);
            $where[] = "start < " . date("Ymd000000", $dmax);
        }

        if (!empty($where)) {
            return ' WHERE ' . implode($where, " AND ");
        } else {
            return '';
        }
        // WE know it's totally reversed, so better use array_reverse than a SORT BY start DESC
    }

    // }}}

    function handler_logger(&$page, $action = null, $arg = null) {
        if ($action == 'session') {

            // we are viewing a session
            $res = XDB::query("SELECT  ls.*, a.alias AS username, sa.alias AS suer
                                 FROM  log_sessions AS ls
                            LEFT JOIN  aliases   AS a  ON (a.uid = ls.uid AND a.type='a_vie')
                            LEFT JOIN  aliases   AS sa ON (sa.uid = ls.suid AND sa.type='a_vie')
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

            $res = XDB::query('SELECT uid FROM aliases WHERE alias={?}',
                              $loguser);
            $loguid  = $res->fetchOneCell();

            if ($loguid) {
                $year  = Env::i('year');
                $month = Env::i('month');
                $day   = Env::i('day');
            } else {
                $year  = Env::i('year', intval(date('Y')));
                $month = Env::i('month', intval(date('m')));
                $day   = Env::i('day', intval(date('d')));
            }

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

            $page->assign('loguser', $loguser);
            // smarty assignments

            if ($loguid || $year) {

                // get the requested sessions
                $where  = $this->_makeWhere($year, $month, $day, $loguid);
                $select = "SELECT  s.id, s.start, s.uid,
                                   a.alias as username
                             FROM  log_sessions AS s
                        LEFT JOIN  aliases   AS a  ON (a.uid = s.uid AND a.type='a_vie')
                    $where
                    ORDER BY start DESC";
                $res = XDB::iterator($select);

                $sessions = array();
                while ($mysess = $res->next()) {
                    $mysess['events'] = array();
                    $sessions[$mysess['id']] = $mysess;
                }
                array_reverse($sessions);

                // attach events
                $sql = "SELECT  s.id, a.text
                          FROM  log_sessions AS s
                    LEFT  JOIN  log_events   AS e ON(e.session=s.id)
                    INNER JOIN  log_actions  AS a ON(a.id=e.action)
                        $where";

                $res = XDB::iterator($sql);
                while ($event = $res->next()) {
                    array_push($sessions[$event['id']]['events'], $event['text']);
                }
                $page->assign_by_ref('sessions', $sessions);
            } else {
                $page->assign('msg_nofilters', "Sélectionner une annuée et/ou un utilisateur");
            }
        }

        $page->changeTpl('admin/logger-view.tpl');

        $page->setTitle('Administration - Logs des sessions');
    }

    function handler_user(&$page, $login = false)
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
            return;
        }

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

        // Account Form {{{
        $to_update = array();
        if (Post::has('disable_weak_access')) {
            $to_update['weak_password'] = null;
        } else if (Post::has('update_account')) {
            if (Post::s('full_name') != $user->fullName()) {
                // XXX: Update profile if a profile is associated
                $to_update['full_name'] = Post::s('full_name');
            }
            if (Post::s('display_name') != $user->displayName()) {
                // XXX: Update profile if a profile is associated
                $to_update['display_name'] = Post::s('display_name');
            }
            if (Post::s('sex') != ($user->isFemale() ? 'female' : 'male')) {
                $to_update['sex'] = Post::s('sex');
            }
            if (!Post::blank('hashpass')) {
                $to_update['password'] = Post::s('hashpass');
                // TODO: Propagate the password update to GoogleApps, when required. Eg:
                // $account = new GoogleAppsAccount($user);
                // if ($account->active() && $account->sync_password) {
                //     $account->set_password($pass_encrypted);
                // }
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
        }
        if (!empty($to_update)) {
            // TODO: fetch the initial values of the fields, and eventually send
            // a summary of the changes to an admin.
            $set = array();
            foreach ($to_update as $k => $value) {
                $set[] = XDB::format($k . ' = {?}', $value);
            }
            XDB::execute('UPDATE  accounts
                             SET  ' . implode(', ', $set) . ' 
                           WHERE  uid = ' . XDB::format('{?}', $user->id()));
            $page->trigSuccess('Données du compte mise à jour avec succès');
            $user = User::getWithUID($user->id());
        }
        // }}}

        // Profile form {{{
        if (Post::has('add_profile') || Post::has('del_profile') || Post::has('owner')) {
            if (Post::i('del_profile', 0) != 0) {
                XDB::execute('DELETE FROM  account_profiles
                                    WHERE  uid = {?} AND pid = {?}',
                             $user->id(), Post::i('del_profile'));
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
        require_once("emails.inc.php");
        $redirect = ($registered ? new Redirect($user) : null);
        if (Post::has('add_fwd')) {
            $email = Post::t('email');
            if (!isvalid_email_redirection($email)) {
                $page->trigError("Email non valide: $email");
            } else {
                $redirect->add_email($email);
                $page->trigSuccess("Ajout de $email effectué");
            }
        } else if (!Post::blank('del_fwd')) {
            $redirect->delete_email(Post::t('del_fwd'));
        } else if (!Post::blank('activate_fwd')) {
            $redirect->modify_one_email(Post::t('activate_fwd', true));
        } else if (!Post::blank('deactivate_fwd')) {
            $redirect->modify_one_email(Post::t('deactivate_fwd', false));
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
                $domain = $globals->mail->domain;
            }

            // Checks for alias' user validity.
            if (!preg_match('/[-a-z0-9\.]+/s', $alias)) {
                $page->trigError("'$alias' n'est pas un alias valide");
            }

            // Eventually adds the alias to the right domain.
            if ($domain == $globals->mail->alias_dom || $domain == $globals->mail->alias_dom2) {
                $req = new AliasReq($user, $alias, 'Admin request', false);
                if ($req->commit()) {
                    $page->trigSuccess("Nouvel alias '$alias@$domain' attribué");
                } else {
                    $page->trigError("Impossible d'ajouter l'alias '$alias@$domain', il est probablement déjà attribué");
                }
            } elseif ($domain == $globals->mail->domain || $domain == $globals->mail->domain2) {
                $res = XDB::execute("INSERT INTO  aliases (uid, alias, type)
                                          VALUES  ({?}, {?}, 'alias')",
                                    $user->id(), $alias);
                $page->trigSuccess("Nouvel alias '$alias' ajouté");
            } else {
                $page->trigError("Le domaine '$domain' n'est pas valide");
            }
        } else if (!Post::blank('del_alias')) {
            XDB::execute("DELETE FROM  aliases
                                WHERE  uid = {?} AND alias = {?} AND
                                       type NOT IN ('a_vie', 'homonyme')",
                         $user->id(), $val);
            XDB::execute("UPDATE  emails
                             SET  rewrite = ''
                           WHERE  uid = {?} AND rewrite LIKE CONCAT({?}, '@%')",
                         $user->id(), $val);
            fix_bestalias($user);
            $page->trigSuccess("L'alias '$val' a été supprimé");
        } else if (!Post::blank('best')) {
            XDB::execute("UPDATE  aliases
                             SET  flags = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', flags, ','), ',bestalias,', ','))
                           WHERE  uid = {?}", $user->id());
            XDB::execute("UPDATE  aliases
                             SET  flags = CONCAT_WS(',', IF(flags = '', NULL, flags), 'bestalias')
                           WHERE  uid = {?} AND alias = {?}", $user->id(), $val);
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


        $page->addJsLink('jquery.ui.core.js');
        $page->addJsLink('jquery.ui.tabs.js');

        // Displays last login and last host information.
        $res = XDB::query("SELECT  start, host
                             FROM  log_sessions
                            WHERE  uid = {?} AND suid = 0
                         ORDER BY  start DESC
                            LIMIT  1", $user->id());
        list($lastlogin,$host) = $res->fetchOneRow();
        $page->assign('lastlogin', $lastlogin);
        $page->assign('host', $host);

        // Display active aliases.
        $page->assign('virtuals', $user->emailAliases());
        $page->assign('aliases', XDB::iterator("SELECT  alias, type='a_vie' AS for_life,
                                                        FIND_IN_SET('bestalias',flags) AS best, expire
                                                  FROM  aliases
                                                 WHERE  uid = {?} AND type != 'homonyme'
                                              ORDER BY  type != 'a_vie'", $user->id()));
        $page->assign('account_types', XDB::iterator('SELECT * FROM account_types ORDER BY type'));
        $page->assign('skins', XDB::iterator('SELECT id, name FROM skins ORDER BY name'));
        $page->assign('profiles', XDB::iterator('SELECT  p.pid, p.hrpid, FIND_IN_SET(\'owner\', ap.perms) AS owner
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

    private static function formatNewUser(&$page, $infosLine, $separator, $promo, $size)
    {
        $infos = explode($separator, $infosLine);
        if (sizeof($infos) > $size || sizeof($infos) < 2) {
            $page->trigError("La ligne $infosLine n'a pas été ajoutée.");
            return false;
        }

        array_map('trim', $infos);
        $hrid = self::getHrid($infos[1], $infos[0], $promo);
        $res1 = XDB::query('SELECT  COUNT(*)
                              FROM  accounts
                             WHERE  hruid = {?}', $hrid);
        $res2 = XDB::query('SELECT  COUNT(*)
                              FROM  profiles
                             WHERE  hrpid = {?}', $hrid);
        if (is_null($hrid) || $res1->fetchOneCell() > 0 || $res2->fetchOneCell() > 0) {
            $page->trigError("La ligne $infosLine n'a pas été ajoutée.");
            return false;
        }
        $infos['hrid'] = $hrid;
        return $infos;
    }

    private static function formatSex(&$page, $sex, $line)
    {
        switch ($sex) {
          case 'F':
            return PlUser::GENDER_FEMALE;
          case 'M':
            return PlUser::GENDER_MALE;
          default:
            $page->trigError("La ligne $line n'a pas été ajoutée car le sexe $sex n'est pas pris en compte.");
            return null;
        }
    }

    private static function formatBirthDate($birthDate)
    {
        return date("Y-m-d", strtotime($birthDate));
    }

    function handler_add_accounts(&$page, $action = null, $promo = null)
    {
        $page->changeTpl('admin/add_accounts.tpl');

        if (Env::has('add_type') && Env::has('people')) {
            $lines = explode("\n", Env::t('people'));
            $separator = Env::t('separator');
            $promotion = Env::i('promotion');
            $nameTypes = DirEnum::getOptions(DirEnum::NAMETYPES);
            $nameTypes = array_flip($nameTypes);

            if (Env::t('add_type') == 'promo') {
                $type = 'x';
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
                    break;
                  case 'M':
                    $degreeid = $eduDegrees[Profile::DEGREE_M];
                    $grad_year = $promotion;
                    $entry_year = $promotion - 2;
                    $promo = 'M' . $promotion;
                    break;
                  case 'D':
                    $degreeid = $eduDegrees[Profile::DEGREE_D];
                    $grad_year = $promotion;
                    $entry_year = $promotion - 3;
                    $promo = 'D' . $promotion;
                    break;
                  default:
                    $page->killError("La formation n'est pas reconnue:" . Env::t('edu_type') . '.');
                }

                foreach ($lines as $line) {
                    if ($infos = self::formatNewUser($page, $line, $separator, $promotion, 6)) {
                        $sex = self::formatSex($page, $infos[3], $line);
                        if (!is_null($sex)) {
                            $name = $infos[1] . ' ' . $infos[0];
                            $birthDate = self::formatBirthDate($infos[2]);
                            $xorgId = Profile::getXorgId($infos[4]);
                            if (is_null($xorgId)) {
                                $page->trigError("La ligne $line n'a pas été ajoutée car le matricule École est mal renseigné.");
                                continue;
                            }

                            XDB::execute('INSERT INTO  profiles (hrpid, xorg_id, ax_id, birthdate_ref, sex)
                                               VALUES  ({?}, {?}, {?}, {?}, {?})',
                                         $infos['hrid'], $xorgId, $infos[5], $birthDate, $sex);
                            $pid = XDB::insertId();
                            XDB::execute('INSERT INTO  profile_name (pid, name, typeid)
                                               VALUES  ({?}, {?}, {?})',
                                         $pid, $infos[0], $nameTypes['name_ini']);
                            XDB::execute('INSERT INTO  profile_name (pid, name, typeid)
                                               VALUES  ({?}, {?}, {?})',
                                         $pid, $infos[1], $nameTypes['firstname_ini']);
                            XDB::execute('INSERT INTO  profile_display (pid, yourself, public_name, private_name,
                                                                        directory_name, short_name, sort_name, promo)
                                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                                         $pid, $infos[1], $name, $name, $name, $name, $infos[0] . ' ' . $infos[1], $promo);
                            XDB::execute('INSERT INTO  profile_education (pid, eduid, degreeid, entry_year, grad_year, flags)
                                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?})',
                                         $pid, $eduSchools[Profile::EDU_X], $degreeid, $entry_year, $grad_year, 'primary');
                            XDB::execute('INSERT INTO  accounts (hruid, type, is_admin, state, full_name, display_name, sex)
                                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?})',
                                         $infos['hrid'], $type, 0, 'active', $name, $infos[1], $sex);
                            $uid = XDB::insertId();
                            XDB::execute('INSERT INTO  account_profiles (uid, pid, perms)
                                               VALUES  ({?}, {?}, {?})',
                                         $uid, $pid, 'owner');
                        }
                    }
                }
            } else if (Env::t('add_type') == 'account') {
                $type = Env::t('type');
                $newAccounts = array();
                foreach ($lines as $line) {
                    if ($infos = self::formatNewUser($page, $line, $separator, $type, 4)) {
                        $sex = self::formatSex($page, $infos[3], $line);
                        if (!is_null($sex)) {
                            XDB::execute('INSERT INTO  accounts (hruid, type, is_admin, state, email, full_name, display_name, sex)
                                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                                         $infos['hrid'], $type, 0, 'active', $infos[2], $infos[1] . ' ' . $infos[0], $infos[1], $sex);
                            $newAccounts[$infos['hrid']] = $infos[1] . ' ' . $infos[0];
                        }
                    }
                }
                if (!empty($newAccounts)) {
                    $page->assign('newAccounts', $newAccounts);
                }
            } else if (Env::t('add_type') == 'ax_id') {
                $type = 'x';
                foreach ($lines as $line) {
                    if ($infos = self::formatNewUser($page, $line, $separator, $promotion, 3)) {
                        XDB::execute('UPDATE  profiles
                                         SET  ax_id = {?}
                                       WHERE  hrpid = {?}',
                                     $infos[2], $infos['hrid']);
                    }
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

    function handler_homonyms(&$page, $op = 'list', $target = null)
    {
        $page->changeTpl('admin/homonymes.tpl');
        $page->setTitle('Administration - Homonymes');
        $this->load("homonyms.inc.php");

        if ($target) {
            $user = User::getSilent($target);
            if (!$user || !($loginbis = select_if_homonyme($user))) {
                $target = 0;
            } else {
                $page->assign('user', $user);
                $page->assign('loginbis',$loginbis);
            }
        }

        $page->assign('op', $op);
        $page->assign('target', $target);

        // on a un $target valide, on prepare les mails
        if ($target) {
            // on examine l'op a effectuer
            switch ($op) {
                case 'mail':
                    S::assert_xsrf_token();

                    send_warning_homonyme($user, $loginbis);
                    switch_bestalias($user, $loginbis);
                    $op = 'list';
                    $page->trigSuccess('Email envoyé à ' . $user->forlifeEmail() . '.');
                    break;

                case 'correct':
                    S::assert_xsrf_token();

                    switch_bestalias($user, $loginbis);
                    XDB::execute("UPDATE  aliases
                                     SET  type = 'homonyme', expire=NOW()
                                   WHERE  alias = {?}", $loginbis);
                    XDB::execute("REPLACE INTO  homonyms (homonyme_id, uid)
                                        VALUES  ({?}, {?})", $target, $target);
                    send_robot_homonyme($user, $loginbis);
                    $op = 'list';
                    $page->trigSuccess('Email envoyé à ' . $user->forlifeEmail() . ', alias supprimé.');
                    break;
            }
        }

        if ($op == 'list') {
            $res = XDB::iterator(
                    "SELECT  a.alias AS homonyme, s.alias AS forlife,
                             IF(h.homonyme_id = s.uid, a.expire, NULL) AS expire,
                             IF(h.homonyme_id = s.uid, a.type, NULL) AS type, ac.uid
                       FROM  aliases       AS a
                  LEFT JOIN  homonyms      AS h  ON (h.homonyme_id = a.uid)
                 INNER JOIN  aliases       AS s  ON (s.uid = h.uid AND s.type = 'a_vie')
                 INNER JOIN  accounts      AS ac ON (ac.uid = a.uid)
                      WHERE  a.type = 'homonyme' OR a.expire != ''
                   ORDER BY  a.alias, forlife");
            $hnymes = Array();
            while ($tab = $res->next()) {
                $hnymes[$tab['homonyme']][] = $tab;
            }
            $page->assign_by_ref('hnymes', $hnymes);
        }
    }

    function handler_deaths(&$page, $promo = 0, $validate = false)
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
                if($val == $death || empty($val)) {
                    continue;
                }

                XDB::execute('UPDATE  profiles
                                 SET  deathdate = {?}, deathdate_rec = NOW()
                               WHERE  pid = {?}', $val, $pid);
                $page->trigSuccess('Ajout du décès de ' . $name . ' le ' . $val . '.');
                if($death == '0000-00-00' || empty($death)) {
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
        $page->assign('decedes', $res);
    }

    function handler_dead_but_active(&$page)
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

    function handler_validate(&$page, $action = 'list', $id = null)
    {
        $page->changeTpl('admin/validation.tpl');
        $page->setTitle('Administration - Valider une demande');
                $page->addCssLink('nl.css');
        $page->addJsLink('ajax.js');
        require_once("validations.inc.php");


        if ($action == 'edit' and !is_null($id)) {
            $page->assign('preview_id', $id);
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
        $page->assign('categories', $categories = explode(',', str_replace("'", '', substr($a['Type'], 5, -1))));

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
            XDB::query('REPLACE INTO  requests_hidden (uid, hidden_requests)
                              VALUES  ({?}, {?})',
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
        $page->assign('vit', new ValidateIterator());
    }

    function handler_validate_answers(&$page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Réponses automatiques de validation');
        $page->assign('title', 'Gestion des réponses automatiques');
        $table_editor = new PLTableEditor('admin/validate/answers','requests_answers','id');
        $table_editor->describe('category','catégorie',true);
        $table_editor->describe('title','titre',true);
        $table_editor->describe('answer','texte',false);
        $table_editor->apply($page, $action, $id);
    }

    function handler_skins(&$page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Skins');
        $page->assign('title', 'Gestion des skins');
        $table_editor = new PLTableEditor('admin/skins','skins','id');
        $table_editor->describe('name','nom',true);
        $table_editor->describe('skin_tpl','nom du template',true);
        $table_editor->describe('auteur','auteur',false);
        $table_editor->describe('comment','commentaire',true);
        $table_editor->describe('date','date',false);
        $table_editor->describe('ext','extension du screenshot',false);
        $table_editor->apply($page, $action, $id);
    }

    function handler_postfix_blacklist(&$page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Postfix : Blacklist');
        $page->assign('title', 'Blacklist de postfix');
        $table_editor = new PLTableEditor('admin/postfix/blacklist','postfix_blacklist','email', true);
        $table_editor->describe('reject_text','Texte de rejet',true);
        $table_editor->describe('email','email',true);
        $table_editor->apply($page, $action, $id);
    }

    function handler_postfix_whitelist(&$page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Postfix : Whitelist');
        $page->assign('title', 'Whitelist de postfix');
        $table_editor = new PLTableEditor('admin/postfix/whitelist','postfix_whitelist','email', true);
        $table_editor->describe('email','email',true);
        $table_editor->apply($page, $action, $id);
    }

    function handler_mx_broken(&$page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - MX Défaillants');
        $page->assign('title', 'MX Défaillant');
        $table_editor = new PLTableEditor('admin/mx/broken', 'mx_watch', 'host', true);
        $table_editor->describe('host', 'Masque', true);
        $table_editor->describe('state', 'Niveau', true);
        $table_editor->describe('text', 'Description du problème', false);
        $table_editor->apply($page, $action, $id);
    }

    function handler_logger_actions(&$page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Actions');
        $page->assign('title', 'Gestion des actions de logger');
        $table_editor = new PLTableEditor('admin/logger/actions','log_actions','id');
        $table_editor->describe('text','intitulé',true);
        $table_editor->describe('description','description',true);
        $table_editor->apply($page, $action, $id);
    }

    function handler_downtime(&$page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Coupures');
        $page->assign('title', 'Gestion des coupures');
        $table_editor = new PLTableEditor('admin/downtime','downtimes','id');
        $table_editor->describe('debut','date',true);
        $table_editor->describe('duree','durée',false);
        $table_editor->describe('resume','résumé',true);
        $table_editor->describe('services','services affectés',true);
        $table_editor->describe('description','description',false);
        $table_editor->apply($page, $action, $id);
    }

    function handler_account_types(&$page, $action = 'list', $id = null) 
    {
        $page->setTitle('Administration - Types de comptes');
        $page->assign('title', 'Gestion des types de comptes');
        $table_editor = new PLTableEditor('admin/account/types', 'account_types', 'type', true);
        $table_editor->describe('type', 'Catégorie', true);
        $table_editor->describe('perms', 'Permissions associées', true);
        $table_editor->apply($page, $action, $id);
    }

    function handler_wiki(&$page, $action = 'list', $wikipage = null, $wikipage2 = null)
    {
        if (S::hasAuthToken()) {
           $page->setRssLink('Changement Récents',
                             '/Site/AllRecentChanges?action=rss&user=' . S::v('hruid') . '&hash=' . S::v('token'));
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

    function handler_ipwatch(&$page, $action = 'list', $ip = null)
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

    function handler_icons(&$page)
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

    function handler_accounts(&$page)
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

    function handler_jobs(&$page, $id = -1)
    {
        $page->changeTpl('admin/jobs.tpl');

        if (Env::has('search')) {
            $res = XDB::query("SELECT  e.id, e.name, e.acronym
                                 FROM  profile_job_enum AS e
                                WHERE  e.name LIKE CONCAT('% ', {?}, '%') OR e.acronym LIKE CONCAT('% ', {?}, '%')",
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
            // TODO: use address and phone classes to update profile_job_enum and profile_phones once they are done.

            S::assert_xsrf_token();
            $selectedJob = Env::has('selectedJob');

            XDB::execute("DELETE FROM  profile_phones
                                WHERE  pid = {?} AND link_type = 'hq'",
                         $id);
            XDB::execute("DELETE FROM  profile_addresses
                                WHERE  jobid = {?} AND type = 'hq'",
                         $id);
            XDB::execute('DELETE FROM  profile_job_enum
                                WHERE  id = {?}',
                         $id);

            if (Env::has('change')) {
                XDB::execute('UPDATE  profile_job
                                 SET  jobid = {?}
                               WHERE  jobid = {?}',
                             Env::i('newJobId'), $id);

                $page->trigSuccess("L'entreprise a bien été remplacée.");
            } else {
                require_once 'profil.func.inc.php';
                require_once 'geocoding.inc.php';

                $display_tel = format_display_number(Env::v('tel'), $error_tel);
                $display_fax = format_display_number(Env::v('fax'), $error_fax);
                $gmapsGeocoder = new GMapsGeocoder();
                $address = array('text' => Env::t('address'));
                $address = $gmapsGeocoder->getGeocodedAddress($address);
                Geocoder::getAreaId($address, 'administrativeArea');
                Geocoder::getAreaId($address, 'subAdministrativeArea');
                Geocoder::getAreaId($address, 'locality');

                XDB::execute('UPDATE  profile_job_enum
                                 SET  name = {?}, acronym = {?}, url = {?}, email = {?},
                                      NAF_code = {?}, AX_code = {?}, holdingid = {?}
                               WHERE  id = {?}',
                             Env::t('name'), Env::t('acronym'), Env::t('url'), Env::t('email'),
                             Env::t('NAF_code'), Env::i('AX_code'), Env::i('holdingId'), $id);

                XDB::execute("INSERT INTO  profile_phones (pid, link_type, link_id, tel_id, tel_type,
                                           search_tel, display_tel, pub)
                                   VALUES  ({?}, 'hq', 0, 0, 'fixed', {?}, {?}, 'public'),
                                           ({?}, 'hq', 0, 1, 'fax', {?}, {?}, 'public')",
                             $id, format_phone_number(Env::v('tel')), $display_tel,
                             $id, format_phone_number(Env::v('fax')), $display_fax);

                XDB::execute("INSERT INTO  profile_addresses (jobid, type, id, accuracy,
                                                              text, postalText, postalCode, localityId,
                                                              subAdministrativeAreaId, administrativeAreaId,
                                                              countryId, latitude, longitude, updateTime,
                                                              north, south, east, west)
                                   VALUES  ({?}, 'hq', 0, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?},
                                            {?}, {?}, FROM_UNIXTIME({?}), {?}, {?}, {?}, {?})",
                             $id, $address['accuracy'], $address['text'], $address['postalText'],
                             $address['postalCode'], $address['localityId'],
                             $address['subAdministrativeAreaId'], $address['administrativeAreaId'],
                             $address['countryId'], $address['latitude'], $address['longitude'],
                             $address['updateTime'], $address['north'], $address['south'],
                             $address['east'], $address['west']);

                $page->trigSuccess("L'entreprise a bien été mise à jour.");
            }
        }

        if (!Env::has('change') && $id != -1) {
            $res = XDB::query("SELECT  e.id, e.name, e.acronym, e.url, e.email, e.NAF_code, e.AX_code,
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
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
