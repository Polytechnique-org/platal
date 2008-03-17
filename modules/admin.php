<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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
            'phpinfo'                      => $this->make_hook('phpinfo', AUTH_MDP, 'admin'),
            'admin'                        => $this->make_hook('default', AUTH_MDP, 'admin'),
            'admin/ax-xorg'                => $this->make_hook('ax_xorg', AUTH_MDP, 'admin'),
            'admin/dead-but-active'        => $this->make_hook('dead_but_active', AUTH_MDP, 'admin'),
            'admin/deaths'                 => $this->make_hook('deaths', AUTH_MDP, 'admin'),
            'admin/downtime'               => $this->make_hook('downtime', AUTH_MDP, 'admin'),
            'admin/homonyms'               => $this->make_hook('homonyms', AUTH_MDP, 'admin'),
            'admin/logger'                 => $this->make_hook('logger', AUTH_MDP, 'admin'),
            'admin/logger/actions'         => $this->make_hook('logger_actions', AUTH_MDP, 'admin'),
            'admin/postfix/blacklist'      => $this->make_hook('postfix_blacklist', AUTH_MDP, 'admin'),
            'admin/postfix/delayed'        => $this->make_hook('postfix_delayed', AUTH_MDP, 'admin'),
            'admin/postfix/regexp_bounces' => $this->make_hook('postfix_regexpsbounces', AUTH_MDP, 'admin'),
            'admin/postfix/whitelist'      => $this->make_hook('postfix_whitelist', AUTH_MDP, 'admin'),
            'admin/mx/broken'              => $this->make_hook('mx_broken', AUTH_MDP, 'admin'),
            'admin/skins'                  => $this->make_hook('skins', AUTH_MDP, 'admin'),
            'admin/synchro_ax'             => $this->make_hook('synchro_ax', AUTH_MDP, 'admin'),
            'admin/user'                   => $this->make_hook('user', AUTH_MDP, 'admin'),
            'admin/promo'                  => $this->make_hook('promo', AUTH_MDP, 'admin'),
            'admin/validate'               => $this->make_hook('validate', AUTH_MDP, 'admin'),
            'admin/validate/answers'       => $this->make_hook('validate_answers', AUTH_MDP, 'admin'),
            'admin/wiki'                   => $this->make_hook('wiki', AUTH_MDP, 'admin'),
            'admin/ipwatch'                => $this->make_hook('ipwatch', AUTH_MDP, 'admin'),
            'admin/icons'                  => $this->make_hook('icons', AUTH_MDP, 'admin'),
        );
    }

    function handler_phpinfo(&$page)
    {
        phpinfo();
        exit;
    }

    function handler_default(&$page)
    {
        $page->changeTpl('admin/index.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration');
    }

    function handler_postfix_delayed(&$page)
    {
        $page->changeTpl('admin/postfix_delayed.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Postfix : Retardés');

        if (Env::has('del')) {
            $crc = Env::v('crc');
            XDB::execute("UPDATE postfix_mailseen SET release = 'del' WHERE crc = {?}", $crc);
            $page->trig($crc." verra tous ses mails supprimés !");
        } elseif (Env::has('ok')) {
            $crc = Env::v('crc');
            XDB::execute("UPDATE postfix_mailseen SET release = 'ok' WHERE crc = {?}", $crc);
            $page->trig($crc." a le droit de passer !");
        }

        $sql = XDB::iterator(
                "SELECT  crc, nb, update_time, create_time,
                         FIND_IN_SET('del', release) AS del,
                         FIND_IN_SET('ok', release) AS ok
                   FROM  postfix_mailseen
                  WHERE  nb >= 30
               ORDER BY  release != ''");

        $page->assign_by_ref('mails', $sql);
    }

    function handler_postfix_regexpsbounces(&$page, $new = null) {
        $page->changeTpl('admin/emails_bounces_re.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Postfix : Regexps Bounces');
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
        $months[0] = "----";

        if ($year && $month) {
            $day_max = Array(-1, 31, checkdate(2, 29, $year) ? 29 : 28 , 31,
                             30, 31, 30, 31, 31, 30, 31, 30, 31);
            $res = XDB::query("SELECT YEAR (MAX(start)), YEAR (MIN(start)),
                                      MONTH(MAX(start)), MONTH(MIN(start)),
                                      DAYOFMONTH(MAX(start)),
                                      DAYOFMONTH(MIN(start))
                                 FROM logger.sessions");
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
        $months[0] = "----";

        if ($year) {
            $res = XDB::query("SELECT YEAR (MAX(start)), YEAR (MIN(start)),
                                      MONTH(MAX(start)), MONTH(MIN(start))
                                 FROM logger.sessions");
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
        $years[0] = "----";

        // retrieve available years
        $res = XDB::query("select YEAR(MAX(start)), YEAR(MIN(start)) FROM logger.sessions");
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
            array_push($where, "uid='$uid'");

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
                                 FROM  logger.sessions AS ls
                            LEFT JOIN  aliases         AS a  ON (a.id = ls.uid AND a.type='a_vie')
                            LEFT JOIN  aliases         AS sa ON (sa.id = ls.suid AND sa.type='a_vie')
                                WHERE  ls.id = {?}", $arg);

            $page->assign('session', $a = $res->fetchOneAssoc());

            $res = XDB::iterator('SELECT  a.text, e.data, e.stamp
                                    FROM  logger.events  AS e
                               LEFT JOIN  logger.actions AS a ON e.action=a.id
                                   WHERE  e.session={?}', $arg);
            while ($myarr = $res->next()) {
               $page->append('events', $myarr);
            }

        } else {
            $loguser = $action == 'user' ? $arg : Env::v('loguser');

            $res = XDB::query('SELECT id FROM aliases WHERE alias={?}',
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
                             FROM  logger.sessions AS s
                        LEFT JOIN  aliases         AS a  ON (a.id = s.uid AND a.type='a_vie')
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
                          FROM  logger.sessions AS s
                    LEFT  JOIN  logger.events   AS e ON(e.session=s.id)
                    INNER JOIN  logger.actions  AS a ON(a.id=e.action)
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

        $page->assign('xorg_title','Polytechnique.org - Administration - Logs des sessions');
    }

    function handler_user(&$page, $login = false)
    {
        global $globals;
        $page->changeTpl('admin/utilisateurs.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Edit/Su/Log');
        require_once("emails.inc.php");
        require_once("user.func.inc.php");

        if (S::has('suid')) {
            $page->kill("Déjà en SUID !!!");
        }

        if (Env::has('user_id')) {
            $login = get_user_forlife(Env::i('user_id'));
            if (empty($login)) {
                $login = Env::i('user_id');
            }
        } elseif (Env::has('login')) {
            $login = get_user_forlife(Env::v('login'));
        }

        if(Env::has('logs_button') && $login) {
            pl_redirect("admin/logger?loguser=$login&year=".date('Y')."&month=".date('m'));
        }

        if (Env::has('ax_button') && $login) {
            pl_redirect("admin/synchro_ax/$login");
        }

        if(Env::has('suid_button') && $login) {
            $_SESSION['log']->log("suid_start", "login by ".S::v('forlife'));
            $_SESSION['suid'] = $_SESSION;
            $r = XDB::query("SELECT id FROM aliases WHERE alias={?}", $login);
            if($uid = $r->fetchOneCell()) {
                start_connexion($uid, true);
                pl_redirect("");
            }
        }

        if ($login) {
            if (is_numeric($login)) {
                $r = XDB::query("SELECT *, a.alias AS forlife,
                                        FIND_IN_SET('watch', u.flags) AS watch, FIND_IN_SET('femme', u.flags) AS sexe,
                                        (year(naissance) > promo - 15 or year(naissance) < promo - 25) AS naiss_err
                                   FROM auth_user_md5 AS u
                              LEFT JOIN aliases       AS a ON (a.id = u.user_id AND type= 'a_vie')
                                  WHERE u.user_id = {?}", $login);
            } else {
                $r  = XDB::query("SELECT  *, a.alias AS forlife,
                                          FIND_IN_SET('watch', u.flags) AS watch, FIND_IN_SET('femme', u.flags) AS sexe,
                                          (year(naissance) > promo - 15 or year(naissance) < promo - 25) AS naiss_err
                                    FROM  auth_user_md5 AS u
                              INNER JOIN  aliases       AS a ON ( a.id = u.user_id AND a.alias={?} AND type!='homonyme' )", $login);
            }
            $mr = $r->fetchOneAssoc();

            if (!is_numeric($login)) { //user has a forlife
                $redirect = new Redirect($mr['user_id']);
            }

            // Check if there was a submission
            foreach($_POST as $key => $val) {
                switch ($key) {
                    case "add_fwd":
                        $email = trim(Env::v('email'));
                        if (!isvalid_email_redirection($email)) {
                            $page->trig("invalid email $email");
                        } else {
                            $redirect->add_email($email);
                            $page->trig("Ajout de $email effectué");
                        }
                        break;

                    case "del_fwd":
                        if (!empty($val)) {
                            $redirect->delete_email($val);
                        }
                        break;

                    case "del_alias":
                        if (!empty($val)) {
                            XDB::execute("DELETE FROM aliases
                                                WHERE id={?} AND alias={?}
                                                      AND type!='a_vie' AND type!='homonyme'", $mr['user_id'], $val);
                            XDB::execute("UPDATE emails
                                             SET rewrite = ''
                                           WHERE uid = {?} AND rewrite LIKE CONCAT({?}, '@%')",
                                         $mr['user_id'], $val);
                            fix_bestalias($mr['user_id']);
                            $page->trig($val." a été supprimé");
                        }
                        break;
                    case "activate_fwd":
                        if (!empty($val)) {
                            $redirect->modify_one_email($val, true);
                        }
                        break;
                    case "deactivate_fwd":
                        if (!empty($val)) {
                            $redirect->modify_one_email($val, false);
                        }
                        break;
                    case "disable_fwd":
                        $redirect->disable();
                        break;
                    case "enable_fwd":
                        $redirect->enable();
                        break;
                    case "clean_fwd":
                        if (!empty($val)) {
                            $redirect->cleanErrors($val);
                        }
                        break;
                    case "add_alias":
                        global $globals;
                        $alias = trim(Env::v('email'));
                        if (strpos($alias, '@') !== false) {
                            list($alias, $domain) = explode('@', $alias);
                        } else {
                            $domain = $globals->mail->domain;
                        }
                        if (!preg_match('/[-a-z0-9\.]+/s', $alias)) {
                            $page->trig("'$alias' n'est pas un alias valide");
                        }
                        if ($domain == $globals->mail->alias_dom || $domain == $globals->mail->alias_dom2) {
                            $req = new AliasReq($mr['user_id'], $alias, 'Admin request', false);
                            if ($req->commit()) {
                                $page->trig("Nouvel alias '$alias@$domain' attribué");
                            } else {
                                $page->trig("Impossible d'ajouter l'alias '$alias@$domain', il est probablement déjà attribué");
                            }
                        } elseif ($domain == $globals->mail->domain || $domain == $globals->mail->domain2) {
                            if (XDB::execute("INSERT INTO  aliases (id,alias,type) VALUES  ({?}, {?}, 'alias')",
                                    $mr['user_id'], $alias)) {
                                $page->trig("Nouvel alias '$alias' ajouté");
                            } else {
                                $page->trig("Impossible d'ajouter l'alias '$alias', il est probablement déjà attribué");
                            }
                        } else {
                            $page->trig("Le domaine '$domain' n'est pas valide");
                        }
                        break;

                    case "best":
                        // 'bestalias' is the first bit of the set : 1
                        // 255 is the max for flags (8 sets max)
                        XDB::execute("UPDATE  aliases SET flags= flags & (255 - 1) WHERE id={?}", $mr['user_id']);
                        XDB::execute("UPDATE  aliases
                                                   SET  flags= flags | 1
                                                WHERE  id={?} AND alias={?}", $mr['user_id'], $val);
                        break;


                    // Editer un profil
                    case "u_edit":
                        require_once('secure_hash.inc.php');
                        $pass_encrypted = Env::v('newpass_clair') != "********" ? hash_encrypt(Env::v('newpass_clair')) : Env::v('passw');
                        $naiss = Env::v('naissanceN');
                        $deces = Env::v('decesN');
                        $perms = Env::v('permsN');
                        $prenm = Env::v('prenomN');
                        $nom   = Env::v('nomN');
                        $promo = Env::i('promoN');
                        $sexe  = Env::v('sexeN');
                        $comm  = trim(Env::v('commentN'));
                        $watch = Env::v('watchN');
                        $flags = '';
                        if ($sexe) {
                            $flags = 'femme';
                        }
                        if ($watch) {
                            if ($flags) {
                                $flags .= ',';
                            }
                            $flags .= 'watch';
                        }

                        if ($watch && !$comm) {
                            $page->trig("Il est nécessaire de mettre un commentaire pour surveiller un compte");
                            break;
                        }

                        $watch = 'SELECT naissance, deces, password, perms,
                                         prenom, nom, flags, promo, comment
                                    FROM auth_user_md5
                                   WHERE user_id = ' . $mr['user_id'];
                        $res = XDB::query($watch);
                        $old_fields = $res->fetchOneAssoc();
                        $query = "UPDATE auth_user_md5 SET
                                         naissance = '$naiss',
                                         deces     = '$deces',
                                         password  = '$pass_encrypted',
                                         perms     = '$perms',
                                         prenom    = '".addslashes($prenm)."',
                                         nom       = '".addslashes($nom)."',
                                         flags     = '$flags',
                                         promo     = $promo,
                                         comment   = '".addslashes($comm)."'
                                   WHERE user_id = '{$mr['user_id']}'";
                        if (XDB::execute($query)) {
                            user_reindex($mr['user_id']);

                            $res = XDB::query($watch);
                            $new_fields = $res->fetchOneAssoc();

                            $mailer = new PlMailer("admin/useredit.mail.tpl");
                            $mailer->assign("user", S::v('forlife'));
                            $mailer->assign('old', $old_fields);
                            $mailer->assign('new', $new_fields);
                            $mailer->send();
                            
                            // update number of subscribers (perms or deceased may have changed)
                            update_NbIns();

                            $page->trig("updaté correctement.");
                        }
                        if (Env::v('nomusageN') != $mr['nom_usage']) {
                            require_once "xorg.misc.inc.php";
                            set_new_usage($mr['user_id'], Env::v('nomusageN'), make_username(Env::v('prenomN'), Env::v('nomusageN')));
                        }
                        if (Env::v('decesN') != $mr['deces']) {
                            require_once 'notifs.inc.php';
                            register_watch_op($mr['user_id'], WATCH_DEATH, $mr['deces']);
                            user_clear_all_subs($mr['user_id'], false);
                        }
                        $r = XDB::query("SELECT *, a.alias AS forlife,
                                                FIND_IN_SET('watch', u.flags) AS watch, FIND_IN_SET('femme', u.flags) AS sexe
                                           FROM auth_user_md5 AS u
                                      LEFT JOIN aliases       AS a ON (a.id = u.user_id AND type= 'a_vie')
                                          WHERE u.user_id = {?}", $mr['user_id']);
                        $mr = $r->fetchOneAssoc();

                        // If GoogleApps is enabled, the user did choose to use synchronized passwords,
                        // and the password was changed, updates the Google Apps password as well.
                        if ($globals->mailstorage->googleapps_domain && Env::v('newpass_clair') != "********") {
                            require_once 'googleapps.inc.php';
                            $account = new GoogleAppsAccount($mr['user_id'], $mr['forlife']);
                            if ($account->active() && $account->sync_password) {
                                $account->set_password($pass_encrypted);
                            }
                        }

                        // If GoogleApps is enabled, and the user is now disabled, disables the Google Apps account as well.
                        if ($globals->mailstorage->googleapps_domain &&
                            $new_fields['perms'] == 'disabled' &&
                            $new_fields['perms'] != $old_fields['perms']) {
                            require_once 'googleapps.inc.php';
                            $account = new GoogleAppsAccount($mr['user_id'], $mr['forlife']);
                            $account->suspend();
                        }
                        break;

                    // DELETE FROM auth_user_md5
                    case "u_kill":
                        user_clear_all_subs($mr['user_id']);
                        // update number of subscribers (perms or deceased may have changed)
                        update_NbIns();
                        $page->trig("'{$mr['user_id']}' a été désinscrit !");
                        $mailer = new PlMailer("admin/useredit.mail.tpl");
                        $mailer->assign("user", S::v('forlife'));
                        $mailer->assign("deletion", true);
                        $mailer->send();
                        break;

                    case "b_edit":
                        XDB::execute("DELETE FROM forums.innd WHERE uid = {?}", $mr['user_id']);
                        if (Env::v('write_perm') != "" || Env::v('read_perm') != ""  || Env::v('commentaire') != "" ) {
                          XDB::execute("INSERT INTO forums.innd
                                                SET ipmin = '0',
                                                    ipmax = '4294967295',
                                                    write_perm = {?},
                                                    read_perm = {?},
                                                    comment = {?},
                                                    priority = '200',
                                                    uid = {?}",
                                       Env::v('write_perm'), Env::v('read_perm'), Env::v('comment'), $mr['user_id']);
                        }
                        break;
                }
            }

            $res = XDB::query("SELECT  start, host
                                 FROM  logger.sessions
                                WHERE  uid={?} AND suid=0
                             ORDER BY  start DESC
                                LIMIT  1", $mr['user_id']);
            list($lastlogin,$host) = $res->fetchOneRow();
            $page->assign('lastlogin', $lastlogin);
            $page->assign('host', $host);

            $res = XDB::iterator("SELECT  alias
                                    FROM  virtual
                              INNER JOIN  virtual_redirect USING(vid)
                                   WHERE  type = 'user' AND redirect LIKE '" . $mr['forlife'] . "@%'");
            $page->assign('virtuals', $res);

            $page->assign('aliases', XDB::iterator(
                        "SELECT  alias, type='a_vie' AS for_life,FIND_IN_SET('bestalias',flags) AS best,expire
                           FROM  aliases
                          WHERE  id = {?} AND type!='homonyme'
                       ORDER BY  type!= 'a_vie'", $mr["user_id"]));
            if ($mr['perms'] != 'pending' && isset($redirect)) {
                $page->assign('emails', $redirect->emails);
            }

            $page->assign('mr',$mr);

            // Bans forums
            $res = XDB::query("SELECT  write_perm, read_perm, comment
                                 FROM  forums.innd
                                WHERE  uid = {?}", $mr['user_id']);
            $bans = $res->fetchOneAssoc();
            $page->assign('bans', $bans);
        }
    }

    function getMatricule($line, $key)
    {
        $mat = $line['matricule'];
        $year = intval(substr($mat, 0, 3));
        $rang = intval(substr($mat, 3, 3));
        if ($year > 200) { $year /= 10; };
        if ($year < 96) {
            return null;
        } else {
            return sprintf('%04u%04u', 1900+$year, $rang);
        }
    }

    function handler_promo(&$page, $action = null, $promo = null)
    {
        if (Env::has('promo')) {
            if(Env::i('promo') > 1900 && Env::i('promo') < 2050) {
                $action = Env::v('valid_promo') == 'Ajouter des membres' ? 'add' : 'ax';
                pl_redirect('admin/promo/' . $action . '/' . Env::i('promo'));
            } else {
                $page->trig('Promo non valide');
            }
        }

        $page->changeTpl('admin/promo.tpl');
        if ($promo > 1900 && $promo < 2050 && ($action == 'add' || $action == 'ax')) {
            $page->assign('promo', $promo);
        } else {
            return;
        }

        $importer = new CSVImporter('auth_user_md5', 'matricule');
        $importer->registerFunction('matricule', 'matricle Ecole vers X.org', array($this, 'getMatricule'));
        switch ($action) {
          case 'add':
            $fields = array('nom', 'nom_ini', 'prenom', 'naissance_ini',
                            'prenom_ini', 'promo', 'promo_sortie', 'flags',
                            'matricule', 'matricule_ax', 'perms');
            $importer->forceValue('promo', $promo);
            $importer->forceValue('promo_sortie', $promo + 3);
            break;
          case 'ax':
            $fields = array('matricule', 'matricule_ax');
            break;
        }
        $importer->apply($page, "admin/promo/$action/$promo", $fields);
    }

    function handler_homonyms(&$page, $op = 'list', $target = null) {
        $page->changeTpl('admin/homonymes.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Homonymes');
        require_once("homonymes.inc.php");

        if ($target) {
            if (! list($prenom,$nom,$forlife,$loginbis) = select_if_homonyme($target)) {
                $target=0;
            } else {
                $page->assign('nom',$nom);
                $page->assign('prenom',$prenom);
                $page->assign('forlife',$forlife);
                $page->assign('loginbis',$loginbis);
            }
        }

        $page->assign('op',$op);
        $page->assign('target',$target);

        // on a un $target valide, on prepare les mails
        if ($target) {

            // on examine l'op a effectuer
            switch ($op) {
                case 'mail':
                send_warning_homonyme($prenom, $nom, $forlife, $loginbis);
                switch_bestalias($target, $loginbis);
                    $op = 'list';
                    break;
                case 'correct':
                switch_bestalias($target, $loginbis);
                    XDB::execute("UPDATE aliases SET type='homonyme',expire=NOW() WHERE alias={?}", $loginbis);
                    XDB::execute("REPLACE INTO homonymes (homonyme_id,user_id) VALUES({?},{?})", $target, $target);
                send_robot_homonyme($prenom, $nom, $forlife, $loginbis);
                    $op = 'list';
                    break;
            }
        }

        if ($op == 'list') {
            $res = XDB::iterator(
                    "SELECT  a.alias AS homonyme,s.id AS user_id,s.alias AS forlife,
                             promo,prenom,nom,
                             IF(h.homonyme_id=s.id, a.expire, NULL) AS expire,
                             IF(h.homonyme_id=s.id, a.type, NULL) AS type
                       FROM  aliases       AS a
                  LEFT JOIN  homonymes     AS h ON (h.homonyme_id = a.id)
                 INNER JOIN  aliases       AS s ON (s.id = h.user_id AND s.type='a_vie')
                 INNER JOIN  auth_user_md5 AS u ON (s.id=u.user_id)
                      WHERE  a.type='homonyme' OR a.expire!=''
                   ORDER BY  a.alias,promo");
            $hnymes = Array();
            while ($tab = $res->next()) {
                $hnymes[$tab['homonyme']][] = $tab;
            }
            $page->assign_by_ref('hnymes',$hnymes);
        }
    }

    function handler_ax_xorg(&$page) {
        $page->changeTpl('admin/ax-xorg.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - AX/X.org');

        // liste des différences
        $res = XDB::query(
                'SELECT  u.promo,u.nom AS nom,u.prenom AS prenom,ia.nom AS nomax,ia.prenom AS prenomax,u.matricule AS mat,ia.matricule_ax AS matax
                   FROM  auth_user_md5 AS u
             INNER JOIN  identification_ax AS ia ON u.matricule_ax = ia.matricule_ax
                  WHERE  (SOUNDEX(u.nom) != SOUNDEX(ia.nom) AND SOUNDEX(CONCAT(ia.particule,u.nom)) != SOUNDEX(ia.nom)
                         AND SOUNDEX(u.nom) != SOUNDEX(ia.nom_patro) AND SOUNDEX(CONCAT(ia.particule,u.nom)) != SOUNDEX(ia.nom_patro))
                         OR u.prenom != ia.prenom OR (u.promo != ia.promo AND u.promo != ia.promo+1 AND u.promo != ia.promo-1)
               ORDER BY  u.promo,u.nom,u.prenom');
        $page->assign('diffs', $res->fetchAllAssoc());

        // gens à l'ax mais pas chez nous
        $res = XDB::query(
                'SELECT  ia.promo,ia.nom,ia.nom_patro,ia.prenom
                   FROM  identification_ax as ia
              LEFT JOIN  auth_user_md5 AS u ON u.matricule_ax = ia.matricule_ax
                  WHERE  u.nom IS NULL');
        $page->assign('mank', $res->fetchAllAssoc());

        // gens chez nous et pas à l'ax
        $res = XDB::query('SELECT promo,nom,prenom FROM auth_user_md5 WHERE matricule_ax IS NULL');
        $page->assign('plus', $res->fetchAllAssoc());
    }

    function handler_deaths(&$page, $promo = 0, $validate = false) {
        $page->changeTpl('admin/deces_promo.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Deces');

        if (!$promo)
            $promo = Env::i('promo');
        if (Env::has('sub10')) $promo -= 10;
        if (Env::has('sub01')) $promo -=  1;
        if (Env::has('add01')) $promo +=  1;
        if (Env::has('add10')) $promo += 10;

        $page->assign('promo',$promo);

        if ($validate) {
            $new_deces = array();
            $res = XDB::iterRow("SELECT user_id,matricule,nom,prenom,deces FROM auth_user_md5 WHERE promo = {?}", $promo);
            while (list($uid,$mat,$nom,$prenom,$deces) = $res->next()) {
                $val = Env::v($mat);
            if($val == $deces || empty($val)) continue;
            XDB::execute('UPDATE auth_user_md5 SET deces={?} WHERE matricule = {?}', $val, $mat);
            $new_deces[] = array('name' => "$prenom $nom", 'date' => "$val");
            if($deces=='0000-00-00' or empty($deces)) {
                require_once('notifs.inc.php');
                register_watch_op($uid, WATCH_DEATH, $val);
                require_once('user.func.inc.php');
                user_clear_all_subs($uid, false);   // by default, dead ppl do not loose their email
            }
            }
            $page->assign('new_deces',$new_deces);
        }

        $res = XDB::iterator('SELECT matricule, nom, prenom, deces FROM auth_user_md5 WHERE promo = {?} ORDER BY nom,prenom', $promo);
        $page->assign('decedes', $res);
    }

    function handler_dead_but_active(&$page) {
        $page->changeTpl('admin/dead_but_active.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Décédés');

        $res = XDB::iterator(
                "SELECT  u.promo, u.nom, u.prenom, u.deces, u.matricule_ax, a.alias, DATE(MAX(s.start)) AS last
                   FROM  auth_user_md5 AS u
              LEFT JOIN  aliases AS a ON (a.id = u.user_id AND a.type = 'a_vie')
              LEFT JOIN  logger.sessions AS s ON (s.uid = u.user_id AND suid = 0)
                  WHERE  perms IN ('admin', 'user') AND deces <> 0
               GROUP BY  u.user_id
               ORDER BY  u.promo, u.nom");
        $page->assign('dead', $res);
    }

    function handler_synchro_ax(&$page, $user = null, $action = null) {
        $page->changeTpl('admin/synchro_ax.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Synchro AX');

        require_once('synchro_ax.inc.php');

        if (is_ax_key_missing()) {
            $page->assign('no_private_key', true);
            $page->run();
        }

        require_once('user.func.inc.php');

        if ($user)
            $login = get_user_forlife($user);

        if (Env::has('user')) {
            $login = get_user_forlife(Env::v('user'));
            if ($login === false) {
                return;
            }
        }

        if (Env::has('mat')) {
            $res = XDB::query(
                    "SELECT  alias
                       FROM  aliases       AS a
                 INNER JOIN  auth_user_md5 AS u ON (a.id=u.user_id AND a.type='a_vie')
                      WHERE  matricule={?}", Env::i('mat'));
            $login = $res->fetchOneCell();
        }

        if ($login) {
            if ($action == 'import') {
                ax_synchronize($login, S::v('uid'));
            }
            // get details from user, but looking only info that can be seen by ax
            $user  = get_user_details($login, S::v('uid'), 'ax');
            $userax= get_user_ax($user['matricule_ax']);
            require_once 'profil.func.inc.php';
            $diff = diff_user_details($userax, $user, 'ax');

            $page->assign('x', $user);
            $page->assign('diff', $diff);
        }
    }

    function handler_validate(&$page, $action = 'list', $id = null)
    {
        $page->changeTpl('admin/valider.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Valider une demande');
                $page->addCssLink('nl.css');
        $page->addJsLink('ajax.js');
        require_once("validations.inc.php");


        if ($action == 'edit' and !is_null($id)) {
            $page->assign('preview_id', $id);
        }

        if(Env::has('uid') && Env::has('type') && Env::has('stamp')) {
            $req = Validate::get_typed_request(Env::v('uid'), Env::v('type'), Env::v('stamp'));
            if($req) { $req->handle_formu(); }
        }

        $r = XDB::iterator('SHOW COLUMNS FROM requests_answers');
        while (($a = $r->next()) && $a['Field'] != 'category');
        $page->assign('categories', $categories = explode(',', str_replace("'", '', substr($a['Type'], 5, -1))));

        $hidden = array();
        if (Post::has('hide')) {
            $hide = array();
            foreach ($categories as $cat)
                if (!Post::v($cat)) {
                    $hidden[$cat] = 1;
                    $hide[] = $cat;
                }
            setcookie('hide_requests', join(',',$hide), time()+(count($hide)?25920000:(-3600)), '/', '', 0);
        } elseif (Env::has('hide_requests'))  {
            foreach (explode(',',Env::v('hide_requests')) as $hide_type)
                $hidden[$hide_type] = true;
        }
        $page->assign('hide_requests', $hidden);

        $page->assign('vit', new ValidateIterator());
    }

    function handler_validate_answers(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title','Polytechnique.org - Administration - Réponses automatiques de validation');
        $page->assign('title', 'Gestion des réponses automatiques');
        $table_editor = new PLTableEditor('admin/validate/answers','requests_answers','id');
        $table_editor->describe('category','catégorie',true);
        $table_editor->describe('title','titre',true);
        $table_editor->describe('answer','texte',false);
        $table_editor->apply($page, $action, $id);
    }
    function handler_skins(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title','Polytechnique.org - Administration - Skins');
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

    function handler_postfix_blacklist(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title','Polytechnique.org - Administration - Postfix : Blacklist');
        $page->assign('title', 'Blacklist de postfix');
        $table_editor = new PLTableEditor('admin/postfix/blacklist','postfix_blacklist','email', true);
        $table_editor->describe('reject_text','Texte de rejet',true);
        $table_editor->describe('email','email',true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_postfix_whitelist(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title','Polytechnique.org - Administration - Postfix : Whitelist');
        $page->assign('title', 'Whitelist de postfix');
        $table_editor = new PLTableEditor('admin/postfix/whitelist','postfix_whitelist','email', true);
        $table_editor->describe('email','email',true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_mx_broken(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title', 'Polytechnique.org - Administration - MX Défaillants');
        $page->assign('title', 'MX Défaillant');
        $table_editor = new PLTableEditor('admin/mx/broken', 'mx_watch', 'host', true);
        $table_editor->describe('host', 'Masque', true);
        $table_editor->describe('state', 'Niveau', true);
        $table_editor->describe('text', 'Description du problème', false);
        $table_editor->apply($page, $action, $id);
    }
    function handler_logger_actions(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title','Polytechnique.org - Administration - Actions');
        $page->assign('title', 'Gestion des actions de logger');
        $table_editor = new PLTableEditor('admin/logger/actions','logger.actions','id');
        $table_editor->describe('text','intitulé',true);
        $table_editor->describe('description','description',true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_downtime(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title','Polytechnique.org - Administration - Coupures');
        $page->assign('title', 'Gestion des coupures');
        $table_editor = new PLTableEditor('admin/downtime','coupures','id');
        $table_editor->describe('debut','date',true);
        $table_editor->describe('duree','durée',false);
        $table_editor->describe('resume','résumé',true);
        $table_editor->describe('services','services affectés',true);
        $table_editor->describe('description','description',false);
        $table_editor->apply($page, $action, $id);
    }

    function handler_wiki(&$page, $action='list', $wikipage='', $wikipage2='')
    {
        require_once 'wiki.inc.php';

        if (S::v('core_rss_hash')) {
           $page->setRssLink('Changement Récents',
                             '/Site/AllRecentChanges?action=rss&user=' . S::v('forlife') . '&hash=' . S::v('core_rss_hash'));
        }
        // update wiki perms
        if ($action == 'update') {
            $perms_read = Post::v('read');
            $perms_edot = Post::v('edit');
            if ($perms_read || $perms_edit) {
                foreach ($_POST as $wiki_page => $val) if ($val == 'on') {
                    $wiki_page = str_replace('_', '/', $wiki_page);
                    if (!$perms_read || !$perms_edit)
                        list($perms0, $perms1) = wiki_get_perms($wiki_page);
                    if ($perms_read)
                        $perms0 = $perms_read;
                    if ($perms_edit)
                        $perms1 = $perms_edit;
                    wiki_set_perms($wiki_page, $perms0, $perms1);
                }
            }
        }

        if ($action == 'delete' && $wikipage != '') {
            if (wiki_delete_page($wikipage)) {
                $page->trig("La page ".$wikipage." a été supprimée.");
            } else {
                $page->trig("Impossible de supprimer la page ".$wikipage.".");
            }
        }

        if ($action == 'rename' && $wikipage != '' && $wikipage2 != '' && $wikipage != $wikipage2) {
            if ($changedLinks = wiki_rename_page($wikipage, $wikipage2)) {
                $s = 'La page <em>'.$wikipage.'</em> a été déplacée en <em>'.$wikipage2.'</em>.';
                if (is_numeric($changedLinks)) {
                    $s .= $changedLinks.' lien'.(($changedLinks>1)?'s ont été modifiés.':' a été modifié.');
                }
                $page->trig($s);
            } else {
                $page->trig("Impossible de déplacer la page ".$wikipage);
            }
        }

        $perms = wiki_perms_options();

        // list wiki pages and their perms
        $wiki_pages = array();
        $dir = wiki_work_dir();
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) if (substr($file,0,1) >= 'A' && substr($file,0,1) <= 'Z') {
                    list($read,$edit) = wiki_get_perms($file);
                    $wiki_pages[$file] = array('read' => $perms[$read], 'edit' => $perms[$edit]);
                    if (is_file($dir . '/cache_' . wiki_filename($file) . '.tpl')) {
                        $wiki_pages[$file]['cached'] = true;
                    }
                }
                closedir($dh);
            }
        }
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
        $page->addJsLink('jquery.js');
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
                Xdb::execute('INSERT IGNORE INTO ip_watch (ip, state, detection, last, uid, description)
                                          VALUES ({?}, {?}, CURDATE(), NOW(), {?}, {?})',
                             ip_to_uint(trim(Post::v('ipN'))), Post::v('stateN'), S::i('uid'), Post::v('descriptionN'));
            };
            break;

        case 'edit':
            Xdb::execute('UPDATE ip_watch
                             SET state = {?}, last = NOW(), uid = {?}, description = {?}
                           WHERE ip = {?}', Post::v('stateN'), S::i('uid'), Post::v('descriptionN'),
                          ip_to_uint(Post::v('ipN')));
            break;

        default:
            if ($action == 'delete' && !is_null($ip)) {
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
                             w.detection, w.state, a.alias AS forlife
                      FROM  ip_watch        AS w
                 LEFT JOIN  logger.sessions AS s  ON (s.ip = w.ip)
                 LEFT JOIN  logger.sessions AS s2 ON (s2.forward_ip = w.ip)
                 LEFT JOIN  aliases         AS a  ON (a.id = s.uid AND a.type = 'a_vie')
                  GROUP BY  w.ip, a.alias
                  ORDER BY  w.state, w.ip, a.alias";
            $it = Xdb::iterRow($sql);

            $table = array();
            $props = array();
            while (list($ip, $host, $date, $state, $forlife) = $it->next()) {
                $ip = uint_to_ip($ip);
                if (count($props) == 0 || $props['ip'] != $ip) {
                    if (count($props) > 0) {
                        $table[] = $props;
                    }
                    $props = array('ip'        => $ip,
                                   'host'      => $host,
                                   'detection' => $date,
                                   'state'     => $state,
                                   'users'     => array($forlife));
                } else {
                    $props['users'][] = $forlife;
                }
            }
            if (count($props) > 0) {
                $table[] = $props;
            }
            $page->assign('table', $table);
        } elseif ($action == 'edit') {
            $sql = "SELECT  w.detection, w.state, w.last, w.description,
                            a1.alias AS edit, a2.alias AS forlife, s.host
                      FROM  ip_watch        AS w
                 LEFT JOIN  aliases         AS a1 ON (a1.id = w.uid AND a1.type = 'a_vie')
                 LEFT JOIN  logger.sessions AS s  ON (w.ip = s.ip)
                 LEFT JOIN  aliases         AS a2 ON (a2.id = s.uid AND a2.type = 'a_vie')
                     WHERE  w.ip = {?}
                  GROUP BY  a2.alias
                  ORDER BY  a2.alias";
            $it = Xdb::iterRow($sql, ip_to_uint($ip));

            $props = array();
            while (list($detection, $state, $last, $description, $edit, $forlife, $host) = $it->next()) {
                if (count($props) == 0) {
                    $props = array('ip'          => $ip,
                                   'host'        => $host,
                                   'detection'   => $detection,
                                   'state'       => $state,
                                   'last'        => $last,
                                   'description' => $description,
                                   'edit'        => $edit,
                                   'users'       => array($forlife));
                } else {
                    $props['users'][] = $forlife;
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
            $page->trig('Dossier des icones introuvables.');
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
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
