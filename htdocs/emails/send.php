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
new_skinned_page('emails/send.tpl',AUTH_MDP);

// action si on recoit un formulaire
if (Env::get('submit') == 'Envoyer')
{
    $to2  = join(', ', Env::getMixed('contacts', Array()));
    $txt  = str_replace('^M', '', Env::get('contenu'));
    $to   = Env::get('to');
    $subj = Env::get('sujet');
    $from = Env::get('from');
    $cc   = Env::get('cc');
    $bcc  = Env::get('bcc');

    if (empty($to) && empty($cc) && empty($to2)) {
        $page->trig("Indique au moins un destinataire.");
    } else {
        require_once("diogenes/diogenes.hermes.inc.php");

        $mymail = new HermesMailer();
	$mymail->setFrom($from);
	$mymail->setSubject($subj);
	if (!empty($to))  { $mymail->addTo($to); }
	if (!empty($cc))  { $mymail->addCc($cc); }
	if (!empty($bcc)) { $mymail->addBcc($bcc); }
	if (!empty($to2)) { $mymail->addTo($to2); }
        $mymail->setTxtBody(wordwrap($txt,72,"\n"));
        if ($mymail->send()) {
            $page->trig("Ton mail a bien été envoyé.");
            $_REQUEST = array('bcc' => Session::get('bestalias').'@'.$globals->mail->domain);
        } else {
            $page->trig("Erreur lors de l'envoi du courriel, réessaye.");
        }
    }
} else {
    $_REQUEST['bcc'] = Session::get('bestalias').'@'.$globals->mail->domain;
}

$res = $globals->xdb->query(
        "SELECT  u.prenom, u.nom, u.promo, a.alias as forlife
           FROM  auth_user_md5 AS u
     INNER JOIN  contacts      AS c ON (u.user_id = c.contact)
     INNER JOIN  aliases       AS a ON (u.user_id=a.id AND FIND_IN_SET('bestalias',a.flags))
          WHERE  c.uid = {?}
         ORDER BY u.nom, u.prenom", Session::getInt('uid'));
$page->assign('contacts', $res->fetchAllAssoc());

$page->run();
?>
