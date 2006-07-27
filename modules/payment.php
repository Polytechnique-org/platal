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

/* sort en affichant une erreur */
function cb_erreur($text) {
    $mymail = new HermesMailer();
    $mymail->addTo("telepaiement@polytechnique.org");
    $mymail->setFrom("webmaster@polytechnique.org");
    $mymail->setSubject("erreur lors d'un télépaiement (CyberPaiement)");
    $mymail->setTxtBody("\n\n".var_export($_REQUEST,true));
    $mymail->send();
    exit;
}

/* sort en affichant une erreur */
function paypal_erreur($text, $send=true) {
    global $page, $erreur;
    if ($erreur) return;
    $erreur = $text;
    if (!$send) return;

    $mymail = new HermesMailer();
    $mymail->addTo("telepaiement@polytechnique.org");
    $mymail->setFrom("webmaster@polytechnique.org");
    $mymail->setSubject("erreur lors d'un télépaiement (PayPal)");
    $mymail->setTxtBody("\n\n".var_export($_REQUEST,true));
    $mymail->send();

    $page->trig($text);
}

/* http://fr.wikipedia.org/wiki/Formule_de_Luhn */
function luhn($nombre) {
    $s = strrev($nombre);
    $sum = 0;
    for ($i = 0; $i < strlen($s); $i++) {
	$dgt = $s{$i};
        $sum += ($i % 2) ? (2*$dgt) % 9 : $dgt;
    }
    return $sum % 10;
}

/* calcule la clé d'acceptation a partir de 5 champs */
function cle_accept($d1,$d2,$d3,$d4,$d5)
{
    $m1 = luhn($d1.$d5);
    $m2 = luhn($d2.$d5);
    $m3 = luhn($d3.$d5);
    $m4 = luhn($d4.$d5);
    $n = $m1 + $m2 + $m3 + $m4;
    $alpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return $alpha{$n-1}.$m1.$m2.$m3.$m4;
}


class PaymentModule extends PLModule
{
    function handlers()
    {
        return array(
            'payment'               => $this->make_hook('payment', AUTH_MDP),
            'payment/cyber_return'  => $this->make_hook('cyber_return',  AUTH_PUB),
            'payment/paypal_return' => $this->make_hook('paypal_return',  AUTH_PUB),
            'admin/payments'        => $this->make_hook('admin', AUTH_MDP, 'admin'),
        );
    }

    function handler_payment(&$page, $ref = -1)
    {
        global $globals;

        require_once 'profil.func.inc.php' ;
        require_once 'money.inc.php' ;

        $page->changeTpl('payment/index.tpl');
        $page->assign('xorg_title','Polytechnique.org - Télépaiements');

        // initialisation
        $op   = Env::v('op', 'select');
        $meth = new PayMethod(Env::i('methode', -1));
        $pay  = new Payment($ref);

        if($pay->flags->hasflag('old')){
            $page->trig("La transaction selectionnée est périmée.");
            $pay = new Payment();
        }
        $val = Env::v('montant') != 0 ? Env::v('montant') : $pay->montant_def;

        if (($e = $pay->check($val)) !== true) {
            $page->trig($e);
        }

        if ($op=='submit') {
            $pay->init($val, $meth);
            $pay->prepareform($pay);
        } else {
            $res = XDB::iterator("SELECT  timestamp, montant
                                              FROM  paiement.transactions
                                             WHERE  uid = {?} AND ref = {?}
                                          ORDER BY  timestamp DESC",
                                            S::v('uid', -1), $ref);

            if ($res->total()) $page->assign('transactions', $res);
        }

        $val = floor($val).".".substr(floor(($val - floor($val))*100+100),1);
        $page->assign('montant',$val);

        $page->assign('meth', $meth);
        $page->assign('pay',  $pay);
        $page->assign('evtlink', $pay->event());

        $page->assign('prefix', $globals->money->mpay_tprefix);
    }

