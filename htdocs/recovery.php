<?php
require("auto.prepend.inc.php");
new_skinned_page('recovery.tpl', AUTH_PUBLIC);

$errors = Array();

if (isset($_REQUEST['login']) and isset($_REQUEST['birth']))  {
    if (!ereg("[0-3][0-9][0-1][0-9][1][9]([0-9]{2})", $_REQUEST['birth'])) {
        $page->assign('error', "Date de naissance incorrecte ou incohérente");
        $page->run();
    }

    $mailorg=strtok($_REQUEST['login'],"@");

    // paragraphe rajouté : si la date de naissance dans la base n'existe pas, on l'update
    // avec celle fournie ici en espérant que c'est la bonne

    $sql="SELECT user_id, naissance FROM auth_user_md5 WHERE username='$mailorg'";
    $result=$globals->db->query($sql);
    if (list($uid,$naissance)=mysql_fetch_array($result)) {
        if((strlen($naissance))<5) {
            $globals->db->query("UPDATE auth_user_md5 SET naissance={$_REQUEST['birth']} WHERE user_id=$uid");
            $naissance = $_REQUEST['birth'];
        }
    }
    mysql_free_result($result);

    if ($naissance == $_REQUEST['birth']) {
        $page->assign('ok', true);
        $url=rand_url_id();
        $stamp=date("Y-m-d H:i:s");
        $sql="INSERT INTO perte_pass (certificat,uid,created) VALUES ('$url',$uid,'$stamp')";

        $globals->db->query($sql);

        // on recupere les emails sans tenir comptes du flags active (ni des autres)
        // sauf qu'il ne faut pas prendre la ligne qui possède l'éventuel appel 
        // au filtre personnel (ligne dont le num = 0)
        $result=$globals->db->query("select email from emails where uid = $uid and num != 0");
        
        $emails = array();
        while(list($email) = mysql_fetch_row($result)) {
            $emails[] = $email;
        }
        mysql_free_result($result);
        $emails = implode(',', $emails);

        require("diogenes.mailer.inc.php");
        $mymail = new DiogenesMailer('Gestion des mots de passe <support+password@polytechnique.org>',
		$emails, 'Ton certificat d\'authentification', false);

        $mymail->setBody("Visite la page suivante qui expire dans six heures :
$baseurl/tmpPWD.php?certificat=$url

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
        $page->assign('error', "Pas de résultat correspondant aux champs entrés dans notre base de données.");
    }
}

$page->run();
?>
