<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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
require_once("emails.inc.php");
require_once("user.func.inc.php");

if (Session::has('suid')) {
    $page->kill("déjà en SUID !!!");
}

if (Env::has('user_id')) {
    $login = get_user_login(Env::getInt('user_id'));
} elseif (Env::has('login')) {
    $login = get_user_login(Env::get('login'));
} else {
    $login = false;
}

if(Env::has('logs_button') && $login) {
    header("Location: logger.php?loguser=$login&year=".date('Y')."&month=".date('m'));
}

if(Env::has('suid_button') && $login) {
    $_SESSION['log']->log("suid_start", "login by ".Session::get('forlife'));
    $_SESSION['suid'] = $_SESSION;
    $r = $globals->db->query("SELECT id FROM aliases WHERE alias='$login'");
    if(list($uid) = mysql_fetch_row($r)) {
	start_connexion($uid,true);
	header("Location: ../");
    }
    mysql_free_result($r);
}

if ($login) {
    $r  = $globals->db->query("SELECT  *, a.alias AS forlife
                                 FROM  auth_user_md5 AS u
                           INNER JOIN  aliases       AS a ON ( a.id = u.user_id AND a.alias='$login' AND type!='homonyme' )");
    $mr = mysql_fetch_assoc($r);
    mysql_free_result($r);

    $redirect = new Redirect($mr['user_id']);

    // Check if there was a submission
    foreach($_POST as $key => $val) {
	switch ($key) {
	    case "add_fwd":
		$email = trim(Env::get('email'));
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
                    $globals->db->query("DELETE FROM aliases WHERE id='{$mr['user_id']}' AND alias='$val'
                            AND type!='a_vie' AND type!='homonyme'");
                    fix_bestalias($nr['user_id']);
                    $page->trig($val." a été supprimé");
                }
		break;

	    case "add_alias":
		$globals->db->query("INSERT INTO aliases (id,alias,type)
				     VALUES ('{$mr['user_id']}','".Env::get('email')."','alias')");
		break;

	    case "best":
		$globals->db->query("UPDATE  aliases SET flags='' WHERE flags='bestalias' AND id='{$mr['user_id']}'");
		$globals->db->query("UPDATE  aliases SET flags='epouse' WHERE flags='epouse,bestalias' AND id='{$mr['user_id']}'");
		$globals->db->query("UPDATE  aliases
					SET  flags=CONCAT(flags,',','bestalias')
				      WHERE  id='{$mr['user_id']}' AND alias='$val'");
		break;


	    // Editer un profil
	    case "u_edit":
		$pass_md5B = Env::get('newpass_clair') != "********" ? md5(Env::get('newpass_clair')) : Env::get('passw');
                $naiss = Env::get('naissanceN');
                $perms = Env::get('permsN');
                $prenm = Env::get('prenomN');
                $nom   = Env::get('nomN');
                $promo = Env::getInt('promo');
                $nom   = Env::get('nomN');
                $comm  = Env::get('commentN');

		$query = "UPDATE auth_user_md5 SET
			    naissance = '$naiss',
			    password  = '$pass_md5B',
			    perms     = '$perms',
			    prenom    = '$prenm',
			    nom       = '$nom',
			    promo     = $promo,
			    comment   = '$comm'
			  WHERE user_id = '{$mr['user_id']}'";
		if ($globals->db->query($query)) {
                    // FIXME: recherche
                    system('echo 1 > /tmp/flag_recherche');

                    require_once("diogenes.hermes.inc.php");
                    $mailer = new HermesMailer();
                    $mailer->setFrom("webmaster@polytechnique.org");
                    $mailer->addTo("web@polytechnique.org");
                    $mailer->setSubject("INTERVENTION ADMIN (".Session::get('forlife']).")");
                    $mailer->setTxtBody(preg_replace("/[ \t]+/", ' ', $query));
                    $mailer->send();

                    $page->trig("updaté correctement.");
                }
		$r  = $globals->db->query("SELECT  *, a.alias AS forlife
                                             FROM  auth_user_md5 AS u
                                       INNER JOIN  aliases       AS a ON (u.user_id=a.id)
                                            WHERE  user_id = {$mr['user_id']}");
                $mr = mysql_fetch_assoc($r);
		mysql_free_result($r);
		break;

            // DELETE FROM auth_user_md5
	    case "u_kill":
		user_clear_all_subs($mr['user_id']);
                $page->trig("'{$mr['user_id']}' a été désinscrit !");
		require_once("diogenes.hermes.inc.php");
		$mailer = new HermesMailer();
		$mailer->setFrom("webmaster@polytechnique.org");
		$mailer->addTo("web@polytechnique.org");
		$mailer->setSubject("INTERVENTION ADMIN (".Session::get('forlife']).")");
		$mailer->setTxtBody("\nUtilisateur $login effacé");
		$mailer->send();
		break;
	}
    }

    $result=$globals->db->query("SELECT  UNIX_TIMESTAMP(start), host
			           FROM  logger.sessions
				  WHERE  uid={$mr['user_id']} AND suid=0
			       ORDER BY  start DESC
				  LIMIT  1");
    list($lastlogin,$host) = mysql_fetch_row($result);
    mysql_free_result($result);
    $page->assign('lastlogin', $lastlogin);
    $page->assign('host', $host);

    $page->mysql_assign("SELECT  alias, type='a_vie' AS for_life,FIND_IN_SET('bestalias',flags) AS best,expire
			   FROM  aliases
			  WHERE  id = {$mr["user_id"]} AND type!='homonyme'
		       ORDER BY  type!= 'a_vie'", 'aliases');
    $page->assign_by_ref('xorgmails', $xorgmails);
    $page->assign_by_ref('email_panne', $email_panne);    
    $page->assign('emails',$redirect->emails);

    $page->assign('mr',$mr);
}

$page->run();
?>
