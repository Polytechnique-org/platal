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
 ***************************************************************************
        $Id: sendmail.php,v 1.5 2004-08-31 10:03:28 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('sendmail.tpl',AUTH_MDP,true);


// action si on recoit un formulaire
if (isset($_REQUEST['submit']) and $_REQUEST['submit'] == 'Envoyer'
    and isset($_REQUEST['to']) and isset($_REQUEST['sujet']) 
    and isset($_REQUEST['contenu']) and isset($_REQUEST['cc'])
    and isset($_REQUEST['bcc'])) {
        $autre_to = (isset($_REQUEST['contacts']) ? join(', ',$_REQUEST['contacts']) : '');

    if (get_magic_quotes_gpc()) {
	$_REQUEST['contenu'] = str_replace('', '', stripslashes($_REQUEST['contenu']));
	$_REQUEST['to'] = stripslashes($_REQUEST['to']);
	$_REQUEST['sujet'] = stripslashes($_REQUEST['sujet']);
	$_REQUEST['from'] = stripslashes($_REQUEST['from']);
	$_REQUEST['cc'] = stripslashes($_REQUEST['cc']);
	$_REQUEST['bcc'] = stripslashes($_REQUEST['bcc']);
	$autre_to = stripslashes($autre_to);
    }

    if ($_REQUEST['to'] == '' and $_REQUEST['cc'] == '' and $autre_to == '') {
        $page->assign('error',"Indique au moins un destinataire.");
    } else {
        require("diogenes.mailer.inc.php");
        $FROM = "From: {$_REQUEST['from']}";
        //$_REQUEST['contenu'] = chunk_split($_REQUEST['contenu'], 76, "\n"); // pas bon, ne tient pas compte des mots
            $dest = $_REQUEST['to'].', '.$autre_to;
        $mymail = new DiogenesMailer($_SESSION['username'], $dest, $_REQUEST['sujet'], false, $_REQUEST['cc'], $_REQUEST['bcc']);
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
$page->mysql_assign($sql, 'contacts','nb_contacts');

$page->run();
?>
