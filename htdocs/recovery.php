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
new_skinned_page('recovery.tpl', AUTH_PUBLIC);

if (isset($_REQUEST['login']) and isset($_REQUEST['birth']))  {
    if (!ereg("[0-3][0-9][0-1][0-9][1][9]([0-9]{2})", $_REQUEST['birth'])) {
        $page->trigger("Date de naissance incorrecte ou incohérente");
        $page->run();
    }
    $birth = sprintf("%s-%s-%s", substr($_REQUEST["birth"],4,4), substr($_REQUEST["birth"],2,2), substr($_REQUEST["birth"],0,2));

    $mailorg=strtok($_REQUEST['login'],"@");

    // paragraphe rajouté : si la date de naissance dans la base n'existe pas, on l'update
    // avec celle fournie ici en espérant que c'est la bonne

    $sql="SELECT  user_id, naissance
	    FROM  auth_user_md5 AS u
      INNER JOIN  aliases       AS a ON (u.user_id=a.id AND type!='homonyme')
	    WHERE a.alias='$mailorg' AND u.perms IN ('admin','user') AND u.deces=0";
    $result=$globals->db->query($sql);
    if (list($uid,$naissance)=mysql_fetch_array($result)) {
        if((strlen($naissance))<5) {
            $globals->db->query("UPDATE auth_user_md5 SET naissance='$birth' WHERE user_id=$uid");
            $naissance = $birth;
        }
    }
    mysql_free_result($result);

    if ($naissance == $birth) {
        $page->assign('ok', true);
        $url=rand_url_id();
        $stamp=date("Y-m-d H:i:s");
        $sql="INSERT INTO perte_pass (certificat,uid,created) VALUES ('$url',$uid,'$stamp')";

        $globals->db->query($sql);

        // on recupere les emails sans tenir comptes du flags active (ni des autres)
        // sauf qu'il ne faut pas prendre la ligne qui possède l'éventuel appel 
        // au filtre personnel (ligne dont le num = 0)
        $result=$globals->db->query("select email from emails where uid = $uid and NOT FIND_IN_SET('filter', flags)");
        
        $emails = array();
        while(list($email) = mysql_fetch_row($result)) {
            $emails[] = $email;
        }
        mysql_free_result($result);
        $emails = implode(',', $emails);
        
	require_once("diogenes.hermes.inc.php");
	$mymail = new HermesMailer();
	$mymail->setFrom('"Gestion des mots de passe" <support+password@polytechnique.org>');
	$mymail->addTo($emails);
	$mymail->setSubject('Ton certificat d\'authentification');
        $mymail->setTxtBody("Visite la page suivante qui expire dans six heures :
{$globals->baseurl}/tmpPWD.php?certificat=$url

Si en cliquant dessus tu n'y arrives pas, copie intégralement l'adresse dans la barre de ton navigateur.

--
Polytechnique.org
\"Le portail des élèves & anciens élèves de l'Ecole polytechnique\"".((!empty($_POST["email"])) ? "

Adresse de secours : {$_POST['email']}" : "")."


Mail envoyé à {$_REQUEST['login']}");
        $mymail->send();

        // on cree un objet logger et on log l'evenement
	$logger = $_SESSION['log'] = (isset($logger) ? $logger : new DiogenesCoreLogger($uid));
	$logger->log("recovery",$emails);
    } else {
        $page->trigger("Pas de résultat correspondant aux champs entrés dans notre base de données.");
    }
}

$page->run();
?>
