<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

$GLOBALS['IS_XNET_SITE'] = true;

require_once dirname(__FILE__).'/../include/xnet.inc.php';

$platal = new Xnet('xnet', 'xnetgrp', 'xnetlists', 'xnetevents', 'geoloc', 'payment', 'bandeau');
if (!($path = Env::v('n')) || substr($path, 0, 4) != 'Xnet') {
    $platal->run();
    exit;
}

/*** WIKI CODE ***/

include dirname(__FILE__) . '/../core/include/wiki.engine.inc.php'; 

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
