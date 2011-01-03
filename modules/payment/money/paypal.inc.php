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

class PayPal
{
    var $val_number;
    var $urlform;
    var $nomsite = "PayPal";
    var $text;

    var $infos;

    function PayPal($val)
    {
        $this->val_number = $val;
    }

    function prepareform(&$pay)
    {
        // Documentation:
        // https://www.paypal.com/developer
        // Warning: the automatic return only works if we force the
        // users to create a paypal account. We do not use it; thus
        // the user must come back on the site.
        global $globals, $platal;

        $this->urlform = 'https://' . $globals->money->paypal_site . '/cgi-bin/webscr';
        $user = S::user();

        $roboturl = str_replace("https://","http://",$globals->baseurl)
                  . '/' . $platal->ns . "payment/paypal_return/" . S::v('uid')
                  . "?comment=" . urlencode(Env::v('comment'));

        $this->infos = array(
            'commercant' => array(
                'business'    => $globals->money->paypal_compte,
                'rm'          => 2,
                'return'      => $roboturl,
                'cn'          => 'Commentaires',
                'no_shipping' => 1,
                'cbt'         => empty($GLOBALS['IS_XNET_SITE']) ?  'Revenir sur polytechnique.org.' : 'Revenir sur polytechnique.net.'
            )
        );

        $info_client = array(
            'first_name' => $user->firstName(),
            'last_name'  => $user->lastName(),
            'email'      => $user->bestEmail()
        );

        $res = XDB::query("SELECT  pa.text, gl.name AS city, pa.postalCode AS zip, pa.countryId AS country,
                                   IF(pp1.display_tel != '', pp1.display_tel, pp2.display_tel) AS night_phone_b
                             FROM  profile_addresses AS pa
                        LEFT JOIN  profile_phones    AS pp1 ON (pp1.pid = pa.pid AND pp1.link_type = 'address'
                                                                AND pp1.link_id = pa.id)
                        LEFT JOIN  profile_phones    AS pp2 ON (pp2.pid = pa.pid AND pp2.link_type = 'user'
                                                                AND pp2.link_id = 0)
                        LEFT JOIN  geoloc_localities AS gl  ON (gl.id = pa.localityId)
                            WHERE  pa.pid = {?} AND FIND_IN_SET('current', pa.flags)
                            LIMIT  1",
                          $user->profile()->id());
        $this->infos['client'] = array_map('replace_accent', array_merge($info_client, $res->fetchOneAssoc()));
        list($this->infos['client']['address1'], $this->infos['client']['address2']) =
            explode("\n", Geocoder::getFirstLines($this->infos['client']['text'],
                                                  $this->infos['client']['zip'], 2));
        unset($this->infos['client']['text']);

        // We build the transaction's reference
        $prefix = ($pay->flags->hasflag('unique')) ? str_pad("", 15, "0") : rand_url_id();
        $fullref = substr("$prefix-xorg-{$pay->id}", -15);

        $this->infos['commande'] = array(
            'item_name'     => replace_accent($pay->text),
            'amount'        => $this->val_number,
            'currency_code' => 'EUR',
            'custom'        => $fullref
        );

        $this->infos['divers'] = array('cmd' => '_xclick');
    }
}

$api = 'PayPal';

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
