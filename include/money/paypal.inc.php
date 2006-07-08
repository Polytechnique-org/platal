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

class PayPal
{
    // {{{ properties

    var $val_number;
    var $urlform;
    var $nomsite = "PayPal";
    var $text;
    
    var $infos;

    // }}}
    // {{{ constructor
    
    function PayPal($val)
    {
    	$this->val_number = $val;
    }

    // }}}
    // {{{ function form()

    function prepareform(&$pay)
    {
    	// toute la doc sur :
	// https://www.paypal.com/fr_FR/pdf/integration_guide.pdf
	// attention : le renvoi automatique ne fonctionne que si
	// on oblige les gens à créer un compte paypal
	// nous ne l'utilisons pas ; il faut donc que l'utilisateur
	// revienne sur le site
        global $globals;

	$this->urlform = 'https://'.$globals->money->paypal_site.'/cgi-bin/webscr';
	$req = $globals->xdb->query("SELECT IF(nom_usage!='', nom_usage, nom) AS nom FROM auth_user_md5 WHERE user_id = {?}",Session::get('uid'));
	$name = $req->fetchOneCell();

        $roboturl = str_replace("https://","http://",$globals->baseurl)
            ."/payment/paypal_return/".Session::getInt('uid');

	$this->infos = Array();
	
	$this->infos['commercant'] = Array(
		'business'    => $globals->money->paypal_compte,
		'rm' 	      => 2,
		'return'      => $roboturl,
		'cn'	      => 'Commentaires',
		'no_shipping' => 1,
		'cbt'         => 'Revenir sur polytechnique.org');
	
	$info_client = Array(
		'first_name' => Session::get('prenom'),
		'last_name'  => $name,
		'email'      => Session::get('bestalias').'@polytechnique.org');
		
	$res = $globals->xdb->query(
		"SELECT a.adr1 AS address1, a.adr2 AS address2,
			a.city, a.postcode AS zip, a.country,
			IF(t.tel, t.tel, q.profile_mobile) AS night_phone_b
		   FROM auth_user_quick AS q
	      LEFT JOIN adresses	AS a ON (q.user_id = a.uid)
	      LEFT JOIN tels        AS t ON (t.uid = a.uid AND t.adrid = a.adrid)
	          WHERE q.user_id = {?} AND FIND_IN_SET('active', a.statut)
		  LIMIT 1", Session::getInt('uid'));
	$this->infos['client']=array_merge($info_client, $res->fetchOneAssoc());

        // on constuit la reference de la transaction
        $prefix = ($pay->flags->hasflag('unique')) ? str_pad("",15,"0") : rand_url_id();
        $fullref = substr("$prefix-xorg-{$pay->id}",-15);

	$this->infos['commande'] = Array(
		'item_name'	=> $pay->text,
		'amount'	=> $this->val_number,
		'currency_code' => 'EUR',
		'custom'	=> $fullref);

	$this->infos['divers'] = Array('cmd' => '_xclick');

    }

    // }}}
}

$api = 'PayPal';

?>
