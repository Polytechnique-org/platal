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
new_skinned_page('inscription/step3.tpl', AUTH_PUBLIC);
require_once("identification.inc.php");

$page->assign('forlife', $forlife);

if (!isvalid_email(Env::get('email'))) {
    $page->trig("Le champ 'E-mail' n'est pas valide.");
}

if (!isvalid_email_redirection(Env::get('email'))) {
    $page->trig("\"$forlife@polytechnique.org\" doit renvoyer vers un email existant valide.
            En particulier, il ne peut pas être renvoyé vers lui-même.");
}

if (!ereg("[0-3][0-9][0-1][0-9][1][9]([0-9]{2})", Env::get('naissance'))) {
    $page->trig("La 'Date de naissance' n'est pas correcte.
            Elle est obligatoire pour continuer mais ne sera jamais visible sur le site par la suite.");
}

if($page->nb_errs()) {
    $page->changeTpl('inscription/step2.tpl');
    require_once("applis.func.inc.php");
    $page->assign('homonyme', $homonyme);
    $page->assign('mailorg', $mailorg);
    
    $page->assign('prenom', $prenom);
    $page->assign('nom', $nom);
    
    $page->run();
}

$ins_id     = rand_url_id(12);
$pass_clair = rand_pass();
$password   = md5($pass_clair);
$date       = date("Y-m-j");

$birth = sprintf("%s-%s-%s", substr(Env::get('naissance'),4,4),
        substr(Env::get('naissance'),2,2), substr(Env::get('naissance'),0,2));

// nouvelle inscription
$sql="REPLACE INTO  en_cours
	       SET  ins_id='$ins_id', password='$password', matricule='$matricule', promo='$promo',
                    nom='".addslashes($nom)."', prenom='".addslashes($prenom)."', email='".Env::get('email')."',
	            naissance='$birth', date='$date', nationalite='".Env::get('nationalite')."',
                    appli_id1='".Env::get('appli_id1')."', appli_type1='".Env::get('appli_type1')."',
                    appli_id2='".Env::get('appli_id2')."', appli_type2='".Env::get('appli_type2')."',
                    loginbis='$mailorg', username='$forlife', homonyme='$homonyme'";
$globals->db->query($sql);

$globals->db->query("UPDATE auth_user_md5 SET last_known_email='".Env::get('email')."' WHERE matricule = $matricule");
// si on venait de la page maj.php, on met a jour la table envoidirect
if (Env::has('envoidirect')) {
  if (Env::get('envoidirect')) {
    $globals->db->query("UPDATE envoidirect SET date_succes=NOW() WHERE uid='".Env::get('envoidirect')."'");
  }
}

require_once('xorg.mailer.inc.php');
$mymail = new XOrgMailer('inscrire.mail.tpl');
$mymail->assign('mailorg', $mailorg);
$mymail->assign('lemail', Env::get('email'));
$mymail->assign('pass_clair', $pass_clair);
$mymail->assign('baseurl', $globals->baseurl);
$mymail->assign('ins_id', $ins_id);
$mymail->assign('subj', $mailorg."@polytechnique.org");
$mymail->send();

$page->run();
?>
