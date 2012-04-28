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

/** Card numbers for tests.
 * Non 3D-Secure shop:
 *  4970 1000 0000 0003 Payment accepted (authorized)
 * 3D-Secure shop:
 *  4970 1000 0000 0000 Payment accepted, with user authentication.
 *  4970 1000 0000 0001 Payment accepted, without user authentication (non 3D-Secure user).
 *  4970 1000 0000 0002 Must contact card issuer (which must force transaction). Succesful authentication.
 *  4970 1000 0000 0006 Technical issue while computing payment warranty.
 *  4970 1000 0000 0007 Technical issue during user authentication.
 *  4970 1000 0000 0097 Payment refused due to 3D-Secure authentication failure (user failed to authenticate).
 *  4970 1000 0000 0098 Payment refused (user credit limit overrun).
 *  4970 1000 0000 0099 Payment refused (invalid credit card visual cryptogramm).
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
        // Improvement: number of digits after the coma might depend from the currency.
        $this->val = 100 * strtr(sprintf("%.02f", (float)$val), '.', ',');
    }

    // }}}
    // {{{ function form()

    private static function replaceNonAlpha($string)
    {
        return trim(preg_replace('/\s\s+/', ' ', preg_replace('/[^a-zA-Z0-9]/', ' ', $string)));
    }

    function prepareform($pay, $user)
    {
        global $globals, $platal;
        $log = S::v('log');

        // Transaction's reference computation.
        $prefix = ($pay->flags->hasflag('unique')) ? str_pad("",15,"0") : rand_url_id();
        $fullref = substr("$prefix-{$pay->id}",-12); // FIXME : check for duplicates
        $ts = new DateTime('now', new DateTimeZone('UTC'));
        $trans_date = $ts->format("YmdHis");
        $trans_id = $ts->format("His"); // FIXME : check for duplicates

        // Form's content.
        $this->urlform = "https://systempay.cyberpluspaiement.com/vads-payment/";
        $this->infos['commercant'] = Array(
            'vads_site_id' => $globals->money->cyperplus_account,
            'vads_return_mode' => 'NONE',
            'vads_url_return' => $pay->url ? $pay->url : $globals->baseurl . '/' . $platal->ns);
        $this->infos['client'] = Array(
            'vads_cust_email' => $user->bestEmail(),
            'vads_cust_id' => $user->id(),
            'vads_cust_name' => substr(self::replaceNonAlpha(replace_accent($user->shortName())), 0, 127));
        $this->infos['commande'] = Array(
            'vads_amount' => $this->val,
            'vads_currency' => '978', # Euro
            'vads_payment_config' => 'SINGLE',
            'vads_trans_date' => $trans_date,
            'vads_trans_id' => $trans_id,
            'vads_order_id' => $fullref,
            'vads_order_info' => substr(self::replaceNonAlpha(replace_accent(Env::v('comment'))), 0, 255),
            'vads_order_info2' => Post::i('display'));
        $this->infos['divers'] = Array(
            'vads_version' => 'V2',
            'vads_ctx_mode' => $globals->money->cyperplus_prod,
            'vads_page_action' => 'PAYMENT',
            'vads_action_mode' => 'INTERACTIVE');

        // Entry key computation.
        $all_params = array_merge($this->infos['commercant'],$this->infos['client'],$this->infos['commande'],$this->infos['divers']);
        ksort($all_params);
        $this->infos['divers']['signature'] = sha1(join('+',$all_params).'+'.$globals->money->cyperplus_key);
    }

    // }}}
}

$api = 'BPLCCyberPlus';

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
