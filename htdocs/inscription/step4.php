<?
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
new_skinned_page('inscription/step4.tpl', AUTH_PUBLIC);

require_once("user.func.inc.php");
require_once('xorg.mailer.inc.php');

if (Env::has('ref')) {
    $globals->xdb->execute(
            "SELECT  username,homonyme,loginbis,matricule,promo,password,
                     nom,prenom,nationalite,email,naissance,date,
                     appli_id1,appli_type1,appli_id2,appli_type2
               FROM  en_cours WHERE ins_id={?}", Env::get('ref'));
}

// vérifions que la référence de l'utilisateur est une référence existante dans "en_cours"
if ( !Env::has('ref') ||
        !list( $forlife, $homonyme, $alias, $matricule, $promo, $password, $nom, $prenom,$nationalite, 
        $email, $naissance,$date,$appli_id1,$appli_type1,$appli_id2,$appli_type2) = mysql_fetch_row($res))
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
                </ol>");
}

$page->assign('forlife',$forlife);

// vérifions qu'il n'y a pas déjà une inscription dans le passé
// ce qui est courant car les double-clic...
$res = $globals->xdb->query('SELECT alias FROM aliases WHERE alias={?}', $forlife);
if ($res->numRows()) {
    $page->kill("Tu es déjà inscrit à polytechnique.org.  Tu as sûrement cliqué deux fois sur le même lien de
            référence ou effectué un double clic.  Consultes tes mails pour obtenir ton identifiant et ton
            mot de passe.");
}

$globals->xdb->execute('UPDATE  auth_user_md5
                           SET  password={?}, nationalite={?}, perms='user',
                                date={?}, naissance={?}, date_ins = NULL
                         WHERE  matricule={?}', $password, $nationalite, $date, $naissance, $matricule);
$globals->xdb->execute('REPLACE INTO auth_user_quick (user_id) SELECT user_id FROM auth_user_md5 WHERE matricule={?}', $matricule);

// on vérifie qu'il n'y a pas eu d'erreur
if ($globals->db->err()) {
    $page->kill($globals->db->error() .  '<br />
            Une erreur s\'est produite lors de la mise en place définitive de ton inscription,
            essaie à nouveau, si cela ne fonctionne toujours pas, envoie un mail à
            <a href="mailto:webmestre@polytechnique.org">webmaster@polytechnique.org</a>');
}
// ok, pas d'erreur, on continue
$res = $globals->xdb->query('SELECT user_id FROM auth_user_md5 WHERE matricule={?}', $matricule);
$uid = $res->fetchOneCell();
if (empty($uid)) {
    $page->kill($globals->db->error() .  '<br />
            Une erreur s\'est produite lors de la mise en place définitive de ton inscription,
            essaie à nouveau, si cela ne fonctionne toujours pas, envoie un mail à
            <a href="mailto:webmestre@polytechnique.org">webmaster@polytechnique.org</a>');
}

$globals->xdb->execute('INSERT INTO aliases (id,alias,type) VALUES ({?}, {?}, "a_vie")', $uid, $forlife);
if($alias) {
    // Les alias supplémentaires sont prenom.nom.NN et, si pas d'homonymie, prenom.nom
    $p2 = sprintf("%02u",($promo%100));
    if(!$homonyme) {
        $globals->xdb->execute('INSERT INTO aliases (id,alias,type) VALUES ({?}, {?}, "alias")', $uid, $alias);
    }
    $globals->xdb->execute('INSERT INTO aliases (id,alias,type) VALUES ({?}, {?}, "alias")', $uid, "$alias.$p2");
}

// on cree un objet logger et on log l'inscription
$logger = new DiogenesCoreLogger($uid);
$logger->log("inscription",$email);

/****************** insertion de l'email dans la table emails + bogofilter ***/
require_once("emails.inc.php");
$redirect = new Redirect($uid);
$redirect->add_email($email);
fix_bestalias($uid);
/****************** ajout des formations ****************/
if (($appli_id1>0)&&($appli_type1)) {
    $globals->xdb->execute('INSERT INTO applis_ins SET uid={?},aid={?},type={?},ordre=0', $uid, $appli_id1, $appli_type1);
}
if (($appli_id2>0)&&($appli_type2)) {
    $globals->xdb->execute('INSERT INTO applis_ins SET uid={?},aid={?},type={?},ordre=1', $uid, $appli_id2, $appli_type2);
}
/****************** envoi d'un mail au démarcheur ***************/
/* si la personne a été marketingnisée, alors on prévient son démarcheur */
$res = $globals->xdb->iterRow(
        "SELECT  DISTINCT a.alias,e.date_envoi
           FROM  envoidirect AS e
     INNER JOIN  aliases     AS a ON ( a.id = e.sender AND a.type='a_vie' )
          WHERE  e.matricule = {?}", $matricule);
while (list($sender_usern, $sender_date) = $res->next()) {
    $mymail = new XOrgMailer('marketing.thanks.tpl');
    $mymail->assign('to', $sender_usern);
    $mymail->assign('prenom', $prenom);
    $mymail->assign('nom',$nom);
    $mymail->assign('promo',$promo);
    $mymail->send();
}

// effacer la pré-inscription devenue 
$globals->xdb->execute('UPDATE en_cours SET loginbis="INSCRIT" WHERE username={?}', $forlife);

// insérer l'inscription dans la table des notifications
require_once('notifs.inc.php');
register_watch_op($uid,WATCH_INSCR);
inscription_notifs_base($uid);

// insérer une ligne dans user_changes pour que les coordonnées complètes
// soient envoyées a l'AX
$globals->xdb->execute('insert into user_changes values ({?})', $uid);

// envoi du mail à l'inscrit
$mymail = new XOrgMailer('inscription.reussie.tpl');
$mymail->assign('forlife', $forlife);
$mymail->assign('prenom', $prenom);
$mymail->send();

// s'il est dans la table envoidirect, on le marque comme inscrit
$globals->xdb->execute('UPDATE envoidirect SET date_succes=NOW() WHERE matricule = {?}', $matricule);
$globals->hook->subscribe($forlife, $uid, $promo, $password, true);

start_connexion($uid,false);
$page->run();
?>
