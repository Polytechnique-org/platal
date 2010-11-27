<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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
    echo "Error.\n";
    $mymail = new PlMailer();
    $mymail->addTo($globals->money->email);
    $mymail->setFrom("webmaster@" . $globals->mail->domain);
    $mymail->setSubject("erreur lors d'un télépaiement (CyberPaiement)");
    $mymail->setTxtBody("\n\n".var_export($_REQUEST,true));
    $mymail->send();
    echo "Notification sent.\n";
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
    for ($i = 0; $i < strlen($s); ++$i) {
    $dgt = $s{$i};
        $sum += ($i % 2) ? (2 * $dgt) % 9 : $dgt;
    }
    return $sum % 10;
}

/* calcule la clé d'acceptation a partir de 5 champs */
function cle_accept($d1, $d2, $d3, $d4, $d5)
{
    $m1 = luhn($d1 . $d5);
    $m2 = luhn($d2 . $d5);
    $m3 = luhn($d3 . $d5);
    $m4 = luhn($d4 . $d5);
    $n = $m1 + $m2 + $m3 + $m4;
    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return $alpha{$n-1} . $m1 . $m2 . $m3 . $m4;
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

/* check if a RIB account number is valid */
function check_rib($rib)
{
    if(strlen($rib) != 23) return false;

    // extract fields
    $rib = strtr(strtoupper($rib),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','12345678912345678923456789');
    $bank    = substr($rib,0,5);
    $counter = substr($rib,5,5);
    $account = substr($rib,10,11);
    $key     = substr($rib,21,2);

    // check
    return (0 == fmod(89 * $bank + 15 * $counter + 3 * $account + $key, 97));
}

class PaymentModule extends PLModule
{
    function handlers()
    {
        return array(
            'payment'                      => $this->make_hook('payment',          AUTH_MDP,    'payment'),
            'payment/cyber_return'         => $this->make_hook('cyber_return',     AUTH_PUBLIC, 'user', NO_HTTPS),
            'payment/cyber2_return'        => $this->make_hook('cyber2_return',    AUTH_PUBLIC, 'user', NO_HTTPS),
            'payment/paypal_return'        => $this->make_hook('paypal_return',    AUTH_PUBLIC, 'user', NO_HTTPS),
            '%grp/paiement'                => $this->make_hook('xnet_payment',     AUTH_MDP),
            '%grp/payment'                 => $this->make_hook('xnet_payment',     AUTH_MDP),
            '%grp/payment/cyber_return'    => $this->make_hook('cyber_return',     AUTH_PUBLIC, 'user', NO_HTTPS),
            '%grp/payment/cyber2_return'   => $this->make_hook('cyber2_return',    AUTH_PUBLIC, 'user', NO_HTTPS),
            '%grp/payment/paypal_return'   => $this->make_hook('paypal_return',    AUTH_PUBLIC, 'user', NO_HTTPS),
            'admin/payments'               => $this->make_hook('admin',            AUTH_MDP,    'admin'),
            'admin/payments/methods'       => $this->make_hook('adm_methods',      AUTH_MDP,    'admin'),
            'admin/payments/transactions'  => $this->make_hook('adm_transactions', AUTH_MDP,    'admin'),
            'admin/reconcile'              => $this->make_hook('adm_reconcile',    AUTH_MDP,    'admin'),
            'admin/reconcile/importlogs'   => $this->make_hook('adm_importlogs',   AUTH_MDP,    'admin'),
            'admin/reconcile/transfers'    => $this->make_hook('adm_transfers',    AUTH_MDP,    'admin'),
            'admin/reconcile/bankaccounts' => $this->make_hook('adm_bankaccounts', AUTH_MDP,    'admin'),
        );
    }

    function handler_payment(&$page, $ref = -1)
    {
        global $globals;

        $this->load('money.inc.php');

        if (!empty($GLOBALS['IS_XNET_SITE'])) {
            if (!$globals->asso('id')) {
                return PL_NOT_FOUND;
            }
            $res = XDB::query('SELECT  asso_id
                                 FROM  payments
                                WHERE  asso_id = {?} AND id = {?}',
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
        $val = Env::v('montant') != 0 ? Env::v('montant') : $pay->amount_def;

        if (($e = $pay->check($val)) !== true) {
            $page->trigError($e);
        }

        if ($op == 'submit') {
            $pay->init($val, $meth);
            $pay->prepareform($pay);
        } else {
            $res = XDB::iterator('SELECT  timestamp, amount
                                    FROM  payment_transactions
                                   WHERE  uid = {?} AND ref = {?}
                                ORDER BY  timestamp DESC',
                                 S::v('uid', -1), $ref);

            if ($res->total()) {
                $page->assign('transactions', $res);
            }
        }

        $val = floor($val) . '.' . substr(floor(($val - floor($val)) * 100 + 100), 1);
        $page->assign('montant', $val);
        $page->assign('comment', Env::v('comment'));

        $page->assign('meth', $meth);
        $page->assign('pay', $pay);
        $page->assign('evtlink', $pay->event());
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
        $res = XDB::query('SELECT  mail, text, confirmation
                             FROM  payments
                            WHERE  id = {?}', $ref);
        if (!list($conf_mail, $conf_title, $conf_text) = $res->fetchOneRow()) {
            cb_erreur("référence de commande inconnue");
        }

        /* on extrait le code de retour */
        if ($champ906 != "0000") {
            $res = XDB::query('SELECT  rcb.text, c.id, c.text
                                 FROM  payment_codeRCB AS rcb
                            LEFT JOIN  payment_codeC   AS c ON (rcb.codeC = c.id)
                                WHERE  rcb.id = {?}', $champ906);
            if (list($rcb_text, $c_id, $c_text) = $res->fetchOneRow()) {
                cb_erreur("erreur lors du paiement : $c_text ($c_id)");
            } else{
                cb_erreur("erreur inconnue lors du paiement");
            }
        }

        /* on fait l'insertion en base de donnees */
        XDB::execute('INSERT INTO  payment_transactions (id, uid, ref, fullref, amount, pkey, comment)
                           VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?})',
                     $champ901, $user->id(), $ref, $champ200, $montant, $champ905, Env::v('comment'));

        // We check if it is an Xnet payment and then update the related ML.
        $res = XDB::query('SELECT  eid
                             FROM  group_events
                            WHERE  paiement_id = {?}', $ref);
        if ($eid = $res->fetchOneCell()) {
            require_once dirname(__FILE__) . '/xnetevents/xnetevents.inc.php';
            $evt = get_event_detail($eid);
            subscribe_lists_event($uid, $evt, 1, $montant, true);
        }

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

    function handler_cyber2_return(&$page, $uid = null)
    {
        global $globals, $platal;

        /* on vérifie la signature */
        $vads_params = array();
        foreach($_REQUEST as $key => $value)
            if(substr($key,0,5) == 'vads_') {
                $vads_params[$key] = $value;
            }
        ksort($vads_params);
        $signature = sha1(join('+', $vads_params) . '+' . $globals->money->cyperplus_key);
        //if($signature != Env::v('signature')) {
        //    cb_erreur("signature invalide");
        //}

        /* on extrait les informations sur l'utilisateur */
        $user = User::get(Env::i('vads_cust_id'));
        if (!$user) {
            cb_erreur("uid invalide");
        }

        /* on extrait la reference de la commande */
        if (!ereg('-([0-9]+)$', Env::v('vads_order_id'), $matches)) {
            cb_erreur("référence de commande invalide");
        }

        $ref = $matches[1];
        $res = XDB::query('SELECT  mail, text, confirmation
                             FROM  payments
                            WHERE  id={?}", $ref);
        if ($res->numRows() != 1) {
            cb_erreur("référence de commande inconnue");
        }
        list($conf_mail, $conf_title, $conf_text) = $res->fetchOneRow();

        /* on extrait le montant */
        if (Env::v('vads_currency') != '978') {
            cb_erreur("monnaie autre que l'euro");
        }
        $amount = ((float)Env::i('vads_amount')) / 100;
        $montant = sprintf("%.02f EUR", $amount);

        /* on extrait le code de retour */
        if (Env::v('vads_result') != '00') {
            cb_erreur('erreur lors du paiement : ?? (' . Env::v('vads_result') . ')');
        }

        /* on fait l'insertion en base de donnees */
        XDB::execute('INSERT INTO  payment_transactions (id, uid, ref, fullref, amount, pkey, comment)
                           VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?})',
                     Env::v('vads_trans_date'), $user->id(), $ref, Env::v('vads_order_id'), $montant, '', Env::v('vads_order_info'));
        echo "Paiement stored.\n";

        // We check if it is an Xnet payment and then update the related ML.
        $res = XDB::query('SELECT  eid, asso_id
                             FROM  group_events
                            WHERE  paiement_id = {?}', $ref);
        if ($res->numRows() == 1) {
            list($eid, $asso_id) = $res->fetchOneRow();
            require_once dirname(__FILE__) . '/xnetevents/xnetevents.inc.php';
            $evt = get_event_detail($eid, false, $asso_id);
            subscribe_lists_event($user->id(), $evt, 1, $amount, true);
        }

        /* on genere le mail de confirmation */
        $conf_text = str_replace(
            array('<prenom>', '<nom>', '<promo>', '<montant>', '<salutation>', '<cher>', '<comment>'),
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
               "reference : " . Env::v('vads_order_id') . "\n".
               "montant : $montant\n\n".
               "dump de REQUEST:\n".
               var_export($_REQUEST,true);
        $mymail->setTxtBody($msg);
        $mymail->send();
        echo "Notifications sent.\n";
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
        $reason = ($status == 'Pending') ? Env::s('pending_reason') : Env::s('reason_code');
        /* reference complete de la commande */
        $fullref = Env::s('cm');
        /* montant de la transaction */
        $montant_nb = Env::s('amt');
        /* devise */
        $montant_dev = Env::s('cc');
        $montant = "$montant_nb $montant_dev";

        /* on extrait le code de retour */
        if ($status != "Completed") {
            if ($status) {
                paypal_erreur("erreur lors du paiement : $status - $reason");
            } else {
                paypal_erreur("Paiement annulé", false);
            }
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
        $res = XDB::query('SELECT  mail, text, confirmation
                             FROM  payments
                            WHERE  id = {?}', $ref);
        if (!list($conf_mail, $conf_title, $conf_text) = $res->fetchOneRow()) {
            paypal_erreur('référence de commande inconnue');
        }

        /* on fait l'insertion en base de donnees */
        XDB::execute("INSERT INTO  payment_transactions (id, uid, ref, fullref, amount, pkey, comment)
                           VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?})",
                    $no_transaction, $user->id(), $ref, $fullref, $montant, $clef, Env::v('comment'));

        // We check if it is an Xnet payment and then update the related ML.
        $res = XDB::query('SELECT  eid
                             FROM  group_events
                            WHERE  paiement_id = {?}', $ref);
        if ($eid = $res->fetchOneCell()) {
            require_once dirname(__FILE__) . '/xnetevents/xnetevents.inc.php';
            $evt = get_event_detail($eid);
            subscribe_lists_event($user->id(), $evt, 1, $montant, true);
        }

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
                                 FROM  group_events             AS e
                           INNER JOIN  group_event_participants AS ep ON (ep.eid = e.eid AND uid = {?})
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
                   FROM  payments
                  WHERE  asso_id = {?} AND NOT FIND_IN_SET('old', flags)
               ORDER BY  id DESC", $globals->asso('id'));
        $tit = $res->fetchAllAssoc();
        $page->assign('titles', $tit);

        $trans = array();
        $event = array();
        if (may_update()) {
            static $orders = array('timestamp' => 'p', 'directory_name' => 'a', 'promo' => 'pd', 'comment' => 'p', 'amount' => 'p');

            if (Get::has('order_id') && Get::has('order') && array_key_exists(Get::v('order'), $orders)) {
                $order_id = Get::i('order_id');
                $order = Get::v('order');
                $ordering = ' ORDER BY ' . $orders[$order] . '.' . $order;
                if (Get::has('order_inv') && Get::i('order_inv') == 1) {
                    $ordering .= ' DESC';
                    $page->assign('order_inv', 0);
                } else {
                    $page->assign('order_inv', 1);
                }
                $page->assign('order_id', $order_id);
                $page->assign('order', $order);
                $page->assign('anchor', 'legend_' . $order_id);
            } else {
                $order_id = false;
                $ordering = '';
                $page->assign('order', false);
            }
        } else {
            $ordering = '';
            $page->assign('order', false);
        }
        foreach($tit as $foo) {
            $pid = $foo['id'];
            if (may_update()) {
                $res = XDB::query('SELECT  p.uid, IF(p.timestamp = \'0000-00-00\', 0, p.timestamp) AS date, p.comment, p.amount
                                     FROM  payment_transactions AS p
                               INNER JOIN  accounts             AS a  ON (a.uid = p.uid)
                                LEFT JOIN  account_profiles     AS ap ON (ap.uid = p.uid AND FIND_IN_SET(\'owner\', ap.perms))
                                LEFT JOIN  profile_display      AS pd ON (ap.pid = pd.pid)
                                    WHERE  p.ref = {?}' . (($order_id == $pid) ? $ordering : ''),
                                  $pid);
                $trans[$pid] = User::getBulkUsersWithUIDs($res->fetchAllAssoc(), 'uid', 'user');
                $sum = 0;
                foreach ($trans[$pid] as $i => $t) {
                    $sum += strtr(substr($t['amount'], 0, strpos($t['amount'], 'EUR')), ',', '.');
                    $trans[$pid][$i]['amount'] = str_replace('EUR', '€', $t['amount']);
                }
                $trans[$pid][] = array('limit'  =>  true,
                                       'amount' => strtr($sum, '.', ',') . ' €');
            }
            $res = XDB::iterRow("SELECT  e.eid, e.short_name, e.intitule, ep.nb, ei.montant, ep.paid
                                   FROM  group_events             AS e
                              LEFT JOIN  group_event_participants AS ep ON (ep.eid = e.eid AND ep.uid = {?})
                             INNER JOIN  group_event_items        AS ei ON (ep.eid = ei.eid AND ep.item_id = ei.item_id)
                                  WHERE  e.paiement_id = {?}",
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
            $res = XDB::query('SELECT  amount
                                 FROM  payment_transactions
                                WHERE  id = {?} AND uid = {?}', $pid, S::v('uid'));
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
        $table_editor = new PLTableEditor('admin/payments','payments','id');
        //$table_editor->add_join_table('payment_transactions','ref',true); => on ne supprime jamais une transaction
        $table_editor->add_sort_field('flags');
        $table_editor->add_sort_field('id', true, true);
        $table_editor->on_delete("UPDATE payments SET flags = 'old' WHERE id = {?}", "Le paiement a été archivé");
        $table_editor->describe('text', 'intitulé', true);
        $table_editor->describe('url', 'site web', false);
        $table_editor->describe('amount_def', 'montant par défaut', false);
        $table_editor->describe('amount_min', 'montant minimum', false);
        $table_editor->describe('amount_max', 'montant maximum', false);
        $table_editor->describe('mail', 'email contact', true);
        $table_editor->describe('confirmation', 'message confirmation', false);

        // adds a column with the start date of the linked event if there is one
        $table_editor->add_option_table('group_events', 'group_events.paiement_id = t.id');
        $table_editor->add_option_field('group_events.debut', 'related_event', 'évènement', 'timestamp');

        $table_editor->apply($page, $action, $id);
    }

    function handler_adm_transactions(&$page, $payment_id = null, $action = "list", $id = null) {
        // show transactions. FIXME: should not be modifiable
        $page->setTitle('Administration - Paiements - Transactions');
        $page->assign('title', "Liste des transactions pour le paiement {$payment_id}");

        if ($payment_id == null)
            $page->trigError("Aucun ID de paiement fourni.");

        $table_editor = new PLTableEditor("admin/transactions/{$payment_id}",'payment_transactions','id');
        $table_editor->set_where_clause(XDB::format('ref = {?}', $payment_id));
        $table_editor->apply($page, 'list', $id); // only the 'list' action is allowed
        $page->assign('readonly', 'readonly');     // don't show modification features
    }

    function handler_adm_bankaccounts(&$page, $action = 'list', $id = null) {
        // managment of bank account used for money transfert
        $page->setTitle('Administration - Paiements - RIBs');
        $page->assign('title', "Liste des RIBs");

        $table_editor = new PLTableEditor('admin/payments/bankaccounts', 'payment_bankaccounts', 'id');
        $table_editor->describe('asso_id', 'ID du groupe', false);
        $table_editor->describe('owner', 'titulaire', true);
        $table_editor->add_option_table('groups', 'groups.id = t.asso_id');
        $table_editor->add_option_field('groups.diminutif', 'group_name', 'groupe', 'varchar','account');

        // check RIB key
        if ($action == 'update' && Post::has('account') && !check_rib(Post::v('account'))) {
            $page->trigError("Le RIB n'est pas valide");
            $table_editor->apply($page, 'edit', $id);
            return;
        }

        $table_editor->apply($page, $action, $id);
    }

    function handler_adm_methods(&$page, $action = 'list', $id = null) {
        // show and edit payment methods
        $page->setTitle('Administration - Paiements - Méthodes');
        $page->assign('title', 'Méthodes de paiement');
        $table_editor = new PLTableEditor('admin/payments/methods', 'payment_methods', 'id');
        $table_editor->apply($page, $action, $id);
    }

    function handler_adm_reconcile(&$page, $step = 'list', $param = null) {
        // reconciles logs with transactions
        // FIXME: the admin is considered to be fair => he doesn't hack the $step value, nor other params
        $page->setTitle('Administration - Paiements - Réconciliations');
        $page->changeTpl('payment/reconcile.tpl');
        $page->assign('step', $step);
        $list = true;

        // actions
        if ($step == 'delete' && $param != null) {
            S::assert_xsrf_token();
            XDB::execute('DELETE FROM payment_reconcilations WHERE id = {?}', $param);
            // FIXME: hardcoding !!!
            XDB::execute('UPDATE payment_transactions SET recon_id = NULL,commission = NULL WHERE recon_id = {?} AND method_id = 2', $param);
            XDB::execute('UPDATE payment_transactions SET recon_id = NULL WHERE recon_id = {?} AND method_id = 1', $param);
            $page->trigSuccess("L'entrée " . $param . ' a été supprimée.');

        } elseif ($step == 'edit') {
            $page->trigError("L'édition n'est pas implémentée.");

        } elseif ($step == 'step5') {
            $page->trigSuccess("La réconciliation est terminée. Il est maintenant nécessaire de générer les virements.");

        }

        if($list) {
            // show list of reconciliations, with a "add" button
            $page->assign('title', 'Réconciliation - Liste');
            $page->assign('step', 'list');

            $recongps = array();

            $res = XDB::query("SELECT  r.id, short_name AS method, period_start, period_end, status,
                                       payment_count, sum_amounts, sum_commissions
                                 FROM  payment_reconcilations AS r
                            LEFT JOIN  payment_methods        AS m ON (r.method_id = m.id)
                                WHERE  recongroup_id IS NULL
                             ORDER BY  period_end DESC, period_start DESC");
            foreach ($res->fetchAllAssoc() as $recon)
                $recongps[] = array('recons' => array($recon), 'transfers' => array());

            $res = XDB::query("SELECT  recongroup_id AS id
                                 FROM  payment_reconcilations
                             GROUP BY  recongroup_id
                             ORDER BY  MAX(period_end) DESC, MIN(period_start) DESC");
            foreach ($res->fetchAllAssoc() as $recongp) {
                $res = XDB::query("SELECT  r.id, short_name AS method, period_start, period_end, status,
                                           payment_count, sum_amounts, sum_commissions
                                     FROM  payment_reconcilations AS r
                                LEFT JOIN  payment_methods        AS m ON (r.method_id = m.id)
                                    WHERE  recongroup_id = {?}
                                 ORDER BY  period_end DESC, period_start DESC",
                                  $recongp['id']);
                $recongp['recons'] = $res->fetchAllAssoc();

                $res = XDB::query('SELECT  id, payment_id, amount, account_id, message, date
                                     FROM  payment_transfers
                                    WHERE  recongroup_id = {?}',
                                  $recongp['id']);
                $recongp['transfers'] = $res->fetchAllAssoc();

                $recongps[] = $recongp;
            }
            $page->assign_by_ref('recongps', $recongps);
        }
    }

    function handler_adm_importlogs(&$page, $step, $param = null) {
        $page->setTitle('Administration - Paiements - Réconciliations');
        $page->changeTpl('payment/reconcile.tpl');
        $page->assign('step', $step);

        if (isset($_SESSION['paymentrecon_data'])) {
            // create temporary table with imported data
            XDB::execute('CREATE TEMPORARY TABLE payment_tmp (
                            reference VARCHAR(255) PRIMARY KEY,
                            date DATE,
                            amount DECIMAL(9,2),
                            commission DECIMAL(9,2)
                          )');
            foreach ($_SESSION['paymentrecon_data'] as $i)
                XDB::execute('INSERT INTO payment_tmp VALUES ({?}, {?}, {?}, {?})',
                              $i['reference'], $i['date'], $i['amount'], $i['commission']);
        }

        if ($step == 'step1') {
            $page->assign('title', 'Étape 1');
            unset($_SESSION['paymentrecon_method']);
            unset($_SESSION['paymentrecon_data']);
            unset($_SESSION['paymentrecon_id']);

            // was a payment method choosen ?
            if ($param != null) {
                $_SESSION['paymentrecon_method'] = (int)$param;
                pl_redirect('admin/reconcile/importlogs/step2');

            } else {
            // ask to choose a payment method
                $res = XDB::query('SELECT id, text FROM payment_methods');
                $page->assign('methods', $res->fetchAllAssoc());
            }

        } elseif ( $step == 'step2' ) {
            $page->assign('title', 'Étape 2');

            // import logs formated in CVS
            $fields = array('date', 'reference', 'amount', 'commission');
            $importer = new PaymentLogsImporter();
            $importer->apply($page, 'admin/reconcile/importlogs/step2', $fields);

            // if import is finished
            $result = $importer->get_result();
            if($result != null) {
                $_SESSION['paymentrecon_data'] = $result;
                pl_redirect('admin/reconcile/importlogs/step3');
            }

        } elseif ($step == 'step3' ) {
            $page->assign('title', 'Étape 3');

            // compute reconcilation summary data
            $res = XDB::query('SELECT  MIN(date) AS period_start, MAX(date) AS period_end,
                                       count(*) AS payment_count, SUM(amount) AS sum_amounts,
                                       SUM(commission) AS sum_commissions
                                 FROM  payment_tmp');
            $recon = $res->fetchOneAssoc();
            $recon['method_id'] = $_SESSION['paymentrecon_method'];

            // create reconciliation item in database
            if(Post::has('next')) {
                S::assert_xsrf_token();

                // get parameters
                $recon['period_start'] = preg_replace('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/', '\3-\2-\1', Post::v('period_start'));
                $recon['period_end']   = preg_replace('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/', '\3-\2-\1', Post::v('period_end'));
                    // FIXME: save checks to be done at next step

                // Create reconcilation item in database
                    // FIXME: check if period doesn't overlap with others for the same method_id
                XDB::execute('INSERT INTO  payment_reconcilations (method_id, period_start, period_end,
                                                                   payment_count, sum_amounts, sum_commissions)
                                   VALUES  ({?}, {?}, {?}, {?}, {?}, {?})',
                             $recon['method_id'], $recon['period_start'], $recon['period_end'],
                             $recon['payment_count'], $recon['sum_amounts'], $recon['sum_commissions']);
                $_SESSION['paymentrecon_id'] = XDB::insertId();

                // reconcile simple cases (trans.commission n'est modifié que s'il vaut NULL)
                XDB::execute("UPDATE  payment_transactions AS trans, payment_tmp AS tmp
                                 SET  trans.recon_id = {?}, trans.commission=tmp.commission
                               WHERE  trans.fullref = tmp.reference
                                      AND trans.amount = tmp.amount AND DATE(trans.ts_confirmed) = tmp.date
                                      AND (trans.commission IS NULL OR trans.commission = tmp.commission)
                                      AND method_id = {?} AND recon_id IS NULL AND status = 'confirmed'",
                             $_SESSION['paymentrecon_id'], $recon['method_id']);

                pl_redirect("admin/reconcile/importlogs/step4");

            // show summary of the imported data + ask form start/end of reconcilation period
            } else {
                $recon['period_start'] = preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', '\3/\2/\1', $recon['period_start']);
                $recon['period_end']   = preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', '\3/\2/\1', $recon['period_end']);
                $page->assign('recon', $recon);
            }

        } elseif ($step == 'step4' ) {
            $page->assign('title', 'Étape 4');

            // get reconcilation summary informations
            $res = XDB::query('SELECT * FROM payment_reconcilations WHERE id = {?}', $_SESSION['paymentrecon_id']);
            $recon = $res->fetchOneAssoc();
            $page->assign('recon', $recon);

            if (Post::has('force')) {
                S::assert_xsrf_token();
                foreach (Post::v('force') as $id => $value) {
                    XDB::execute('UPDATE  payment_transactions AS trans, payment_tmp AS tmp
                                     SET  trans.recon_id = {?}, trans.commission = tmp.commission
                                   WHERE  trans.id = {?} AND trans.fullref = tmp.reference',
                                 $_SESSION['paymentrecon_id'], $id);
                }
                $page->trigSuccess('La réconciliation a été forcée pour ' . count(Post::v('force')) . ' transaction(s).');

            } elseif (Post::has('next')) {
                if (strlen($recon['comments'])< 3 ) {
                    $page->trigError('Le commentaire doit contenir au moins 3 caractères.');
                } else {
                    XDB::execute("UPDATE payment_reconcilations SET status = 'transfering' WHERE id = {?}", $_SESSION['paymentrecon_id']);
                    pl_redirect('admin/reconcile/step5');
                }

            } elseif (Post::has('savecomments')) {
                S::assert_xsrf_token();
                $recon['comments'] = Post::v('comments');
                $page->assign('recon', $recon);
                XDB::execute('UPDATE payment_reconcilations SET comments = {?} WHERE id = {?}', $recon['comments'], $_SESSION['paymentrecon_id']);
                $page->trigSuccess('Les commentaires ont été enregistrés.');
            }

            // reconcilation results - ok
            $res = XDB::query('SELECT  count(*), SUM(amount), SUM(commission)
                                 FROM  payment_transactions
                                WHERE recon_id = {?}',
                              $recon['id']);
            list($ok_count, $ok_sum_amounts, $ok_sum_coms) = $res->fetchOneRow();
            $page->assign('ok_count', $ok_count);

            // reconcilation results - ref exists, but some data differs
            $res = XDB::query('SELECT  id, fullref, method_id, ts_confirmed, trans.amount, trans.commission, status, recon_id,
                                       reference, date, tmp.amount as amount2, tmp.commission as commission2
                                 FROM  payment_transactions AS trans
                           INNER JOIN  payment_tmp          AS tmp ON (trans.fullref = tmp.reference)
                                WHERE  trans.recon_id IS NULL OR trans.recon_id != {?}',
                              $recon['id']);
            $differs = $res->fetchAllAssoc();
            $page->assign_by_ref('differs', $differs);
            $page->assign('differ_count', count($differs));

            // reconcilation results - ref doesn't exists in database
            $res = XDB::query('SELECT  tmp.*
                                 FROM  payment_tmp          AS tmp
                            LEFT JOIN  payment_transactions AS trans ON (trans.fullref = tmp.reference)
                                WHERE  trans.fullref IS NULL');
            $only_import = $res->fetchAllAssoc();
            $page->assign_by_ref('only_import', $only_import);
            $page->assign('onlyim_count', count($only_import));

            // reconcilation results - exists in database but not in import
            $res = XDB::query('SELECT  trans.*
                                 FROM  payment_transactions AS trans
                            LEFT JOIN  payment_tmp          AS tmp ON (trans.fullref = tmp.reference)
                                WHERE  {?} <= DATE(trans.ts_confirmed) AND DATE(trans.ts_confirmed) <= {?}
                                       AND tmp.reference IS NULL AND method_id = {?}',
                              $recon['period_start'], $recon['period_end'], $recon['method_id']);
            $only_database = $res->fetchAllAssoc();
            $page->assign_by_ref('only_database', $only_database);
            $page->assign('onlydb_count', count($only_database));
        }
    }

    function handler_adm_transfers(&$page, $action = null, $id = null) {
        // list/log all bank transfers and link them to individual transactions

        if (Post::has('generate')) {
            $recon_ids = array_keys(Post::v('recon_id'));

            // generate a new reconcilation group ID
            $res = XDB::query("SELECT MAX(recongroup_id)+1 FROM payment_reconcilations");
            $recongp_id = $res->fetchOneCell();
            if ($recongp_id == null) $recongp_id = 1;

            // add reconcilations to group
                // FIXME: should check if reconcilations are in good status
            XDB::execute("UPDATE  payment_reconcilations
                             SET  recongroup_id = {?}, status = 'closed'
                           WHERE  id IN {?}",
                         $recongp_id, $recon_ids);

            // create transfers
            XDB::execute('INSERT INTO  payment_transfers
                               SELECT  NULL, {?}, t.ref, SUM(t.amount+t.commission), NULL, p.text, NULL
                                 FROM  payment_transactions AS t
                            LEFT JOIN  payments             AS p ON (t.ref = p.id)
                            LEFT JOIN  groups               AS g ON (p.asso_id = g.id)
                                WHERE  t.recon_id IN {?}
                             GROUP BY  t.ref',
                         $recongp_id, $recon_ids);

            //$res = XDB::query("SELECT * FROM  payment_reconcilations WHERE id IN {?}", $recon_ids);
            //$recons = $res->fetchAllAssoc();

            $page->trigSuccess('Les virements ont été générés pour ' . count($recon_ids) . ' réconciliations.');
            $this->handler_adm_reconcile($page);

        } elseif ($action == 'delgroup') {
            S::assert_xsrf_token();
            XDB::execute("UPDATE  payment_reconcilations
                             SET  status = 'transfering', recongroup_id = NULL
                           WHERE  recongroup_id = {?}", $id);
            XDB::execute("DELETE FROM  payment_transfers
                                WHERE  recongroup_id = {?} AND date IS NULL", $id);

            $page->trigSuccess("Les virements non réalisés ont été supprimé du groupe ".$id.".");
            $this->handler_adm_reconcile($page);

        } elseif ($action == "confirm") {
            S::assert_xsrf_token();
            XDB::execute('UPDATE  payment_transfers
                             SET  date = NOW()
                           WHERE  id = {?}', $id);

            $page->trigSuccess('Virement ' . $id . ' confirmé.');
            $this->handler_adm_reconcile($page);

        } else {
            pl_redirect('admin/reconcile');
        }
    }
}

class PaymentLogsImporter extends CSVImporter {
    protected $result;

    public function __construct() {
        parent::__construct('');
        $this->registerFunction('systempay_commission', 'Compute BPLC commission', array($this, 'compute_systempay_commission'));
        $this->registerFunction('payment_id', 'Autocompute payment ID', array($this, 'compute_payment_id'));
        //$this->forceValue('payment_id','func_payment_id');
    }

    public function run($action = null, $insert_relation = null, $update_relation = null) {
        $this->result = array();
        foreach ($this->data as $line) {
            $a = $this->makeAssoc($line, $insert_relation);
            $a['date'] = preg_replace('/([0-9]{2})\/([0-9]{2})\/([0-9]{4}).*/', '\3-\2-\1', $a['date']);
            $a['amount'] = str_replace(',', '.', $a['amount']);
            $a['commission'] = str_replace(',', '.', $a['commission']);
            $this->result[] = $a;
        }
    }

    public function get_result() {
        return $this->result;
    }

    static public function compute_systempay_commission($line, $key, $relation) {
        static $EEE_countries = array(
            'France', 'Allemagne', 'Autriche', 'Belgique', 'Bulgarie', 'Chypre',
            'Danemark', 'Espagne', 'Estonie', 'Finlande', 'Grèce', 'Hongrie', 'Irlande', 'Islande', 'Italie',
            'Lettonie', 'Liechtenstein', 'Lituanie', 'Luxembourg', 'Malte', 'Norvège', 'Pays-Bas', 'Pologne',
            'Portugal', 'Roumanie', 'Royaume-Uni', 'Slovaquie', 'Slovénie', 'Suède', 'République Tchèque'
        );

        if($key!='commission' || !array_key_exists('carte', $line)) {
            return null;
        }
        $amount = self::getValue($line, 'amount', $relation['amount']);
        if (in_array($line['pays carte'], $EEE_countries)) {
            return -0.20 - round($amount * 0.005, 2);
        } else {
            return -0.20 - round($amount * 0.005, 2) - 0.76;
        }
    }

    static public function compute_payment_id($line, $key, $relation) {
        if ($key != 'payment_id') {
            return null;
        }
        $reference = self::getValue($line, 'reference', $relation['reference']);
        if (ereg('-([0-9]+)$', $reference, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