    function handler_cyber_return(&$page, $uid = null)
    {
        require_once 'diogenes/diogenes.hermes.inc.php';

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
        $res = XDB::query("
            SELECT  a.prenom,a.nom,a.promo,l.alias,FIND_IN_SET(a.flags,'femme')
              FROM  auth_user_md5 AS a
        INNER JOIN  aliases       AS l ON (a.user_id=l.id AND type!='homonyme')
             WHERE  a.user_id={?}", $uid);
        if (!list($prenom,$nom,$promo,$forlife,$femme) = $res->fetchOneRow()) {
            cb_erreur("uid invalide");
        }


        /* on extrait la reference de la commande */
        if (!ereg('-xorg-([0-9]+)$',$champ200,$matches)) {
            cb_erreur("référence de commande invalide");
        }

        echo ($ref = $matches[1]);
        $res = XDB::query("SELECT mail,text,confirmation
                                       FROM paiement.paiements WHERE id={?}", $ref);
        if (!list($conf_mail,$conf_title,$conf_text) = $res->fetchOneRow()) {
            cb_erreur("référence de commande inconnue");
        }

        /* on extrait le code de retour */
        if ($champ906 != "0000") {
            $res = XDB::query("SELECT  rcb.text,c.id,c.text
                                           FROM  paiement.codeRCB AS rcb
                                      LEFT JOIN  paiement.codeC   AS c ON rcb.codeC=c.id
                                          WHERE  rcb.id='$champ906'");
            if (list($rcb_text, $c_id, $c_text) = $res->fetchOneRow()) {
                cb_erreur("erreur lors du paiement : $c_text ($c_id)");
            } else{ 
                cb_erreur("erreur inconnue lors du paiement");
            }
        }

        /* on fait l'insertion en base de donnees */
        XDB::execute("INSERT INTO  paiement.transactions (id,uid,ref,fullref,montant,cle)
                                     VALUES  ({?},{?},{?},{?},{?},{?})",
                                $champ901, $uid, $ref, $champ200, $montant, $champ905);

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
               "reference : $champ200\n".
               "montant : $montant\n\n".
               "dump de REQUEST:\n".
               var_export($_REQUEST,true);
        $mymail->setTxtBody($msg);
        $mymail->send();
        exit;
    }

    function handler_paypal_return(&$page, $uid = null)
    {
        $page->changeTpl('payment/retour_paypal.tpl');
        require_once 'diogenes/diogenes.hermes.inc.php';

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
                paypal_erreur("erreur lors du paiement : $status - $reason");
            else
                paypal_erreur("Paiement annulé", false);
        }

        /* on extrait les informations sur l'utilisateur */
        $res = XDB::query("
            SELECT  a.prenom,a.nom,a.promo,l.alias,FIND_IN_SET(a.flags,'femme')
              FROM  auth_user_md5 AS a
        INNER JOIN  aliases       AS l ON (a.user_id=l.id AND type!='homonyme')
             WHERE  a.user_id={?}", $uid);
        if (!list($prenom,$nom,$promo,$forlife,$femme) = $res->fetchOneRow()) {
            paypal_erreur("uid invalide");
        }

        /* on extrait la reference de la commande */
        if (!ereg('-xorg-([0-9]+)$',$fullref,$matches)) {
            paypal_erreur("référence de commande invalide");
        }

        $ref = $matches[1];
        $res = XDB::query("SELECT  mail,text,confirmation
                                       FROM  paiement.paiements WHERE id={?}", $ref);
        if (!list($conf_mail,$conf_title,$conf_text) = $res->fetchOneRow()) {
            paypal_erreur("référence de commande inconnue");
        }

        /* on fait l'insertion en base de donnees */
        XDB::execute("INSERT INTO  paiement.transactions (id,uid,ref,fullref,montant,cle)
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
    }
    function handler_admin(&$page, $action = 'list', $id = null) {
        require_once('../classes/PLTableEditor.php');
        $page->assign('xorg_title','Polytechnique.org - Administration - Paiements');
        $page->assign('title', 'Gestion des télépaiements');
        $table_editor = new PLTableEditor('admin/payments','paiement.paiements','id');
        $table_editor->add_join_table('paiement.transactions','ref',true);
        $table_editor->describe('text','intitulé',true);
        $table_editor->describe('url','site web',false);
        $table_editor->describe('montant_def','montant par défaut',false);
        $table_editor->describe('montant_min','montant minimum',false);
        $table_editor->describe('montant_max','montant maximum',false);
        $table_editor->describe('mail','email contact',true);
        $table_editor->describe('confirmation','message confirmation',false);
        $table_editor->apply($page, $action, $id);
    }  
}

?>
