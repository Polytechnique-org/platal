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
new_skinned_page('carnet/notifs.tpl', AUTH_COOKIE);
require_once('notifs.inc.php');

$watch = new Watch(Session::getInt('uid'));

if(Env::has('promo')) {
    if(preg_match('!^ *(\d{4}) *$!', Env::get('promo'), $matches)) {
	$p = intval($matches[1]);
	if($p<1900 || $p>2100) {
            $page->trig("la promo entrée est invalide");
	} else {
	    if (Env::has('add_promo')) $watch->_promos->add($p);
	    if (Env::has('del_promo')) $watch->_promos->del($p);
	}
    } elseif (preg_match('!^ *(\d{4}) *- *(\d{4}) *$!', Env::get('promo'), $matches)) {
	$p1 = intval($matches[1]);
	$p2 = intval($matches[2]);
	if($p1<1900 || $p1>2100) {
            $page->trig('la première promo de la plage entrée est invalide');
	} elseif($p2<1900 || $p2>2100) {
            $page->trig('la seconde promo de la plage entrée est invalide');
	} else {
	    if (Env::has('add_promo')) $watch->_promos->addRange($p1,$p2);
	    if (Env::has('del_promo')) $watch->_promos->delRange($p1,$p2);
	}
    } else {
        $page->trig("La promo (ou la plage de promo) entrée est dans un format incorrect.");
    }
}

if (Env::has('del_nonins')) $watch->_nonins->del(Env::get('del_nonins'));
if (Env::has('add_nonins')) $watch->_nonins->add(Env::get('add_nonins'));
if (Env::has('subs'))       $watch->_subs->update('sub');
if (Env::has('flags_contacts')) {
    $watch->watch_contacts = Env::getBool('contacts');
    $watch->saveFlags();
}
if (Env::has('flags_mail')) {
    $watch->watch_mail     = Env::getBool('mail');
    $watch->saveFlags();
}

$page->assign_by_ref('watch', $watch);
$page->run();

// vim:set et sws=4 sw=4 sts=4:
?>
