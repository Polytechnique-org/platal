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
        $Id: notifs.php,v 1.4 2004-11-05 14:34:04 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('carnet/notifs.tpl', AUTH_COOKIE);
require('notifs.inc.php');

$notifs = new Notifs($_SESSION['uid']);

$err = Array();

foreach($_REQUEST as $key=>$val) {
    switch($key) {
	case 'add_promo':
	    $p = intval($val);
	    if(($p<1900) || ($p>2100)) {
		$err[] = "il faut entrer une promo sur 4 chiffres";
	    } else {
		$notifs->add_promo($val);
	    };
	    break;

	case 'del_promo':
	    $notifs->del_promo($val);
	    break;

	case 'add_nonins':
	    $notifs->add_nonins($val);
	    break;

	case 'del_nonins':
	    $notifs->del_nonins($val);
	    break;

	case 'flags':
	    $flags = new FlagSet();
	    if(isset($_REQUEST['contacts'])) $flags->addflag('contacts');
	    if(isset($_REQUEST['deaths'])) $flags->addflag('deaths');
	    $notifs->flags = $flags;
	    $notifs->saveFlags();
	    break;
    }
}
$page->assign_by_ref('notifs', $notifs);

$page->run();

?>
