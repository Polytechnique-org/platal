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
 ***************************************************************************
        $Id: assign_general.inc.php,v 1.2 2004/09/02 23:25:30 x2000habouzit Exp $
 ***************************************************************************/

$page->assign('mobile_public',$mobile_public);
$page->assign('mobile_ax',$mobile_ax);
$page->assign('libre_public',$libre_public);
$page->assign('web_public',$web_public);

$page->assign('nom', $nom);
$page->assign('prenom', $prenom);
$page->assign('promo', $promo);
$page->assign('epouse', $epouse);
$page->assign('femme', $femme);

$page->assign('nationalite',$nationalite);

$page->assign('mobile',$mobile);

$page->assign('web',$web);

$page->assign('libre',$libre);

$page->assign('appli_id1',$appli_id1);
$page->assign('appli_id2',$appli_id2);
$page->assign('appli_type1',$appli_type1);
$page->assign('appli_type2',$appli_type2);

?>
