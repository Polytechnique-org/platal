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
        $Id: xorg.common.inc.php,v 1.6 2004-11-21 20:56:26 x2000habouzit Exp $
 ***************************************************************************/

$i=0;
define("AUTH_PUBLIC", $i++);
define("AUTH_COOKIE", $i++);
define("AUTH_MDP", $i++);

define("PERMS_USER", "user");
define("PERMS_ADMIN", "admin");

define('SKIN_COMPATIBLE','default.tpl');
define('SKIN_COMPATIBLE_ID',1);

define('SKINNED', 0);
define('NO_SKIN', 1);

// import class definitions
require("diogenes.database.inc.php");
require("xorg.globals.inc.php");
require("xorg.session.inc.php");

$globals = new XorgGlobals;
require("config.xorg.inc.php");

session_start();

// connect to database
$globals->dbconnect();
$conn = $globals->db->connect_id;
if($site_dev)
    $globals->db->trace_on();
?>
