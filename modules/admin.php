<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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
            'admin'                        => $this->make_hook('default',   AUTH_MDP, 'admin'),
            'admin/postfix/delayed'        => $this->make_hook('postfix_delayed', AUTH_MDP, 'admin'),
            'admin/postfix/regexp_bounces' => $this->make_hook('postfix_regexpsbounces', AUTH_MDP, 'admin'),
            'admin/logger'                 => $this->make_hook('logger', AUTH_MDP, 'admin'),
            'admin/logger/actions'         => $this->make_hook('logger_action', AUTH_MDP, 'admin'),
            'admin/users'                  => $this->make_hook('user', AUTH_MDP, 'admin'),
            'admin/homonyms'               => $this->make_hook('homonyms', AUTH_MDP, 'admin'),
            'admin/ax-xorg'                => $this->make_hook('ax_xorg', AUTH_MDP, 'admin'),
            'admin/deaths'                 => $this->make_hook('deaths', AUTH_MDP, 'admin'),
            'admin/synchro_ax'             => $this->make_hook('synchro_ax', AUTH_MDP, 'admin'),
            'admin/events'                 => $this->make_hook('events', AUTH_MDP, 'admin'),
            'admin/formations'             => $this->make_hook('formations', AUTH_MDP, 'admin'),
            'admin/newsletter'             => $this->make_hook('newsletter', AUTH_MDP, 'admin'),
            'admin/newsletter/edit'        => $this->make_hook('newsletter_edit', AUTH_MDP, 'admin'),
            'admin/lists'                  => $this->make_hook('lists', AUTH_MDP, 'admin'),
            'admin/validate'               => $this->make_hook('validate', AUTH_MDP, 'admin'),
            'admin/geoloc'                 => $this->make_hook('geoloc', AUTH_MDP, 'admin'),
            'admin/geoloc/dynamap'         => $this->make_hook('geoloc_dynamap', AUTH_MDP, 'admin'),
            'admin/trombino'               => $this->make_hook('trombino', AUTH_MDP, 'admin'),
        );
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

    function handler_logger(&$page) {
        $page->changeTpl('logger-view.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Logs des sessions');
        require_once('diogenes/diogenes.logger-view.inc.php');

        if (!Env::has('logauth')) {
            $_REQUEST['logauth'] = 'native';
        }

        $logview = new DiogenesLoggerView;
        $logview->run($page);

        $page->fakeDiogenes();
    }

    function handler_user(&$page, $login = false) {
        $page->changeTpl('admin/utilisateurs.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Edit/Su/Log');
        require_once("emails.inc.php");
        require_once("user.func.inc.php");

        if (S::has('suid')) {
            $page->kill("déjà en SUID !!!");
        }

        if (Env::has('user_id')) {
            $login = get_user_login(Env::i('user_id'));
        } elseif (Env::has('login')) {
            $login = get_user_login(Env::v('login'));
        }

        if(Env::has('logs_button') && $login) {
            pl_redirect("admin/logger?login=$login&year=".date('Y')."&month=".date('m'));
        }

        if (Env::has('ax_button') && $login) {
            pl_redirect("admin/synchro_ax/$login");
        }

        if(Env::has('suid_button') && $login) {
            $_SESSION['log']->log("suid_start", "login by ".S::v('forlife'));
            $_SESSION['suid'] = $_SESSION;
            $r = XDB::query("SELECT id FROM aliases WHERE alias={?}", $login);
            if($uid = $r->fetchOneCell()) {
                start_connexion($uid,true);
                pl_redirect("");
            }
        }

        if ($login) {
            $r  = XDB::query("SELECT  *, a.alias AS forlife, u.flags AS sexe
                                          FROM  auth_user_md5 AS u
                                    INNER JOIN  aliases       AS a ON ( a.id = u.user_id AND a.alias={?} AND type!='homonyme' )", $login);
            $mr = $r->fetchOneAssoc();

            $redirect = new Redirect($mr['user_id']);

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
                            XDB::execute("DELETE FROM aliases WHERE id={?} AND alias={?}
                                    AND type!='a_vie' AND type!='homonyme'", $mr['user_id'], $val);
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
                    case "add_alias":
                        XDB::execute("INSERT INTO  aliases (id,alias,type) VALUES  ({?}, {?}, 'alias')",
                                $mr['user_id'], Env::v('email'));
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
                    $perms = Env::v('permsN');
                    $prenm = Env::v('prenomN');
                    $nom   = Env::v('nomN');
                    $promo = Env::i('promoN');
                    $sexe  = Env::v('sexeN');
                    $comm  = Env::v('commentN');

                    $query = "UPDATE auth_user_md5 SET
                            naissance = '$naiss',
                            password  = '$pass_encrypted',
                            perms     = '$perms',
                            prenom    = '".addslashes($prenm)."',
                            nom       = '".addslashes($nom)."',
                            flags     = '$sexe',
                            promo     = $promo,
                            comment   = '".addslashes($comm)."'
                        WHERE user_id = '{$mr['user_id']}'";
                    if (XDB::execute($query)) {
                            user_reindex($mr['user_id']);

                            require_once("diogenes/diogenes.hermes.inc.php");
                            $mailer = new HermesMailer();
                            $mailer->setFrom("webmaster@polytechnique.org");
                            $mailer->addTo("web@polytechnique.org");
                            $mailer->setSubject("INTERVENTION de ".S::v('forlife'));
                            $mailer->setTxtBody(preg_replace("/[ \t]+/", ' ', $query));
                            $mailer->send();

                            $page->trig("updaté correctement.");
                        }
                        if (Env::v('nomusageN') != $mr['nom_usage']) {
                            require_once('nomusage.inc.php');
                            set_new_usage($mr['user_id'], Env::v('nomusageN'), make_username(Env::v('prenomN'), Env::v('nomusageN')));
                        }
                        $r  = XDB::query("SELECT  *, a.alias AS forlife, u.flags AS sexe
                                                      FROM  auth_user_md5 AS u
                                                INNER JOIN  aliases       AS a ON (u.user_id=a.id)
                                                     WHERE  user_id = {?}", $mr['user_id']);
                        $mr = $r->fetchOneAssoc();
                        break;

                    // DELETE FROM auth_user_md5
                    case "u_kill":
                        user_clear_all_subs($mr['user_id']);
                        $page->trig("'{$mr['user_id']}' a été désinscrit !");
                        require_once("diogenes/diogenes.hermes.inc.php");
                        $mailer = new HermesMailer();
                        $mailer->setFrom("webmaster@polytechnique.org");
                        $mailer->addTo("web@polytechnique.org");
                        $mailer->setSubject("INTERVENTION de ".S::v('forlife'));
                        $mailer->setTxtBody("\nUtilisateur $login effacé");
                        $mailer->send();
                        break;
                }
            }

            $res = XDB::query("SELECT  UNIX_TIMESTAMP(start), host
                                           FROM  logger.sessions
                                          WHERE  uid={?} AND suid=0
                                       ORDER BY  start DESC
                                          LIMIT  1", $mr['user_id']);
            list($lastlogin,$host) = $res->fetchOneRow();
            $page->assign('lastlogin', $lastlogin);
            $page->assign('host', $host);

            $page->assign('aliases', XDB::iterator(
                        "SELECT  alias, type='a_vie' AS for_life,FIND_IN_SET('bestalias',flags) AS best,expire
                           FROM  aliases
                          WHERE  id = {?} AND type!='homonyme'
                       ORDER BY  type!= 'a_vie'", $mr["user_id"]));
            $page->assign('xorgmails', $xorgmails);
            $page->assign('email_panne', $email_panne);    
            $page->assign('emails',$redirect->emails);

            $page->assign('mr',$mr);
        }
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
        	    user_clear_all_subs($uid, false);	// by default, dead ppl do not loose their email
        	}
            }
            $page->assign('new_deces',$new_deces);
        }
        
        $res = XDB::iterator('SELECT matricule, nom, prenom, deces FROM auth_user_md5 WHERE promo = {?} ORDER BY nom,prenom', $promo);
        $page->assign('decedes', $res);
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
    
    function handler_events(&$page, $arch) {
        $page->changeTpl('admin/evenements.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Evenements');
        
        $arch = $arch == 'archives';
        $evid = Post::i('evt_id');
        $page->assign('arch', $arch);
        
        switch(Post::v('action')) {
            case "Proposer":
                XDB::execute('UPDATE evenements SET titre={?}, texte={?}, peremption={?}, promo_min={?}, promo_max={?} WHERE id = {?}', 
                        Post::v('titre'), Post::v('texte'), Post::v('peremption'), Post::v('promo_min'), Post::v('promo_max'), $evid);
                break;
        
            case "Valider":
                XDB::execute('UPDATE evenements SET creation_date = creation_date, flags = CONCAT(flags,",valide") WHERE id = {?}', $evid);
                break;
        
            case "Invalider":
                XDB::execute('UPDATE evenements SET creation_date = creation_date, flags = REPLACE(flags,"valide", "") WHERE id = {?}', $evid);
                break;
        
            case "Supprimer":
                XDB::execute('DELETE from evenements WHERE id = {?}', $evid);
                break;
        
            case "Archiver":
                XDB::execute('UPDATE evenements SET creation_date = creation_date, flags = CONCAT(flags,",archive") WHERE id = {?}', $evid);
                break;
        
            case "Desarchiver":
                XDB::execute('UPDATE evenements SET creation_date = creation_date, flags = REPLACE(flags,"archive","") WHERE id = {?}', $evid);
                break;
        
            case "Editer":
                $res = XDB::query('SELECT titre, texte, peremption, promo_min, promo_max FROM evenements WHERE id={?}', $evid);
                list($titre, $texte, $peremption, $promo_min, $promo_max) = $res->fetchOneRow();
                $page->assign('mode', 'edit');
                $page->assign('titre',$titre);
                $page->assign('texte',$texte);
                $page->assign('promo_min',$promo_min);
                $page->assign('promo_max',$promo_max);
                $page->assign('peremption',$peremption);
        
                $select = "";
                for ($i = 1 ; $i < 30 ; $i++) {
                    $p_stamp=date("Ymd",time()+3600*24*$i);
                    $year=substr($p_stamp,0,4);
                    $month=substr($p_stamp,4,2);
                    $day=substr($p_stamp,6,2);
        
                    $select .= "<option value=\"$p_stamp\"" . (($p_stamp == strtr($peremption, array("-" => ""))) ? " selected" : "")."> $day / $month / $year</option>\n";
                }
                $page->assign('select',$select);
        
                break;
        }
        
        if ($action != "Editer") {
        
            $sql = "SELECT  e.id, e.titre, e.texte,
                            DATE_FORMAT(e.creation_date,'%d/%m/%Y %T') AS creation_date,
                            DATE_FORMAT(e.peremption,'%d/%m/%Y') AS peremption,
                            e.promo_min, e.promo_max,
                            FIND_IN_SET('valide', e.flags) AS fvalide,
                            FIND_IN_SET('archive', e.flags) AS farch,
                            u.promo, u.nom, u.prenom, a.alias AS forlife
                      FROM  evenements    AS e
                INNER JOIN  auth_user_md5 AS u ON(e.user_id = u.user_id)
                INNER JOIN  aliases AS a ON (u.user_id = a.id AND a.type='a_vie')
                     WHERE  ".($arch ? "" : "!")."FIND_IN_SET('archive',e.flags)
                  ORDER BY  FIND_IN_SET('valide',e.flags), peremption";
            $page->assign('evs', XDB::iterator($sql));
        }
    }
    
    function handler_newsletter(&$page, $new = false) {
        $page->changeTpl('admin/newsletter.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Newsletter : liste');
        require_once("newsletter.inc.php");
        
        if($new) {
           insert_new_nl();
           pl_redirect("admin/newsletter");
        }
        
        $page->assign_by_ref('nl_list', get_nl_slist());
    }
    
    function handler_newsletter_edit(&$page, $nid = 'last', $aid = null, $action = 'edit') {
        $page->changeTpl('admin/newsletter_edit.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Newsletter : Edition'); 
        require_once("newsletter.inc.php");
        
        $nl  = new NewsLetter($nid);
        
        if($action == 'delete') {
            $nl->delArticle($aid);
            pl_redirect("admin/newsletter/edit/$nid");
        }
        
        if($aid == 'update') {
            $nl->_title = Post::v('title');
            $nl->_date  = Post::v('date');
            $nl->_head  = Post::v('head');
            $nl->save();
        }
        
        if(Post::v('save')) {
            $art  = new NLArticle(Post::v('title'), Post::v('body'), Post::v('append'),
                    $aid, Post::v('cid'), Post::v('pos'));
            $nl->saveArticle($art);
            pl_redirect("admin/newsletter/edit/$nid");
        }
        
        if($action == 'edit') {
            $eaid = $aid;
            if(Post::has('title')) {
                $art  = new NLArticle(Post::v('title'), Post::v('body'), Post::v('append'),
                        $eaid, Post::v('cid'), Post::v('pos'));
            } else {
        	   $art = ($eaid == 'new') ? new NLArticle() : $nl->getArt($eaid);
            }
            $page->assign('art', $art);
        }
        
        $page->assign_by_ref('nl',$nl);
    }
    
    function handler_lists(&$page) {
        $page->changeTpl('admin/lists.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Mailing lists');
        require_once 'lists.inc.php';
        
        $client =& lists_xmlrpc(S::v('uid'), S::v('password'));
        $listes = $client->get_all_lists();
        $page->assign_by_ref('listes',$listes);
    }
    
    function handler_validate(&$page) {
        $page->changeTpl('admin/valider.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Valider une demande');
        require_once("validations.inc.php");
        
        if(Env::has('uid') && Env::has('type') && Env::has('stamp')) {
            $req = Validate::get_request(Env::v('uid'), Env::v('type'), Env::v('stamp'));
            if($req) { $req->handle_formu(); }
        }
        
        $page->assign('vit', new ValidateIterator());
    }
    
    function handler_geoloc(&$page, $action = false) {
        $page->changeTpl('admin/geoloc.tpl');
        require_once("geoloc.inc.php");
        $page->assign('xorg_title','Polytechnique.org - Administration - Geolocalisation');
        
        $nb_synchro = 0;
        
        if (Env::has('id') && is_numeric(Env::v('id'))) {
            if (synchro_city(Env::v('id'))) $nb_synchro ++;
        }
        
        if ($action == 'missinglat') {
            $res = XDB::iterRow("SELECT id FROM geoloc_city WHERE lat = 0 AND lon = 0");
            while ($a = $res->next()) if (synchro_city($a[0])) $nb_synchro++;
        }
        
        if ($nb_synchro) 
            $page->trig(($nb_synchro > 1)?($nb_synchro." villes ont été synchronisées"):"Une ville a été synchronisée");
        
        $res = XDB::query("SELECT COUNT(*) FROM geoloc_city WHERE lat = 0 AND lon = 0");
        $page->assign("nb_missinglat", $res->fetchOneCell());
    }
    
    function handler_geoloc_dynamap(&$page, $action = false) {
        $page->changeTpl('admin/geoloc_dynamap.tpl');
        
        if ($action == 'cities_not_on_map') {
            require_once('geoloc.inc.php');
            if (!fix_cities_not_on_map(20))
                $page->trig("Impossible d'accéder au webservice");
            else
                $refresh = true;
        }
        
        if ($action == 'smallest_maps') {
            require_once('geoloc.inc.php');
            set_smallest_levels();
        }
        
        if ($action == 'precise_coordinates') {
            XDB::execute("UPDATE adresses AS a INNER JOIN geoloc_city AS c ON(a.cityid = c.id) SET a.glat = c.lat / 100000, a.glng = c.lon / 100000");
        }
        
        if ($action == 'newmaps') {
            require_once('geoloc.inc.php');
            if (!get_new_maps(Env::v('url')))
                $page->trig("Impossible d'accéder aux nouvelles cartes");
        }
        
        $countMissing = XDB::query("SELECT COUNT(*) FROM geoloc_city AS c LEFT JOIN geoloc_city_in_maps AS m ON(c.id = m.city_id) WHERE m.city_id IS NULL");
        $missing = $countMissing->fetchOneCell();
        
        $countNoSmallest = XDB::query("SELECT SUM(IF(infos = 'smallest',1,0)) AS n FROM geoloc_city_in_maps GROUP BY city_id ORDER BY n");
        $noSmallest = $countNoSmallest->fetchOneCell() == 0;
        
        $countNoCoordinates = XDB::query("SELECT COUNT(*) FROM adresses WHERE cityid IS NOT NULL AND glat = 0 AND glng = 0");
        $noCoordinates = $countNoCoordinates->fetchOneCell();
        
        if (isset($refresh) && $missing) {
            $page->assign("xorg_extra_header", "<meta http-equiv='Refresh' content='3'/>");
        }
        $page->assign("nb_cities_not_on_map", $missing);
        $page->assign("no_smallest", $noSmallest);
        $page->assign("no_coordinates", $noCoordinates);
    }
    
    function handler_trombino(&$page, $uid = null, $action = null) {
        $page->changeTpl('admin/admin_trombino.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Trombino');
        $page->assign('uid', $uid);
        
        $q   = XDB::query(
                "SELECT  a.alias,promo
                  FROM  auth_user_md5 AS u
            INNER JOIN  aliases       AS a ON ( u.user_id = a.id AND type='a_vie' )
                 WHERE  user_id = {?}", $uid);
        list($forlife, $promo) = $q->fetchOneRow();
        
        switch ($action) {
        
            case "original":
                header("Content-type: image/jpeg");
        	readfile("/home/web/trombino/photos".$promo."/".$forlife.".jpg");
                exit;
        	break;
        
            case "new":
                $data = file_get_contents($_FILES['userfile']['tmp_name']);
            	list($x, $y) = getimagesize($_FILES['userfile']['tmp_name']);
            	$mimetype = substr($_FILES['userfile']['type'], 6);
            	unlink($_FILES['userfile']['tmp_name']);
                XDB::execute(
                        "REPLACE INTO photo SET uid={?}, attachmime = {?}, attach={?}, x={?}, y={?}",
                        $uid, $mimetype, $data, $x, $y);
            	break;
        
            case "delete":
                XDB::execute('DELETE FROM photo WHERE uid = {?}', $uid);
                break;
        }
        
        $page->assign('forlife', $forlife);
    }
    
}

?>
