<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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
    global $globals;
    $mymail = new PlMailer();
    $mymail->addTo($globals->money->email);
    $mymail->setFrom("webmaster@" . $globals->mail->domain);
    $mymail->setSubject("erreur lors d'un télépaiement (CyberPaiement)");
    $mymail->setTxtBody("\n\n".var_export($_REQUEST,true));
    $mymail->send();
    exit;
}

/* sort en affichant une erreur */
function paypal_erreur($text, $send=true)
{
    global $erreur, $globals;
    if ($erreur) return;
    $erreur = $text;
    if (!$send) return;

    $mymail = new PlMailer();
    $mymail->addTo($globals->money->email);
    $mymail->setFrom("webmaster@" . $globals->mail->domain);
    $mymail->setSubject("erreur lors d'un télépaiement (PayPal)");
    $mymail->setTxtBody("\n\n".var_export($_REQUEST,true));
    $mymail->send();

    Platal::page()->trigError($text);
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

/* decode the comment */
function comment_decode($comment) {
    $comment = urldecode($comment);
    if (is_utf8($comment)) {
        return $comment;
    } else {
        return utf8_encode($comment);
    }
}


class PaymentModule extends PLModule
{
    function handlers()
    {
        return array(
            'payment'                    => $this->make_hook('payment',       AUTH_MDP),
            'payment/cyber_return'       => $this->make_hook('cyber_return',  AUTH_PUBLIC, 'user', NO_HTTPS),
            'payment/paypal_return'      => $this->make_hook('paypal_return', AUTH_PUBLIC, 'user', NO_HTTPS),
            '%grp/paiement'              => $this->make_hook('xnet_payment',  AUTH_MDP),
            '%grp/payment'               => $this->make_hook('xnet_payment',  AUTH_MDP),
            '%grp/payment/cyber_return'  => $this->make_hook('cyber_return',  AUTH_PUBLIC, 'user', NO_HTTPS),
            '%grp/payment/paypal_return' => $this->make_hook('paypal_return', AUTH_PUBLIC, 'user', NO_HTTPS),
            'admin/payments'             => $this->make_hook('admin',         AUTH_MDP,    'admin'),

        );
    }

    function handler_payment(&$page, $ref = -1)
    {
        global $globals;

        require_once 'profil.func.inc.php' ;
        $this->load('money.inc.php');

        if (!empty($GLOBALS['IS_XNET_SITE'])) {
            if (!$globals->asso('id')) {
                return PL_NOT_FOUND;
            }
            $res = XDB::query("SELECT asso_id
                                 FROM paiement.paiements
                                WHERE asso_id = {?} AND id = {?}",
                              $globals->asso('id'), $ref);
            if (!$res->numRows()) {
                return PL_FORBIDDEN;
            }
        }
        $page->changeTpl('payment/index.tpl');
        $page->setTitle('Télépaiements');

        // initialisation
        $op   = Env::v('op', 'select');
        $meth = new PayMethod(Env::i('methode', -1));
        $pay  = new Payment($ref);

        if($pay->flags->hasflag('old')){
            $page->trigError("La transaction selectionnée est périmée.");
            $pay = new Payment();
        }
        $val = Env::v('montant') != 0 ? Env::v('montant') : $pay->montant_def;

        if (($e = $pay->check($val)) !== true) {
            $page->trigError($e);
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
        $page->assign('comment',Env::v('comment'));

        $page->assign('meth', $meth);
        $page->assign('pay',  $pay);
        $page->assign('evtlink', $pay->event());

        $page->assign('prefix', $globals->money->mpay_tprefix);
    }

    function handler_cyber_return(&$page, $uid = null)
    {
        /* reference banque (numero de transaction) */
        $champ901 = Env::s('CHAMP901');
        /* cle d'acceptation */
        $champ905 = Env::s('CHAMP905');
        /* code retour */
        $champ906 = Env::s('CHAMP906');
        /* email renvoye par la banque */
        $champ104 = Env::s('CHAMP104');
        /* reference complete de la commande */
        $champ200 = Env::s('CHAMP200');
        /* montant de la transaction */
        $champ201 = Env::s('CHAMP201');
        /* devise */
        $champ202 = Env::s('CHAMP202');
        $montant = "$champ201 $champ202";

        /* on extrait les informations sur l'utilisateur */
        $user = User::get($uid);
        if (!$user) {
            cb_erreur("uid invalide");
        }


        /* on extrait la reference de la commande */
        if (!ereg('-xorg-([0-9]+)$', $champ200, $matches)) {
            cb_erreur("référence de commande invalide");
        }

        echo ($ref = $matches[1]);
        $res = XDB::query("SELECT  mail, text, confirmation
                             FROM  paiement.paiements
                            WHERE  id={?}", $ref);
        if (!list($conf_mail, $conf_title, $conf_text) = $res->fetchOneRow()) {
            cb_erreur("référence de commande inconnue");
        }

        /* on extrait le code de retour */
        if ($champ906 != "0000") {
            $res = XDB::query('SELECT  rcb.text, c.id, c.text
                                 FROM  paiement.codeRCB AS rcb
                            LEFT JOIN  paiement.codeC   AS c ON (rcb.codeC = c.id)
                                WHERE  rcb.id = {?}', $champ906);
            if (list($rcb_text, $c_id, $c_text) = $res->fetchOneRow()) {
                cb_erreur("erreur lors du paiement : $c_text ($c_id)");
            } else{
                cb_erreur("erreur inconnue lors du paiement");
            }
        }

        /* on fait l'insertion en base de donnees */
        XDB::execute("INSERT INTO  paiement.transactions (id, uid, ref, fullref, montant, cle, comment)
                           VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?})",
                     $champ901, $user->id(), $ref, $champ200, $montant, $champ905, Env::v('comment'));

        /* on genere le mail de confirmation */
        $conf_text = str_replace(
            array('<prenom>', '<nom>', '<promo>', '<montant>', '<salutation>', '<cher>', 'comment>'),
            array($user->firstName(), $user->lastName(), $user->promo(), $montant,
                  $user->isFemale() ? 'Chère' : 'Cher', $user->isFemale() ? 'Chère' : 'Cher',
                  Env::v('comment')), $conf_text);

        global $globals;
        $mymail = new PlMailer();
        $mymail->setFrom($conf_mail);
        $mymail->addCc($conf_mail);
        $mymail->setSubject($conf_title);
        $mymail->setWikiBody($conf_text);
        $mymail->sendTo($user);

        /* on envoie les details de la transaction à telepaiement@ */
        $mymail = new PlMailer();
        $mymail->setFrom("webmaster@" . $globals->mail->domain);
        $mymail->addTo($globals->money->email);
        $mymail->setSubject($conf_title);
        $msg = 'utilisateur : ' . $user->login() . ' (' . $user->id() . ')' . "\n" .
               'mail : ' . $user->forlifeEmail() . "\n\n" .
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

        /* reference banque (numero de transaction) */
        $no_transaction = Env::s('tx');
        /* token a renvoyer pour avoir plus d'information */
        $clef = Env::s('sig');
        /* code retour */
        $status = Env::s('st');
        /* raison */
        $reason = ($status == 'Pending')? Env::s('pending_reason'): Env::s('reason_code');
        /* reference complete de la commande */
        $fullref = Env::s('cm');
        /* montant de la transaction */
        $montant_nb = Env::s('amt');
        /* devise */
        $montant_dev = Env::s('cc');
        $montant = "$montant_nb $montant_dev";

        /* on extrait le code de retour */
        if ($status != "Completed") {
            if ($status)
                paypal_erreur("erreur lors du paiement : $status - $reason");
            else
                paypal_erreur("Paiement annulé", false);
        }

        /* on extrait les informations sur l'utilisateur */
        $user = User::get($uid);
        if (!$user) {
            paypal_erreur("uid invalide");
        }

        /* on extrait la reference de la commande */
        if (!ereg('-xorg-([0-9]+)$', $fullref, $matches)) {
            paypal_erreur("référence de commande invalide");
        }

        $ref = $matches[1];
        $res = XDB::query("SELECT  mail, text, confirmation
                             FROM  paiement.paiements
                            WHERE  id = {?}", $ref);
        if (!list($conf_mail,$conf_title,$conf_text) = $res->fetchOneRow()) {
            paypal_erreur("référence de commande inconnue");
        }

        /* on fait l'insertion en base de donnees */
        XDB::execute("INSERT INTO  paiement.transactions (id, uid, ref, fullref, montant, cle, comment)
                           VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?})",
                    $no_transaction, $user->id(), $ref, $fullref, $montant, $clef, Env::v('comment'));

        /* on genere le mail de confirmation */
        $conf_text = str_replace(array('<prenom>', '<nom>', '<promo>', '<montant>', '<salutation>', '<cher>'),
                                 array($user->firstName(), $user->lastName(), $user->promo(), $montant,
                                       $user->isFemale() ? 'Chère' : 'Cher',
                                       $user->isFemale() ? 'Chère' : 'Cher'), $conf_text);

        global $globals;
        $mymail = new PlMailer();
        $mymail->setFrom($conf_mail);
        $mymail->addCc($conf_mail);
        $mymail->setSubject($conf_title);
        $mymail->setWikiBody($conf_text);
        $mymail->sendTo($user);

        /* on envoie les details de la transaction à telepaiement@ */
        $mymail = new PlMailer();
        $mymail->setFrom("webmaster@" . $globals->mail->domain);
        $mymail->addTo($globals->money->email);
        $mymail->setSubject($conf_title);
        $msg = 'utilisateur : ' . $user->login() . ' (' . $user->id() . ')' . "\n" .
               'mail : ' . $user->forlifeEmail() . "\n\n" .
               "paiement : $conf_title ($conf_mail)\n".
               "reference : $champ200\n".
               "montant : $montant\n\n".
               "dump de REQUEST:\n".
               var_export($_REQUEST,true);
        $mymail->setTxtBody($msg);
        $mymail->send();

        $page->assign('texte', $conf_text);
        $page->assign('erreur', $erreur);
    }

    function handler_xnet_payment(&$page, $pid = null)
    {
        global $globals;

        $perms = S::v('perms');
        if (!$perms->hasFlag('groupmember')) {
            if (is_null($pid)) {
                return PL_FORBIDDEN;
            }
            $res = XDB::query("SELECT  1
                                 FROM  groupex.evenements AS e
                           INNER JOIN  groupex.evenements_participants AS ep ON (ep.eid = e.eid AND uid = {?})
                                WHERE  e.paiement_id = {?} AND e.asso_id = {?}",
                              S::i('uid'), $pid, $globals->asso('id'));
            if ($res->numRows() == 0) {
                return PL_FORBIDDEN;
            }
        }

        if (!is_null($pid)) {
            return  $this->handler_payment($page, $pid);
        }
        $page->changeTpl('payment/xnet.tpl');

        $res = XDB::query(
                "SELECT  id, text, url
                   FROM  {$globals->money->mpay_tprefix}paiements
                  WHERE  asso_id = {?} AND NOT FIND_IN_SET('old', flags)
               ORDER BY  id DESC", $globals->asso('id'));
        $tit = $res->fetchAllAssoc();
        $page->assign('titres', $tit);


        // TODO: replug sort.
        $trans = array();
        $event = array();
        foreach($tit as $foo) {
            $pid = $foo['id'];
            if (may_update()) {
                $res = XDB::query('SELECT  t.uid, timestamp AS `date`, t.comment, montant
                                     FROM  ' . $globals->money->mpay_tprefix . 'transactions AS t
                                    WHERE  t.ref = {?}', $pid);
                $trans[$pid] = User::getBulkUsersWithUIDs($res->fetchAllAssoc(), 'uid', 'user');
                $sum = 0;
                foreach ($trans[$pid] as $i => $t) {
                    $sum += strtr(substr($t['montant'], 0, strpos($t['montant'], 'EUR')), ',', '.');
                    $trans[$pid][$i]['montant'] = str_replace('EUR', '€', $t['montant']);
                }
                $trans[$pid][] = array('nom' => 'somme totale',
                                       'montant' => strtr($sum, '.', ',').' €');
            }
            $res = XDB::iterRow("SELECT e.eid, e.short_name, e.intitule, ep.nb, ei.montant, ep.paid
                                   FROM groupex.evenements AS e
                              LEFT JOIN groupex.evenements_participants AS ep ON (ep.eid = e.eid AND uid = {?})
                             INNER JOIN groupex.evenements_items AS ei ON (ep.eid = ei.eid AND ep.item_id = ei.item_id)
                                  WHERE e.paiement_id = {?}",
                                 S::v('uid'), $pid);
            $event[$pid] = array();
            $event[$pid]['paid'] = 0;
            if ($res->total()) {
                $event[$pid]['topay'] = 0;
                while(list($eid, $shortname, $title, $nb, $montant, $paid) = $res->next()) {
                    $event[$pid]['topay']     += ($nb * $montant);
                    $event[$pid]['eid']       = $eid;
                    $event[$pid]['shortname'] = $shortname;
                    $event[$pid]['title']     = $title;
                    $event[$pid]['ins']       = !is_null($nb);
                    $event[$pid]['paid']      = $paid;
                }
            }
            $res = XDB::query("SELECT montant
                                 FROM {$globals->money->mpay_tprefix}transactions AS t
                                WHERE ref = {?} AND uid = {?}", $pid, S::v('uid'));
            $montants = $res->fetchColumn();

            foreach ($montants as $m) {
                $p = strtr(substr($m, 0, strpos($m, 'EUR')), ',', '.');
                $event[$pid]['paid'] += trim($p);
            }
        }
        $page->register_modifier('decode_comment', 'decode_comment');
        $page->assign('trans', $trans);
        $page->assign('event', $event);
    }

    function handler_admin(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Paiements');
        $page->assign('title', 'Gestion des télépaiements');
        $table_editor = new PLTableEditor('admin/payments','paiement.paiements','id');
        $table_editor->add_join_table('paiement.transactions','ref',true);
        $table_editor->add_sort_field('flags');
        $table_editor->add_sort_field('id', true, true);
        $table_editor->on_delete("UPDATE paiement.paiements SET flags = 'old' WHERE id = {?}", "Le paiement a été archivé");
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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
