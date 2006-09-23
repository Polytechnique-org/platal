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

ini_set('include_path', '.:../include:/usr/share/php');

require_once('connect.db.inc.php');
require_once('xorg.inc.php');
require_once('emails.inc.php');

$opts = getopt('i:o:');
if (($opts['i'] && $opts['i'] == '-') || empty($opts['i'])) {
    $file = 'php://stdin';
} else {
    $file = $opts['i'];
}
if (($opts['o'] && $opts['o'] == '-') || empty($opts['o'])) {
    $output = 'php://stdout';
} else {
    $output = $opts['o'];
}

$emails = explode("\n", file_get_contents($file));
$list   = array();

foreach ($emails as $_email) {
    $email = valide_email($_email);
    if (empty($email) || $email=='@') {
        continue;
    }

    $sel = XDB::query(
            "SELECT  e1.uid, e1.panne != 0 AS panne, count(e2.uid) AS nb_mails, u.nom, u.prenom, u.promo, a.alias
               FROM  emails        AS e1
          LEFT JOIN  emails        AS e2 ON (e1.uid = e2.uid AND FIND_IN_SET('active', e2.flags) AND e1.email != e2.email)
         INNER JOIN  auth_user_md5 AS u  ON (e1.uid = u.user_id)
         INNER JOIN  aliases       AS a  ON (u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
              WHERE  e1.email = {?}
           GROUP BY  e1.uid", $email);
    if ($x = $sel->fetchOneAssoc()) {
        if (!$x['panne']) {
            XDB::execute("UPDATE emails
                             SET panne=NOW(), last=NOW()
                                 panne_level = 1
                           WHERE email = {?}",
                          $email);
        } else {
            XDB::execute("UPDATE emails
                             SET last = CURDATE(),
                                 panne_level = panne_level + 1
                           WHERE email = {?}
                                 AND DATE_ADD(last, INTERVAL 15 DAY) < CURDATE()", $email);
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

    https://www.polytechnique.org/emails/redirect


  A bientôt sur Polytechnique.org !
  L'équipe d'administration <support@polytechnique.org>
  
---------------------------------------------------------------------------

  PS : si jamais tu ne disposes plus du mot de passe te permettant
d'accéder au site, rends toi sur la page

    https://www.polytechnique.org/recovery

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

        if (!isset($list[$x['alias']])) {
            $list[$x['alias']] = array($email);
        } else {
            $list[$x['alias']][] = $email;
        }
    } else {
        echo "$email : cette addresse n'est pas dans la base\n";
    }
}

XDB::execute("UPDATE emails
                 SET panne_level = panne_level - 1
               WHERE flags = 'active' AND panne_level > 1
                     AND last != CURDATE()");

$csv = "nom;prenom;promo;alias;bounce;nbmails\n";
foreach ($list as $alias=>$mails) {
    $sel = Xdb::query(
            "SELECT u.user_id, count(e.email) AS nb_mails, u.nom, u.prenom, u.promo
               FROM aliases       AS a
         INNER JOIN auth_user_md5 AS u ON a.id = u.user_id
         LEFT JOIN emails         AS e ON (e.uid = u.user_id AND FIND_IN_SET('active', e.flags) AND e.panne = 0)
              WHERE a.alias = {?}
           GROUP BY u.user_id", $alias);
    if ($x = $sel->fetchOneAssoc()) {
        $csv .= $x['nom'].';'.$x['prenom'].';' .$x['promo'].';'.$alias.';' . join(',', $mails) . ';'.$x['nb_mails']."\n";
    }
}

$fo = fopen($output, 'w+');
fwrite($fo, $csv);
fclose($fo);

?>
