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
        $Id: moderate.php,v 1.15 2004-10-30 15:45:40 x2000habouzit Exp $
 ***************************************************************************/

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = $_REQUEST['liste'];

if(preg_match('!(?:[a-z0-9]+\.)?polytechnique\.org-(.*)!', $liste,$matches)) {
    header('Location: ?liste='.$matches[1]);
}

require("auto.prepend.inc.php");
new_skinned_page('listes/moderate.tpl', AUTH_MDP, true);
include('xml-rpc-client.inc.php');

$client = new xmlrpc_client("http://{$_SESSION['uid']}:{$_SESSION['password']}@localhost:4949");

if(isset($_REQUEST['sadd'])) {
    $client->handle_request('polytechnique.org', $liste,$_REQUEST['sadd'],4,'');
    /** 4 is the magic for SUBSCRIBE see Defaults.py **/
    header("Location: moderate.php?liste=$liste");
}

if(isset($_POST['sdel'])) {
    $client->handle_request('polytechnique.org', $liste,$_POST['sdel'],2,stripslashes($_POST['reason']));
    /** 2 is the magic for REJECT see Defaults.py **/
}

if(isset($_REQUEST['mid'])) {
    $mid = $_REQUEST['mid'];
    if(isset($_REQUEST['mok'])) {
	unset($_GET['mid']);
	$client->handle_request('polytechnique.org', $liste,$mid,1,''); /** 1 = APPROVE **/
    } elseif(isset($_POST['mno'])) {
	$reason = stripslashes($_POST['reason']);
	$mail = $client->get_pending_mail('polytechnique.org', $liste, $mid);
	if($client->handle_request('polytechnique.org', $liste,$mid,2,$reason)) { /** 2 = REJECT **/
	    include_once('diogenes.mailer.inc.php');
	    $mailer = new DiogenesMailer("$liste-bounces@polytechnique.org",
		"$liste-owner@polytechnique.org", "Message refusé");
	    $texte = "le message suivant :\n\n"
		    ."    Auteur: {$mail['sender']}\n"
		    ."    Sujet : « {$mail['subj']} »\n"
		    ."    Date  : ".strftime("le %d %b %Y à %H:%M:%S", (int)$mail['stamp'])."\n\n"
		    ."a été refusé par {$_SESSION['prenom']} {$_SESSION['nom']} avec la raison :\n"
		    ."« $reason »";
	    $mailer->setBody(wordwrap($texte,72));
	    $mailer->send();
	}
    } elseif(isset($_REQUEST['mdel'])) {
	unset($_GET['mid']);
	$mail = $client->get_pending_mail('polytechnique.org', $liste, $mid);
	if($client->handle_request('polytechnique.org', $liste,$mid,3,'')) { /** 3 = DISCARD **/
	    include_once('diogenes.mailer.inc.php');
	    $mailer = new DiogenesMailer("$liste-bounces@polytechnique.org",
		"$liste-owner@polytechnique.org", "Message supprimé");
	    $texte = "le message suivant :\n\n"
		    ."    Auteur: {$mail['sender']}\n"
		    ."    Sujet : « {$mail['subj']} »\n"
		    ."    Date  : ".strftime("le %d %b %Y à %H:%M:%S",(int)$mail['stamp'])."\n\n"
	            ."a été supprimé par {$_SESSION['prenom']} {$_SESSION['nom']}.\n\n"
		    ."Rappel: il ne faut utiliser cette opération que dans le cas de spams ou de virus !\n";
	    $mailer->setBody(wordwrap($texte,72));
	    $mailer->send();
	}
    }
}

if(isset($_REQUEST['sid'])) {

    $sid = $_REQUEST['sid'];
    if(list($subs,$mails) = $client->get_pending_ops('polytechnique.org', $liste)) {
	foreach($subs as $user) {
	    if($user['id'] == $sid) $u = $user;
	}
	if($u) {
	    $page->changeTpl('listes/moderate_sub.tpl');
	    $page->assign('del_user',$u);
	} else {
	    $page->assign_by_ref('subs', $subs);
	    $page->assign_by_ref('mails', $mails);
	}
    } else
	$page->assign('no_list', true);

} elseif(isset($_GET['mid'])) {

    $mid = $_REQUEST['mid'];
    $mail = $client->get_pending_mail('polytechnique.org', $liste,$mid);
    if(is_array($mail)) {
	    $fname = '/etc/mailman/fr/refuse.txt';
	    $h = fopen($fname,'r');
	    $msg = fread($h, filesize($fname));
	    fclose($h);
	    $msg = str_replace("%(adminaddr)s","$liste-owner@polytechnique.org", $msg);
	    $msg = str_replace("%(request)s","<< SUJET DU MAIL >>", $msg);
	    $msg = str_replace("%(reason)s","<< TON EXPLICATION >>", $msg);
	    $msg = str_replace("%(listname)s","$liste", $msg);
	    $page->assign('msg', $msg); 

	$page->changeTpl('listes/moderate_mail.tpl');
        $page->assign_by_ref('mail', $mail);
    } else {
	if(list($subs,$mails) = $client->get_pending_ops('polytechnique.org', $liste)) {
	    $page->assign_by_ref('subs', $subs);
	    $page->assign_by_ref('mails', $mails);
	} else
	    $page->assign('no_list', true);
    }

} elseif(list($subs,$mails) = $client->get_pending_ops('polytechnique.org', $liste)) {

    $page->assign_by_ref('subs', $subs);
    $page->assign_by_ref('mails', $mails);

} else
    $page->assign('no_list', true);

$page->register_modifier('qpd','quoted_printable_decode');
$page->run();
?>
