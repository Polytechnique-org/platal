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
        $Id: panel.php,v 1.5 2004/11/22 20:04:38 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
new_skinned_page('carnet/panel.tpl', AUTH_COOKIE);
require_once('notifs.inc.php');

if(isset($_GET['read'])) {
    $_SESSION['watch_last'] = $_GET['read'];
    header("Location: panel.php");
}

$page->assign('now',date('YmdHis'));
$notifs = new Notifs($_SESSION['uid'], true);

$page->assign_by_ref('notifs', $notifs);

$page->run();

?>
