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
        $Id: step2.php,v 1.4 2004/11/22 20:04:41 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
require_once("identification.inc.php");

new_skinned_page('inscription/step2.tpl', AUTH_PUBLIC);

require_once("applis.func.inc.php");

$page->assign('homonyme', $homonyme);
$page->assign('forlife',  $forlife);
$page->assign('mailorg',  $mailorg);
$page->assign('prenom',   $prenom);
$page->assign('nom',      $nom);

$page->run();
?>
