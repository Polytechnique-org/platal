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
new_skinned_page('paiement/retour_paypal.tpl', AUTH_MDP);
require_once("diogenes/diogenes.hermes.inc.php");

/* sort en affichant une erreur */
function erreur($text, $send=true) {
    global $page, $erreur;
    if ($erreur) return;
    $erreur = $text;
    if (!$send) return;

    $mymail = new HermesMailer();
    $mymail->addTo("webmaster@polytechnique.org");
    $mymail->setFrom("webmaster@polytechnique.org");
    $mymail->setSubject("erreur lors d'un télépaiement");
    $mymail->setTxtBody("\n\n".var_export($_REQUEST,true));
    $mymail->send();

    $page->trig($text);
}

/* user id */
$uid = clean_request('uid');
/* reference banque (numero de transaction) */
$no_transaction = clean_request('tx');
/* token a renvoyer pour avoir plus d'information */
$clef = clean_request('sig');
/* code retour */
$status = clean_request('st');
/* raison */
$reason = ($status == 'Pending')?clean_request('pending_reason'):clean_request('reason_code');
/* reference complete de la commande */
$fullref = clean_request('cm');
/* montant de la transaction */
$montant_nb = clean_request('amt');
/* devise */
$montant_dev = clean_request('cc');
$montant = "$montant_nb $montant_dev";

/* on extrait le code de retour */
if ($status != "Completed") {
	if ($status)
		erreur("erreur lors du paiement : $status - $reason");
	else
		erreur("Paiement annulé", false);
}

/* on extrait les informations sur l'utilisateur */
$res = $globals->xdb->query("
    SELECT  a.prenom,a.nom,a.promo,l.alias,FIND_IN_SET(a.flags,'femme')
      FROM  auth_user_md5 AS a
INNER JOIN  aliases       AS l ON (a.user_id=l.id AND type!='homonyme')
     WHERE  a.user_id={?}", $uid);
if (!list($prenom,$nom,$promo,$forlife,$femme) = $res->fetchOneRow()) {
    erreur("uid invalide");
}

/* on extrait la reference de la commande */
if (!ereg('-xorg-([0-9]+)$',$fullref,$matches)) {
    erreur("référence de commande invalide");
}

$ref = $matches[1];
$res = $globals->xdb->query("SELECT mail,text,confirmation FROM paiement.paiements WHERE id={?}", $ref);
if (!list($conf_mail,$conf_title,$conf_text) = $res->fetchOneRow()) {
    erreur("référence de commande inconnue");
}

/* on fait l'insertion en base de donnees */
$globals->xdb->execute("INSERT INTO  paiement.transactions (id,uid,ref,fullref,montant,cle)
                             VALUES  ({?},{?},{?},{?},{?},{?})",
                        $no_transaction, $uid, $ref, $fullref, $montant, $clef);

/* on genere le mail de confirmation */
$conf_text = str_replace("<prenom>",$prenom,$conf_text);
$conf_text = str_replace("<nom>",$nom,$conf_text);
$conf_text = str_replace("<promo>",$promo,$conf_text);
$conf_text = str_replace("<montant>",$montant,$conf_text);
$conf_text = str_replace("<salutation>",$femme ? "Chère" : "Cher",$conf_text);
$conf_text = str_replace("<cher>",$femme ? "Chère" : "Cher",$conf_text);

$mymail = new HermesMailer();
$mymail->setFrom($conf_mail);
$mymail->addTo("\"$prenom $nom\" <$forlife@polytechnique.org>");
$mymail->addCc($conf_mail);
$mymail->setSubject($conf_title);
$mymail->setTxtBody($conf_text);
$mymail->send();

/* on envoie les details de la transaction à telepaiement@ */
$mymail = new HermesMailer();
$mymail->setFrom("webmaster@polytechnique.org");
$mymail->addTo("telepaiement@polytechnique.org");
$mymail->setSubject($conf_title);
$msg = "utilisateur : $prenom $nom ($uid)\n".
       "mail : $forlife@polytechnique.org\n\n".
       "paiement : $conf_title ($conf_mail)\n".
       "reference : $no_transaction\n".
       "montant : $montant\n\n".
       "dump de REQUEST:\n".
       var_export($_REQUEST,true);
$mymail->setTxtBody($msg);
$mymail->send();

$page->assign('texte', $conf_text);
$page->assign('erreur', $erreur);
$page->run();
?>
