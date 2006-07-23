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

require_once("xorg.inc.php");
new_admin_page('admin/utilisateurs.tpl');
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
} else {
    $login = false;
}

if(Env::has('logs_button') && $login) {
    http_redirect("logger.php?loguser=$login&year=".date('Y')."&month=".date('m'));
}

if (Env::has('ax_button') && $login) {
    http_redirect("synchro_ax.php?user=$login");
}

if(Env::has('suid_button') && $login) {
    $_SESSION['log']->log("suid_start", "login by ".S::v('forlife'));
    $_SESSION['suid'] = $_SESSION;
    $r = XDB::query("SELECT id FROM aliases WHERE alias={?}", $login);
    if($uid = $r->fetchOneCell()) {
	start_connexion($uid,true);
	http_redirect("../");
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

$page->run();

// vim:set et sws=4 sts=4 sw=4:
?>
