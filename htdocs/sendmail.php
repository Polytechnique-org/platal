<?php
require("auto.prepend.inc.php");
new_skinned_page('sendmail.tpl',AUTH_MDP,true);

// action si on recoit un formulaire
if (isset($_REQUEST['submit']) and $_REQUEST['submit'] == 'Envoyer'
    and isset($_REQUEST['to']) and isset($_REQUEST['sujet']) 
    and isset($_REQUEST['contenu']) and isset($_REQUEST['cc'])
    and isset($_REQUEST['bcc'])) {
        $autre_to = (isset($_REQUEST['contacts']) ? join(', ',$_REQUEST['contacts']) : '');

    if ($_REQUEST['to'] == '' and $_REQUEST['cc'] == '' and $autre_to == '') {
        echo "<p class=\"erreur\">\n  Indique au moins un destinataire.\n</p>\n";
    } else {
        if (get_magic_quotes_gpc()) {
            $_REQUEST['contenu'] = str_replace('', '', stripslashes($_REQUEST['contenu']));
            $_REQUEST['to'] = stripslashes($_REQUEST['to']);
            $_REQUEST['sujet'] = stripslashes($_REQUEST['sujet']);
            $_REQUEST['from'] = stripslashes($_REQUEST['from']);
            $_REQUEST['cc'] = stripslashes($_REQUEST['cc']);
            $_REQUEST['bcc'] = stripslashes($_REQUEST['bcc']);
            $autre_to = stripslashes($autre_to);
        }
        require("mailer.inc.php");
        $FROM = "From: {$_REQUEST['from']}";
        //$_REQUEST['contenu'] = chunk_split($_REQUEST['contenu'], 76, "\n"); // pas bon, ne tient pas compte des mots
            $dest = $_REQUEST['to'].', '.$autre_to;
        $mymail = new mailer($_SESSION['username'], $dest, $_REQUEST['sujet'], false, $_REQUEST['cc'], $_REQUEST['bcc']);
        $mymail->addHeader($FROM);
        $mymail->setBody(wordwrap($_REQUEST['contenu'],72,"\n"));
        if ($mymail->send()) {
            $page->assign('error',"Ton mail a bien été envoyé.");
            $_REQUEST = array();
        } else {
            $page->assign('error',"Erreur lors de l'envoi du courriel, réessaye.\n");
        }
    } // ! if ($_REQUEST['to'] == '' and $_REQUEST['cc'] == '')
}

$sql = "SELECT u.prenom, u.nom, u.promo, u.username
        FROM auth_user_md5 as u, contacts as c
        WHERE u.user_id = c.contact AND c.uid = {$_SESSION['uid']}
        ORDER BY u.nom, u.prenom";
$page->mysql_assoc($sql, 'contacts','nb_contacts');

$page->display();
?>
