<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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

require_once("xorg.inc.php");
new_nonhtml_page('vcard.tpl', AUTH_COOKIE);
require_once("xorg.misc.inc.php");
require_once("user.func.inc.php");

function format_adr($params, &$smarty)
{
    // $adr1, $adr2, $adr3, $postcode, $city, $region, $country
    extract($params['adr']);
    $adr = $adr1;
    $adr = trim("$adr\n$adr2");
    $adr = trim("$adr\n$adr3");
    return quoted_printable_encode(";;$adr;$city;$region;$postcode;$country");
}

$page->register_modifier('qp_enc', 'quoted_printable_encode');
$page->register_function('format_adr', 'format_adr');

$login = get_user_forlife(Env::get('x'));
$user  = get_user_details($login);

// alias virtual
$res = $globals->xdb->query(
	"SELECT alias
	   FROM virtual
     INNER JOIN virtual_redirect USING(vid)
     INNER JOIN auth_user_quick  ON ( user_id = {?} AND emails_alias_pub = 'public' )
     	  WHERE ( redirect={?} OR redirect={?} )
	        AND alias LIKE '%@{$globals->mail->alias_dom}'",
        Session::getInt('uid'), $user['forlife'].'@'.$globals->mail->domain, $user['forlife'].'@'.$globals->mail->domain2);
$user['virtualalias'] = $res->fetchOneCell();

$page->assign_by_ref('vcard', $user);

header("Pragma: ");
header("Cache-Control: ");
header("Content-type: text/x-vcard\n");
header("Content-Transfer-Encoding: Quoted-Printable\n");

$page->run();
?>
