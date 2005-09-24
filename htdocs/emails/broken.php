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

require_once('xorg.inc.php');
new_skinned_page('emails/broken.tpl',AUTH_COOKIE);
require_once('emails.inc.php');

if (Get::has('email') && Get::has('action')) {
    $email = valide_email(Get::get('email'));
    // vérifications d'usage
    $sel = $globals->xdb->query(
            "SELECT  e.uid, a.alias
               FROM  emails        AS e
         INNER JOIN  auth_user_md5 AS u ON e.uid = u.user_id
         INNER JOIN  aliases       AS a ON (e.uid = a.id AND type!='homonyme' AND FIND_IN_SET('bestalias',a.flags))
              WHERE  e.email={?}", $email);

    if (list($uid, $dest) = $sel->fetchOneRow()) {
	// envoi du mail
	$message = "Bonjour !
	
Ce mail a été généré automatiquement par le service de patte cassée de
Polytechnique.org car un autre utilisateur, ".Session::get('prenom').' '.Session::get('nom').",
nous a signalé qu'en t'envoyant un mail, il avait reçu un message d'erreur
indiquant que ton adresse de redirection $email
ne fonctionnait plus !

Nous te suggérons de vérifier cette adresse, et le cas échéant de mettre
à jour sur le site <{$globals->baseurl}/emails.php> tes adresses
de redirection...

Pour plus de rensignements sur le service de patte cassée, n'hésites pas à
consulter la page <{$globals->baseurl}/emails/broken.php>.


A bientôt sur Polytechnique.org !
L'équipe d'administration <support@polytechnique.org>";

	require_once("diogenes/diogenes.hermes.inc.php");
	$mail = new HermesMailer();
	$mail->setFrom('"Polytechnique.org" <support@polytechnique.org>');
	$mail->addTo("$dest@polytechnique.org");
	$mail->setSubject("Une de tes adresse de redirection Polytechnique.org ne marche plus !!");
	$mail->setTxtBody($message);
	$mail->send();
        $page->trig("Mail envoyé ! :o)");
    }
} elseif (Post::has('email')) {
    $email = valide_email(Post::get('email'));

    list(,$fqdn) = split('@', $email);
    $fqdn = strtolower($fqdn);
    if ($fqdn == 'polytechnique.org' || $fqdn == 'melix.org' || $fqdn == 'm4x.org' || $fqdn == 'melix.net') {
        $page->assign('neuneu', true);
    } else {
        $page->assign('email',$email);
        $sel = $globals->xdb->query(
                "SELECT  e1.uid, e1.panne != 0 AS panne, count(e2.uid) AS nb_mails, u.nom, u.prenom, u.promo
                   FROM  emails as e1
              LEFT JOIN  emails as e2 ON(e1.uid = e2.uid AND FIND_IN_SET('active', e2.flags) AND e1.email != e2.email)
             INNER JOIN  auth_user_md5 as u ON(e1.uid = u.user_id)
                  WHERE  e1.email = {?}
               GROUP BY  e1.uid", $email);
        if ($x = $sel->fetchOneAssoc()) {
            // on écrit dans la base que l'adresse est cassée
            if (!$x['panne']) {
                $globals->xdb->execute("UPDATE emails SET panne=NOW() WHERE email = {?}", $email);
            }
            $page->assign_by_ref('x', $x);
        }
    }
}

$page->run();
?>
