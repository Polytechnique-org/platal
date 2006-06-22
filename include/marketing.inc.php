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

require_once("xorg.misc.inc.php");

// {{{ function mark_from_mail

function mark_from_mail($uid, $email) {
    global $globals;
    $res = $globals->xdb->query(
        "SELECT u.nom, u.prenom, a.alias
           FROM register_marketing  AS r
     INNER JOIN auth_user_md5       AS u ON (r.sender = u.user_id)
     INNER JOIN aliases             AS a ON (a.id = r.sender AND a.type='a_vie')
          WHERE r.uid = {?} AND r.email = {?}",
        $uid, $email);
    $sender = $res->fetchOneAssoc();
    return "\"".$sender['prenom']." ".$sender['nom']."\" <".$sender['alias']."@polytechnique.org>";
    
}

// }}}
// {{{ function mark_text_mail

function mark_text_mail($uid, $email)
{
    global $globals;
    $title = "Annuaire en ligne des Polytechniciens";

    $res = $globals->xdb->query("SELECT COUNT(*) FROM auth_user_md5 WHERE perms IN ('admin', 'user') and deces = 0");
    $num_users = $res->fetchOneCell();

    $res = $globals->xdb->query("SELECT flags, nom, prenom, promo FROM auth_user_md5 WHERE user_id = {?}", $uid);
    $u = $res->fetchOneAssoc();

    $mailorg = make_forlife($u['prenom'],$u['nom'],$u['promo']);

    $to = "\"".$u['prenom']." ".$u['nom']."\" <".$email.">";

    $titre = "Annuaire en ligne des Polytechniciens";
    $text  = "   ".($u['flags']?"Chère":"Cher")." camarade,\n\n";
    $text .= "   Ta fiche n'est pas à jour dans l'annuaire des Polytechniciens sur Internet. Pour la mettre à jour, il te suffit de visiter cette page ou de copier cette adresse dans la barre de ton navigateur :\n\n";
    $text .= "==========================================================\n";
    $text .= $globals->baseurl."/register/?hash=%%hash%%\n";
    $text .= "==========================================================\n\n";
    $text .= "Il ne te faut que 5 minutes sur http://www.polytechnique.org/ pour rejoindre les $num_users camarades branchés grâce au système de reroutage de l'X et qui permet de joindre un camarade en connaissant seulement son nom et son prénom... et de bénéficier pour la vie d'une adresse prestigieuse $mailorg@polytechnique.org et son alias discret $mailorg@m4x.org (m4x = mail for X).\n\n";
    $text .= "Pas de nouvelle boîte aux lettres à relever, il suffit de la rerouter vers ton adresse personnelle et/ou professionnelle que tu indiques et que tu peux changer tous les jours si tu veux sans imposer à tes correspondants de modifier leur carnet d'adresses...\n\n";
    $text .= "De plus, le site web offre les services d'annuaire (recherche multi-critères), de forums, de mailing-lists. Ce portail est géré par une dizaine de jeunes camarades, avec le soutien et les conseils de nombreux X de toutes promotions, incluant notamment des camarades de la Kès des élèves de l'X et d'autres de l'AX. Les serveurs sont hébergés au sein même de l'Ecole polytechnique, sur une connexion rapide, et les services évoluent en fonction des besoins exprimés par la communauté sur Internet.\n\n";
    $text .="N'hésite pas à transmettre ce message à tes camarades ou à nous écrire, nous proposer toute amélioration ou suggestion pour les versions prochaines du site.\n\n";
    $text .= "A bientôt sur http://www.polytechnique.org !\n";
    $text .= "Bien à toi,\n";
    $text .= "%%sender%%\n\n";
    $text .= "--\n";
    $text .= "Polytechnique.org\n";
    $text .= "\"Le portail des élèves & anciens élèves de l'X\"\n";
    $text .= "http://www.polytechnique.org/\n";
    $text .= "http://www.polytechnique.net/\n";
    return array($to, $title, $text);
}
// }}}
// {{{ function mark_send_mail()

function mark_send_mail($uid, $email, $perso, $to='', $title='', $text='') 
{
    require_once("diogenes/diogenes.hermes.inc.php");
    global $globals;

    $hash = rand_url_id(12);
    $globals->xdb->execute('UPDATE register_marketing SET nb=nb+1,hash={?},last=NOW() WHERE uid={?} AND email={?}', $hash, $uid, $email);
 
    if ($to == '')
        list($to, $title, $text) = mark_text_mail($uid, $email);

    if ($perso == 'staff')
        $from = "\"Equipe Polytechnique.org\" <register@polytechnique.org>";
    else
        $from = mark_from_mail($uid, $email);
    
    $sender = substr($from, 1, strpos($from, '"', 2)-1);
    $text = str_replace(array("%%hash%%", "%%sender%%"), array($hash, $sender), $text);

    $mailer = new HermesMailer();
    $mailer->setFrom($from);
    $mailer->addTo($to);
    $mailer->setSubject($title);
    $mailer->setTxtBody(wordwrap($text, 80));
    $mailer->send();
}

// }}}
// {{{ function relance

function relance($uid, $nbx = -1)
{
    require_once('xorg.mailer.inc.php');
    global $globals;

    if ($nbx < 0) {
        $res = $globals->xdb->query("SELECT COUNT(*) FROM auth_user_md5 WHERE deces=0");
        $nbx = $res->fetchOneCell();
    }

    $res = $globals->xdb->query(
            "SELECT  r.date, u.promo, u.nom, u.prenom, r.email, r.bestalias
               FROM  register_pending AS r
         INNER JOIN  auth_user_md5    AS u ON u.user_id = r.uid
              WHERE  hash!='INSCRIT' AND uid={?} AND TO_DAYS(relance) < TO_DAYS(NOW())", $uid);
    if (!list($date, $promo, $nom, $prenom, $email, $alias) = $res->fetchOneRow()) {
        return false;
    }

    require_once('secure_hash.inc.php');
    
    $hash     = rand_url_id(12);
    $pass     = rand_pass();
    $pass_encrypted = hash_encrypt($pass);
    $fdate    = strftime('%d %B %Y', strtotime($date));
    
    $mymail = new XOrgMailer('marketing.relance.tpl');
    $mymail->assign('nbdix',      $nbx);
    $mymail->assign('fdate',      $fdate);
    $mymail->assign('lusername',  $alias);
    $mymail->assign('nveau_pass', $pass);
    $mymail->assign('baseurl',    $globals->baseurl);
    $mymail->assign('lins_id',    $hash);
    $mymail->assign('lemail',     $email);
    $mymail->assign('subj',       $alias.'@'.$globals->mail->domain);
    $mymail->send();
    $globals->xdb->execute('UPDATE register_pending SET hash={?}, password={?}, relance=NOW() WHERE uid={?}', $hash, $pass_encrypted, $uid);

    return "$prenom $nom ($promo)";
}

// }}}

// vim:set et sw=4 sts=4 sws=4:
?>
