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
        $name = $user->lastName();

        $roboturl = str_replace("https://","http://",$globals->baseurl)
                  . '/' . $platal->ns . "payment/paypal_return/" . S::v('uid')
                  . "?comment=" . urlencode(Env::v('comment'));

        $this->infos = array();

        $this->infos['commercant'] = array(
            'business'    => $globals->money->paypal_compte,
            'rm'          => 2,
            'return'      => $roboturl,
            'cn'          => 'Commentaires',
            'no_shipping' => 1,
            'cbt'         => empty($GLOBALS['IS_XNET_SITE']) ?
            'Revenir sur polytechnique.org.' :
            'Revenir sur polytechnique.net.'
        );

        $info_client = array(
            'first_name' => S::v('prenom'),
            'last_name'  => $name,
            'email'      => S::user()->bestEmail()
        );

        // XXX: waiting for port of adresses.
        $res = XDB::query(
                "SELECT  a.text, l.name AS city, a.postalCode AS zip, a.countryiId AS country,
                         IF(t1.display_tel != '', t1.display_tel, t2.display_tel) AS night_phone_b
                   FROM  auth_user_quick   AS q
              LEFT JOIN  profile_addresses AS a  ON (q.user_id = a.pid AND FIND_IN_SET('current', a.flags))
              LEFT JOIN  profile_phones    AS t1 ON (t1.uid = a.uid AND t1.link_type = 'address'
                                                     AND t1.link_id = a.adrid)
              LEFT JOIN  profile_phones    AS t2 ON (t2.uid = a.uid AND t2.link_type = 'user'
                                                     AND t2.link_id = 0)
              LEFT JOIN  geoloc_localities AS l  ON (l.id = a.localityId)
                  WHERE  q.user_id = {?}
                  LIMIT  1",
                S::v('uid'));
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
