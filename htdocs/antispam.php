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
        $Id: antispam.php,v 1.10 2004-11-03 15:55:22 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('antispam.tpl', AUTH_MDP);
require("email.classes.inc.php");

$bogo = new Bogo($_SESSION['uid']);

if (isset($_REQUEST['filtre']) and isset($_REQUEST['statut_filtre'])) {
    $bogo->change($_SESSION['uid'], intval($_REQUEST['statut_filtre']));
}

$page->assign('filtre',$bogo->level());

$page->run();
?>
