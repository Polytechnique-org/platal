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
new_skinned_page('paiment/index.tpl', AUTH_MDP);
require_once('profil.func.inc.php');
require_once('money.inc.php');

// initialisation
$op = isset($_REQUEST['op']) ? $_REQUEST['op'] : 'select';

$meth = new PayMethod(isset($_REQUEST['methode']) ? $_REQUEST['methode'] : -1);
$pay  = new Payment(isset($_REQUEST['ref']) ? $_REQUEST['ref'] : -1);

if($pay->flags->hasflag('old')){
    $page->trigger("La transaction selectionnée est périmée.");
    $pay = new Payment();
}
$val  = (($op=="submit") && isset($_REQUEST['montant'])) ? $_REQUEST['montant'] : $pay->montant_def;

if (($e = $pay->check($val)) !== true) {
    $page->trigger($e);
}

if ($op=='submit') {
    $pay->init($val, $meth);
}

$page->assign('montant',$val);

$page->assign('meth', $meth);
$page->assign('pay',  $pay);

$page->assign('prefix',$globals->money->mpay_tprefix);
$page->run();
?>
