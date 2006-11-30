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

class CyberPayment
{
    // {{{ properties

    var $val;

    var $urlform;
    var $nomsite = "la BP Lorraine Champagne";
    var $infos;

    // }}}
    // {{{ constructor
    
    function CyberPayment($val)
    {
        $this->val = strtr(sprintf("%.02f", (float)$val), '.', ',');
    }

    // }}}
    // {{{ function form()

    function prepareform(&$pay)
    {
    	// toute la doc se trouve sur
    	// http://www.cyberpaiement.tm.fr/donnees.htm

        global $globals, $platal;

        $roboturl = str_replace("https://","http://", $globals->baseurl)
            . '/' . $platal->ns . "payment/cyber_return/".S::v('uid')."?comment=".urlencode(Env::v('comment'))."&CHAMPBPX";
        $req = XDB::query("SELECT IF(nom_usage!='', nom_usage, nom) AS nom
                             FROM auth_user_md5
                            WHERE user_id = {?}",S::v('uid'));
    	$name = $req->fetchOneCell();

        // on constuit la reference de la transaction
        $prefix = ($pay->flags->hasflag('unique')) ? str_pad("",15,"0") : rand_url_id();
        $fullref = substr("$prefix-xorg-{$pay->id}",-15);

        $this->urlform = "https://ecom.cimetz.com/telepaie/cgishell.exe/epaie01.exe";
    	$this->infos['commercant'] = Array(
    		'CHAMP000' => 510879,
    		'CHAMP001' => 5965,
    		'CHAMP002' => 5429159012,
    		'CHAMP003' => "I",
    		'CHAMP004' => "Polytechnique.org",
    		'CHAMP005' => $roboturl,
    		'CHAMP006' => "Polytechnique.org",
    		'CHAMP007' => $globals->baseurl . '/' . $platal->ns,
    		'CHAMP008' => $pay->mail);
    	$this->infos['client'] = Array(
    		'CHAMP100' => $name,
    		'CHAMP101' => S::v('prenom'),
    		'CHAMP102' => '.',
    		'CHAMP103' => '.',
    		'CHAMP104' => S::v('bestalias').'@polytechnique.org',
    		'CHAMP106' => '.',
    		'CHAMP107' => '.',
    		'CHAMP108' => '.',
    		'CHAMP109' => '.',
    		'CHAMP110' => '.');
    	$this->infos['commande'] = Array(
    		'CHAMP200' => $fullref,
    		'CHAMP201' => $this->val,
    		'CHAMP202' => "EUR");
    	$this->infos['divers'] = Array('CHAMP900' => '01');
    }

    // }}}
}

$api = 'CyberPayment';

?>
