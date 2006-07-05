<?
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

require_once('xorg.inc.php');
new_simple_page('register/end.tpl', AUTH_PUBLIC);
require_once('user.func.inc.php');

if (Env::has('hash')) {
    $res = $globals->xdb->query(
            "SELECT  r.uid, r.forlife, r.bestalias, r.mailorg2, r.password, r.email, r.naissance, u.nom, u.prenom, u.promo, u.flags
               FROM  register_pending AS r
         INNER JOIN  auth_user_md5    AS u ON r.uid = u.user_id
              WHERE  hash={?} AND hash!='INSCRIT'", Env::get('hash'));
}

if ( !Env::has('hash') ||
        !list($uid, $forlife, $bestalias, $mailorg2, $password, $email, $naissance, $nom, $prenom, $promo, $femme) = $res->fetchOneRow())
{
    $page->kill("<p>Cette adresse n'existe pas, ou plus, sur le serveur.</p>
                 <p>Causes probables :</p>
                 <ol>
                   <li>Vérifie que tu visites l'adresse du dernier e-mail reçu s'il y en a eu plusieurs.</li>
                   <li>Tu as peut-être mal copié l'adresse reçue par mail, vérifie-la à la main.</li>
                   <li>
                   Tu as peut-être attendu trop longtemps pour confirmer.  Les
                   pré-inscriptions sont annulées tous les 30 jours.
                   </li>
                   <li>
                   Tu es en fait déjà inscrit.
                   </li>
                </ol>");
}



/***********************************************************/
/****************** REALLY CREATE ACCOUNT ******************/
/***********************************************************/

$globals->xdb->execute('UPDATE  auth_user_md5
                           SET  password={?}, perms="user", date=NOW(), naissance={?}, date_ins = NOW()
                         WHERE  user_id={?}', $password, $naissance, $uid);
$globals->xdb->execute('REPLACE INTO auth_user_quick (user_id) VALUES ({?})', $uid);
$globals->xdb->execute('INSERT INTO aliases (id,alias,type) VALUES ({?}, {?}, "a_vie")', $uid, $forlife);
$globals->xdb->execute('INSERT INTO aliases (id,alias,type,flags) VALUES ({?}, {?}, "alias", "bestalias")', $uid, $bestalias);
if ($mailorg2) {
    $globals->xdb->execute('INSERT INTO aliases (id,alias,type) VALUES ({?}, {?}, "alias")', $uid, $mailorg2);
}

require_once('emails.inc.php');
$redirect = new Redirect($uid);
$redirect->add_email($email);

// on cree un objet logger et on log l'inscription
$logger = new DiogenesCoreLogger($uid);
$logger->log('inscription', $email);

$globals->xdb->execute('UPDATE register_pending SET hash="INSCRIT" WHERE uid={?}', $uid);

$globals->hook->subscribe($forlife, $uid, $promo, $password);

require_once('xorg.mailer.inc.php');
$mymail = new XOrgMailer('mails/inscription.reussie.tpl');
$mymail->assign('forlife', $forlife);
$mymail->assign('prenom', $prenom);
$mymail->send();

start_connexion($uid,false);
$_SESSION['auth'] = AUTH_MDP;

/***********************************************************/
/************* envoi d'un mail au démarcheur ***************/
/***********************************************************/
$res = $globals->xdb->iterRow(
        "SELECT  DISTINCT sa.alias, IF(s.nom_usage,s.nom_usage,s.nom) AS nom, s.prenom, s.flags AS femme
           FROM  register_marketing AS m
     INNER JOIN  auth_user_md5      AS s  ON ( m.sender = s.user_id )
     INNER JOIN  aliases            AS sa ON ( sa.id = m.sender AND FIND_IN_SET('bestalias', sa.flags) )
          WHERE  m.uid = {?}", $uid);
$globals->xdb->execute("UPDATE register_mstats SET success=NOW() WHERE uid={?}", $uid);

while (list($salias, $snom, $sprenom, $sfemme) = $res->next()) {
    require_once('diogenes/diogenes.hermes.inc.php');
    $mymail = new HermesMailer();
    $mymail->setSubject("$prenom $nom s'est inscrit à Polytechnique.org !");
    $mymail->setFrom('"Marketing Polytechnique.org" <register@polytechnique.org>');
    $mymail->addTo("\"$sprenom $snom\" <$salias@{$globals->mail->domain}>");
    $msg = ($sfemme?'Cher':'Chère')." $sprenom,\n\n"
         . "Nous t'écrivons pour t'informer que {$prenom} {$nom} (X{$promo}), "
         . "que tu avais incité".($femme?'e':'')." à s'inscrire à Polytechnique.org, "
         . "vient à l'instant de terminer son inscription.\n\n"
         . "Merci de ta participation active à la reconnaissance de ce site !!!\n\n"
         . "Bien cordialement,\n"
         . "L'équipe Polytechnique.org";
    $mymail->setTxtBody(wordwrap($msg, 72));
    $mymail->send();
}

$globals->xdb->execute("DELETE FROM register_marketing WHERE uid = {?}", $uid);

redirect('success.php');
$page->assign('uid', $uid);
$page->run();

?>
