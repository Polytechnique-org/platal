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

$page->assign('mobile_pub',$mobile_pub);
$page->assign('web_pub',$web_pub);
$page->assign('freetext_pub',$freetext_pub);

$page->assign('nom', $nom);
$page->assign('prenom', $prenom);
$page->assign('promo', $promo);
$page->assign('promo_sortie', $promo_sortie);
$page->assign('nom_usage', $nom_usage);

$page->assign('nationalite',$nationalite);

$page->assign('mobile',$mobile);

$page->assign('web',$web);

$page->assign('freetext',$freetext);

$page->assign('appli_id1',$appli_id1);
$page->assign('appli_id2',$appli_id2);
$page->assign('appli_type1',$appli_type1);
$page->assign('appli_type2',$appli_type2);

$page->assign('photo_pub',$photo_pub);
$page->assign('nouvellephoto', $nouvellephoto);
$page->assign('nickname', $nickname);
?>
