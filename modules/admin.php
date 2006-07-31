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
            'admin/ax-xorg'                => $this->make_hook('ax_xorg', AUTH_MDP, 'admin'),
            'admin/deaths'                 => $this->make_hook('deaths', AUTH_MDP, 'admin'),
            'admin/downtime'               => $this->make_hook('downtime', AUTH_MDP, 'admin'),
            'admin/homonyms'               => $this->make_hook('homonyms', AUTH_MDP, 'admin'),
            'admin/logger'                 => $this->make_hook('logger', AUTH_MDP, 'admin'),
            'admin/logger/actions'         => $this->make_hook('logger_actions', AUTH_MDP, 'admin'),
            'admin/postfix/blacklist'      => $this->make_hook('postfix_blacklist', AUTH_MDP, 'admin'),
            'admin/postfix/delayed'        => $this->make_hook('postfix_delayed', AUTH_MDP, 'admin'),
            'admin/postfix/regexp_bounces' => $this->make_hook('postfix_regexpsbounces', AUTH_MDP, 'admin'),
            'admin/postfix/whitelist'      => $this->make_hook('postfix_whitelist', AUTH_MDP, 'admin'),
            'admin/skins'                  => $this->make_hook('skins', AUTH_MDP, 'admin'),
            'admin/synchro_ax'             => $this->make_hook('synchro_ax', AUTH_MDP, 'admin'),
            'admin/user'                   => $this->make_hook('user', AUTH_MDP, 'admin'),
            'admin/validate'               => $this->make_hook('validate', AUTH_MDP, 'admin'),
            'admin/validate/answers'       => $this->make_hook('validate_answers', AUTH_MDP, 'admin'),
            'admin/wiki'                   => $this->make_hook('wiki', AUTH_MDP, 'admin'),
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
        require_once dirname(__FILE__).'/../classes/LoggerView.php';

        if (!Env::has('logauth')) {
            $_REQUEST['logauth'] = 'native';
        }

        $logview = new LoggerView;
        $logview->run($page);

        $page->assign('xorg_title','Polytechnique.org - Administration - Logs des sessions');
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
                    $deces = Env::v('decesN');
                    $perms = Env::v('permsN');
                    $prenm = Env::v('prenomN');
                    $nom   = Env::v('nomN');
                    $promo = Env::i('promoN');
                    $sexe  = Env::v('sexeN');
                    $comm  = Env::v('commentN');

                    $query = "UPDATE auth_user_md5 SET
                            naissance = '$naiss',
                            deces     = '$deces',
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
    
    function handler_validate(&$page) {
        $page->changeTpl('admin/valider.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Valider une demande');
        require_once("validations.inc.php");
        
        if(Env::has('uid') && Env::has('type') && Env::has('stamp')) {
            $req = Validate::get_request(Env::v('uid'), Env::v('type'), Env::v('stamp'));
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
        require_once('../classes/PLTableEditor.php');
        $page->assign('xorg_title','Polytechnique.org - Administration - Réponses automatiques de validation');
        $page->assign('title', 'Gestion des réponses automatiques');
        $table_editor = new PLTableEditor('admin/validate/answers','requests_answers','id');
        $table_editor->describe('category','catégorie',true);
        $table_editor->describe('title','titre',true);
        $table_editor->describe('answer','texte',false);
        $table_editor->apply($page, $action, $id);
    }
    function handler_skins(&$page, $action = 'list', $id = null) {
        require_once('../classes/PLTableEditor.php');
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
        require_once('../classes/PLTableEditor.php');
        $page->assign('xorg_title','Polytechnique.org - Administration - Postfix : Blacklist');
        $page->assign('title', 'Blacklist de postfix');
        $table_editor = new PLTableEditor('admin/postfix/blacklist','postfix_blacklist','email', true);
        $table_editor->describe('reject_text','Texte de rejet',true);
        $table_editor->describe('email','email',true);
        $table_editor->apply($page, $action, $id);
    }  
    function handler_postfix_whitelist(&$page, $action = 'list', $id = null) {
        require_once('../classes/PLTableEditor.php');
        $page->assign('xorg_title','Polytechnique.org - Administration - Postfix : Whitelist');
        $page->assign('title', 'Whitelist de postfix');
        $table_editor = new PLTableEditor('admin/postfix/whitelist','postfix_whitelist','email', true);
        $table_editor->describe('email','email',true);
        $table_editor->apply($page, $action, $id);
    }  
    function handler_logger_actions(&$page, $action = 'list', $id = null) {
        require_once('../classes/PLTableEditor.php');
        $page->assign('xorg_title','Polytechnique.org - Administration - Actions');
        $page->assign('title', 'Gestion des actions de logger');
        $table_editor = new PLTableEditor('admin/logger/actions','logger.actions','id');
        $table_editor->describe('text','intitulé',true);
        $table_editor->describe('description','description',true);
        $table_editor->apply($page, $action, $id);
    }  
    function handler_downtime(&$page, $action = 'list', $id = null) {
        require_once('../classes/PLTableEditor.php');
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
    function handler_wiki(&$page, $action='list') {
        require_once 'wiki.inc.php';
        
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
        
        $perms = wiki_perms_options();
        
        // list wiki pages and their perms
        $wiki_pages = array();
        $dir = wiki_work_dir();
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) if (substr($file,0,1) >= 'A' && substr($file,0,1) <= 'Z') {
                    list($read,$edit) = wiki_get_perms($file);
                    $wiki_pages[$file] = array('read' => $perms[$read], 'edit' => $perms[$edit]);
                }
                closedir($dh);
            }
        }
        ksort($wiki_pages);
        
        $page->changeTpl('admin/wiki.tpl');
        $page->assign('wiki_pages', $wiki_pages);        
        $page->assign('perms_opts', $perms);
    }
}

?>
