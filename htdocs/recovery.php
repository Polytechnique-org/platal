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
new_skinned_page('recovery.tpl', AUTH_PUBLIC);

if (Env::has('login') and Env::has('birth'))  {
    if (!ereg('[0-3][0-9][0-1][0-9][1][9]([0-9]{2})', Env::get('birth'))) {
        $page->trig_run('Date de naissance incorrecte ou incohérente');
    }
    $birth   = sprintf('%s-%s-%s', substr(Env::get('birth'),4,4), substr(Env::get('birth'),2,2), substr(Env::get('birth'),0,2));

    $mailorg = strtok(Env::get('login'), '@');

    // paragraphe rajouté : si la date de naissance dans la base n'existe pas, on l'update
    // avec celle fournie ici en espérant que c'est la bonne

    $res = $globals->xdb->query(
            "SELECT  user_id, naissance
               FROM  auth_user_md5 AS u
         INNER JOIN  aliases       AS a ON (u.user_id=a.id AND type!='homonyme')
              WHERE  a.alias={?} AND u.perms IN ('admin','user') AND u.deces=0", $mailorg);
    list($uid, $naissance) = $res->fetchOneRow();

    if ($naissance == $birth) {
        $page->assign('ok', true);

        $url   = rand_url_id(); 
        $globals->xdb->execute('INSERT INTO perte_pass (certificat,uid,created) VALUES ({?},{?},NOW())', $url, $uid);
        $res   = $globals->xdb->query('SELECT email FROM emails WHERE uid = {?} AND NOT FIND_IN_SET("filter", flags)', $uid);
        $mails = implode(', ', $res->fetchColumn());
        
	require_once("diogenes/diogenes.hermes.inc.php");
	$mymail = new HermesMailer();
	$mymail->setFrom('"Gestion des mots de passe" <support+password@polytechnique.org>');
	$mymail->addTo($mails);
	$mymail->setSubject('Ton certificat d\'authentification');
        $mymail->setTxtBody("Visite la page suivante qui expire dans six heures :
{$globals->baseurl}/tmpPWD.php?certificat=$url

Si en cliquant dessus tu n'y arrives pas, copie intégralement l'adresse dans la barre de ton navigateur.

--
Polytechnique.org
\"Le portail des élèves & anciens élèves de l'Ecole polytechnique\"".(Post::get('email') ? "

Adresse de secours : \n    ".Post::get('email') : "")."

Mail envoyé à ".Env::get('login'));
        $mymail->send();

        // on cree un objet logger et on log l'evenement
	$logger = $_SESSION['log'] = new DiogenesCoreLogger($uid);
	$logger->log('recovery', $emails);
    } else {
        $page->trig('Pas de résultat correspondant aux champs entrés dans notre base de données.');
    }
}

$page->run();
?>
