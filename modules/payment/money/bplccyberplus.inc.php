<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

/*
    Numéros de cartes de test :
    Commerçant non enrôlé 3D-Secure
    4970 1000 0000 0003 Paiement accepté (autorisation accordée)
    Commerçant enrôlé 3D-Secure
    4970 1000 0000 0000 Paiement accepté avec authentification internaute
    4970 1000 0000 0001 Paiement accepté sans authentification internaute (Internaute non enrôlé 3D-Secure)
    4970 1000 0000 0002 contacter l'émetteur de carte (Transaction à forcer). Authentification réalisée avec succès.
    4970 1000 0000 0006 Problème technique lors du calcul de la garantie de paiement
    4970 1000 0000 0007 Problème technique lors de l’authentification porteur
    4970 1000 0000 0097 Paiement refusé pour cause d’authentification 3D-Secure échouée (l'internaute n'est pas parvenu à s'authentifier)
    4970 1000 0000 0098 Paiement refusé (autorisation refusée pour cause de plafond dépassé)
    4970 1000 0000 0099 Paiement refusé (autorisation refusée suite à erreur dans le cryptogramme visuel saisi)
*/

class BPLCCyberPlus
{
    // {{{ properties

    var $val;

    var $urlform;
    var $nomsite = "la BP Lorraine Champagne";
    var $infos;

    // }}}
    // {{{ constructor

    function BPLCCyberPlus($val)
    {
        // SVA : nombre de chiffres apr�s la virgule d�pendant de currency ?
        $this->val = 100 * strtr(sprintf("%.02f", (float)$val), '.', ',');
    }

    // }}}
    // {{{ function form()

    function prepareform($pay)
    {
        global $globals, $platal;
        $log = S::v('log');

        // on constuit la reference de la transaction
        $prefix = ($pay->flags->hasflag('unique')) ? str_pad("",15,"0") : rand_url_id();
        $fullref = substr("$prefix-{$pay->id}",-12); // FIXME : check for duplicates
        $ts = time();
        $trans_date = date("YmdHis", $ts);
        $trans_id = date("His", $ts); // FIXME : check for duplicates

        // contenu du formulaire
        $this->urlform = "https://systempay.cyberpluspaiement.com/vads-payment/";
        $this->infos['commercant'] = Array(
            'vads_site_id' => $globals->money->cyperplus_account,
            'vads_return_mode' => 'NONE',
            'vads_url_return' => $pay->url ? $pay->url : $globals->baseurl . '/' . $platal->ns);
        $this->infos['client'] = Array(
            'vads_cust_email' => S::user()->bestEmail(),
            'vads_cust_id' => S::v('uid'),
            'vads_cust_name' => replace_accent(S::user()->fullName()));
        $this->infos['commande'] = Array(
            'vads_amount' => $this->val,
            'vads_currency' => '978', # Euro
            'vads_payment_config' => 'SINGLE',
            'vads_trans_date' => $trans_date,
            'vads_trans_id' => $trans_id,
            'vads_order_id' => $fullref,
            'vads_order_info' => Env::v('comment'));
        $this->infos['divers'] = Array(
            'vads_version' => 'V2',
            'vads_ctx_mode' => $globals->money->cyperplus_prod,
            'vads_page_action' => 'PAYMENT',
            'vads_action_mode' => 'INTERACTIVE');

        // calcul de la cl� d'acceptation en entr�e
        $all_params = array_merge($this->infos['commercant'],$this->infos['client'],$this->infos['commande'],$this->infos['divers']);
        ksort($all_params);
        $this->infos['divers']['signature'] = sha1(join('+',$all_params).'+'.$globals->money->cyperplus_key);
    }

    // }}}
}

$api = 'BPLCCyberPlus';

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
