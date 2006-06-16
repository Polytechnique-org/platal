<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

ini_set('include_path', '../include:/usr/share/php');
 
require_once('../include/xorg.inc.php');
require_once('../include/emails.inc.php');

$opts = getopt('i:');
if (($opts['i'] && $opts['i'] == '-') || empty($opts['i'])) {
    $file = 'php://stdin';
} else {
    $file = $opts['i'];
}

$emails = explode("\n", file_get_contents($file));
foreach ($emails as $_email) {
    $email = valide_email($_email);
    if (empty($email) || $email=='@') {
        continue;
    }

    $sel = $globals->xdb->query(
            "SELECT  e1.uid, e1.panne != 0 AS panne, count(e2.uid) AS nb_mails, u.nom, u.prenom, u.promo, a.alias
               FROM  emails        AS e1
          LEFT JOIN  emails        AS e2 ON (e1.uid = e2.uid AND FIND_IN_SET('active', e2.flags) AND e1.email != e2.email)
         INNER JOIN  auth_user_md5 AS u  ON (e1.uid = u.user_id)
         INNER JOIN  aliases       AS a  ON (u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
              WHERE  e1.email = {?}
           GROUP BY  e1.uid", $email);
    if ($x = $sel->fetchOneAssoc()) {
        if (!$x['panne']) {
            $globals->xdb->execute("UPDATE emails SET panne=NOW() WHERE email = {?}", $email);
        }

        if (empty($x['nb_mails'])) {
            echo "$email : seule adresse active de {$x['prenom']} {$x['nom']}\n";
        } else {
            $message = "  Bonjour !
	
  Nous t'écrivons car lors de l'envoi de la lettre d'information mensuelle
de Polytechnique.org à ton adresse polytechnicienne :

    {$x['alias']}@polytechnique.org,

l'adresse {$email}, sur laquelle tu rediriges ton courrier, ne
fonctionnait pas.

  Estimant que cette information serait susceptible de t'intéresser, nous
avons préféré t'en informer. Il n'est pas impossible qu'il ne s'agisse que
d'une panne temporaire.  Si tu souhaites changer la liste des adresses sur
lesquelles tu reçois le courrier qui t'es envoyé à ton adresse
polytechnicienne, il te suffit de te rendre sur la page :

    {$globals->baseurl}/emails/redirect.php


  A bientôt sur Polytechnique.org !
  L'équipe d'administration <support@polytechnique.org>
  
---------------------------------------------------------------------------

  PS : si jamais tu ne disposes plus du mot de passe te permettant
d'accéder au site, rends toi sur la page

    {$globals->baseurl}/recovery.php

elle te permettra de créer un nouveau mot de passe après avoir rentré ton
login ({$x['alias']}) et ta date de naissance !";

            require_once("diogenes/diogenes.hermes.inc.php");
            $mail = new HermesMailer();
            $mail->setFrom('"Polytechnique.org" <support@polytechnique.org>');
            $mail->addTo("\"{$x['prenom']} {$x['nom']}\" <{$x['alias']}@polytechnique.org>");
            $mail->setSubject("Une de tes adresse de redirection Polytechnique.org ne marche plus !!");
            $mail->setTxtBody($message);
            $mail->send();
            echo "$email : mail envoyé\n";
        }
    } else {
        echo "$email : cette addresse n'est pas dans la base\n";
    } 
}

?>
