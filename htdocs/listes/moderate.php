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

if (!Env::has('liste')) header('Location: index.php');
$liste = strtolower(Env::get('liste'));

if (preg_match("!(?:[a-z0-9]+\\.)?{$globals->mail->domain}-(.*)!", $liste, $matches)) {
    header('Location: ?liste='.$matches[1]);
}

require_once("xorg.inc.php");
new_skinned_page('listes/moderate.tpl', AUTH_MDP);
require_once('lists.inc.php');

$client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'));
$page->register_modifier('qpd','quoted_printable_decode');

if(Env::has('sadd')) {
    $client->handle_request($liste,Env::get('sadd'),4,''); /* 4 = SUBSCRIBE */
    header("Location: moderate.php?liste=$liste");
}

if(Post::has('sdel')) {
    $client->handle_request($liste,Post::get('sdel'),2,stripslashes(Post::get('reason'))); /* 2 = REJECT */
}

if(Env::has('mid')) {
    $mid    = Env::get('mid');
    $mail   = $client->get_pending_mail($liste, $mid);
    $reason = '';

    $prenom = Session::get('prenom');
    $nom    = Session::get('nom');

    
    if (Env::has('mok')) {
        $action  = 1; /** 2 = ACCEPT **/
        $subject = "Message accepté";
        $append .= "a été accepté par $prenom $nom.\n";
    } elseif (Env::has('mno')) {
        $action  = 2; /** 2 = REJECT **/
        $subject = "Message refusé";
	$reason  = stripslashes(Post::get('reason'));
        $append  = "a été refusé par $prenom $nom avec la raison :\n\n"
                .  $reason;
    } elseif (Env:has('mdel')) {
        $action  = 3; /** 3 = DISCARD **/
        $sbuject = "Message supprimé";
        $append  = "a été supprimé par $prenom $nom.\n\n"
                .  "Rappel: il ne faut utiliser cette opération que dans le cas de spams ou de virus !\n";
    }
   
    if (isset($action) && $client->handle_request($liste,$mid,$action,$reason) {
        $texte = "le message suivant :\n\n"
                ."    Auteur: {$mail['sender']}\n"
                ."    Sujet : « {$mail['subj']} »\n"
                ."    Date  : ".strftime("le %d %b %Y à %H:%M:%S", (int)$mail['stamp'])."\n\n"
                .$append;
        require_once('diogenes.hernes.inc.php');
        $mailer = new HermesMailer();
        $mailer->addTo("$liste-owner@{$globals->mail->domain}");
        $mailer->setFrom("$liste-bounces@{$globals->mail->domain}");
        $mailer->addHeader('Reply-To', "$liste-owner@{$globals->mail->domain}");
        $mailer->setSubject($subject);
        $mailer->setTxtBody(wordwrap($texte,72));
        $mailer->send();
        Get::kill('mid');
    }

    if(Get::has('mid') && is_array($mail)) {
	$msg = file_get_contents('/etc/mailman/fr/refuse.txt');
	$msg = str_replace("%(adminaddr)s","$liste-owner@{$globals->mail->domain}", $msg);
	$msg = str_replace("%(request)s","<< SUJET DU MAIL >>", $msg);
	$msg = str_replace("%(reason)s","<< TON EXPLICATION >>", $msg);
	$msg = str_replace("%(listname)s","$liste", $msg);
	$page->assign('msg', $msg); 

	$page->changeTpl('listes/moderate_mail.tpl');
        $page->assign_by_ref('mail', $mail);
        $page->run();
    }

} elseif (Env::has('sid')) {

    if(list($subs,$mails) = $client->get_pending_ops($liste)) {
	foreach($subs as $user) {
	    if ($user['id'] == Env::get('sid')) {
                $page->changeTpl('listes/moderate_sub.tpl');
                $page->assign('del_user',$user);
                $page->run();
            }
	}
    }

}

if(list($subs,$mails) = $client->get_pending_ops($liste)) {
    $page->assign_by_ref('subs', $subs);
    $page->assign_by_ref('mails', $mails);
} else {
    $page->kill("La liste n'existe pas ou tu n'as pas le droit de la modérer");
}

$page->run();
?>
