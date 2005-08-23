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
require_once("validations.inc.php");
require_once("xorg.misc.inc.php");

new_skinned_page('orange.tpl', AUTH_MDP);

$res = $globals->xdb->query(
        "SELECT  u.promo,u.promo_sortie
           FROM  auth_user_md5  AS u
          WHERE  user_id={?}", Session::getInt('uid'));

list($promo,$promo_sortie_old) = $res->fetchOneRow();
$page->assign('promo_sortie_old', $promo_sortie_old);
$page->assign('promo',  $promo);

$promo_sortie = Env::get('promo_sortie');

if ($promo_sortie) {
	$sortie_req = false;
	if (!is_numeric($promo_sortie) || $promo_sortie < 1000 || $promo_sortie > 9999)
	$page->trig('L\'année de sortie doit être un nombre de quatre chiffres');
	elseif ($promo_sortie < $promo + 3)
	$page->trig('Trop tôt');
	elseif ($promo_sortie == $promo_sortie_old)
	$page->trig('Tu appartiens déjà à la promotion correspondante à cette année de sortie.');
	elseif ($promo_sortie == $promo + 3)
	{
	$globals->xdb->execute(
	"UPDATE auth_user_md5 set promo_sortie={?} 
	WHERE user_id={?}",$promo_sortie,Session::getInt('uid'));
	$page->trig('Ton statut "orange" a été supprimé.');
	$page->assign('promo_sortie_old', $promo_sortie);
	}
	else 
	$page->assign('promo_sortie', $sortie_req = $promo_sortie);

	if (Env::has('submit') && $sortie_req && ($promo_sortie_old != $sortie_req && $promo_sortie != $promo + 3)) {
		$myorange = new OrangeReq(Session::getInt('uid'), $sortie_req);
		$myorange->submit();
		$page->assign('myorange', $myorange);
	}
}

$page->run();
?>
