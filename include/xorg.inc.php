<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

define('PL_CLASS', 'Xorg');
define('PL_GLOBALS_CLASS', 'PlatalGlobals');
define('PL_LOGGER_CLASS', 'PlatalLogger');
define('PL_SESSION_CLASS', 'XorgGroupeXSession');
define('PL_PAGE_CLASS', 'XorgPage');

require_once dirname(dirname(__FILE__)) . '/core/include/platal.inc.php';
require_once 'security.inc.php';
require_once 'common.inc.php';

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
