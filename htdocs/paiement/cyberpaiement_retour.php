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
        $Id: cyberpaiement_retour.php,v 1.4 2004-09-02 21:22:19 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('paiment/index.tpl', AUTH_MDP);

require("diogenes.mailer.inc.php");

/* en attendant PHP 4.2, on definit var_export */
function var_exp(&$var) {
  ob_start();
  var_dump($var);
  $ret = ob_get_contents();
  ob_end_clean();
  return $ret;
}

/* sort en affichant une erreur */
function erreur($text) {
  $text .= "\n\n".var_exp($_REQUEST);
  $mymail = new DiogenesMailer("webmaster","web","erreur lors d'un télépaiement",false);
  $mymail->setBody($text);
  $mymail->send();
  exit;
}

/* calcule la clé de Luhn d'un nombre */
function luhn($nombre) {
  $sum = 0;
  for ($i = 0; $i < strlen($nombre); $i++) {
    $digit = substr($nombre,$i,1);
    if ($i % 2) {
      if ($digit*2>9) 
	$sum += 2*$digit - 9;
      else
        $sum += 2*$digit;
    } else {
      $sum += $digit;
    }
  }
  return $sum % 10;
}

/* calcule la clé d'acceptation a partir de 5 champs */
function cle_accept($d1,$d2,$d3,$d4,$d5)
{
  $m1 = luhn(strrev($d1.$d5));
  $m2 = luhn(strrev($d2.$d5));
  $m3 = luhn(strrev($d3.$d5));
  $m4 = luhn(strrev($d4.$d5));
  $n = $m1 + $m2 + $m3 + $m4;
  $alpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  return substr($alpha,$n-1,1).$m1.$m2.$m3.$m4;
}

/* user id */
$uid = clean_request('uid');
/* reference banque (numero de transaction) */
$champ901 = clean_request('CHAMP901');
/* cle d'acceptation */
$champ905 = clean_request('CHAMP905');
/* code retour */
$champ906 = clean_request('CHAMP906');
/* email renvoye par la banque */
$champ104 = clean_request('CHAMP104');
/* reference complete de la commande */
$champ200 = clean_request('CHAMP200');
/* montant de la transaction */
$champ201 = clean_request('CHAMP201');
/* devise */
$champ202 = clean_request('CHAMP202');
$montant = "$champ201 $champ202";

/* on extrait les informations sur l'utilisateur */
$res = $globals->db->query("
    SELECT  a.prenom,a.nom,a.promo,l.alias,FIND_IN_SET(i.flags,'femme')
      FROM  auth_user_md5  AS a
INNER JOIN  aliases        AS l ON a.user_id=l.id
INNER JOIN  identification AS i ON a.matricule=i.matricule
     WHERE  a.user_id='$uid'");
if (!list($prenom,$nom,$promo,$forlife,$femme) = mysql_fetch_row($res))
  erreur("uid invalide");

/* on extrait la reference de la commande */
if (!ereg('-xorg-([0-9]+)$',$champ200,$matches))
  erreur("référence de commande invalide");
$ref = $matches[1];
echo $ref;
$res = $globals->db->query("select mail,text,confirmation from paiement.paiements where id='$ref'");
if (!list($conf_mail,$conf_title,$conf_text) = mysql_fetch_row($res))
  erreur("référence de commande inconnue");

/* on extrait le code de retour */
if ($champ906 != "0000") {
  $res = $globals->db->query("select rcb.text,c.id,c.text from paiement.codeRCB as rcb left join paiement.codeC as c on rcb.codeC=c.id where rcb.id='$champ906'");
  if (list($rcb_text,$c_id,$c_text) = mysql_fetch_row($res)) 
    erreur("erreur lors du paiement : $c_text ($c_id)");
  else
    erreur("erreur inconnue lors du paiement");
}

/* on fait l'insertion en base de donnees */
$globals->db->query("insert into paiement.transactions set id='$champ901',uid='$uid',ref='$ref',fullref='$champ200',montant='$montant',cle='$champ905'");

/* on genere le mail de confirmation */
$conf_text = str_replace("<prenom>",$prenom,$conf_text);
$conf_text = str_replace("<nom>",$nom,$conf_text);
$conf_text = str_replace("<promo>",$promo,$conf_text);
$conf_text = str_replace("<montant>",$montant,$conf_text);
$conf_text = str_replace("<salutation>",$femme ? "Chère" : "Cher",$conf_text);

$mymail = new DiogenesMailer($conf_mail,$forlife,$conf_title,false,$conf_mail);
$mymail->setBody($conf_text);
$mymail->send();

/* on envoie les details de la transaction à telepaiement@ */
$mymail = new DiogenesMailer("webmaster","telepaiement",$conf_title,false);
$msg = "utilisateur : $prenom $nom ($uid)\n".
       "mail : $forlife@polytechnique.org\n\n".
       "paiement : $conf_title ($conf_mail)\n".
       "reference : $champ200\n".
       "montant : $montant\n\n".
       "dump de REQUEST:\n".
       var_exp($_REQUEST);
$mymail->setBody($msg);
$mymail->send();

?>
