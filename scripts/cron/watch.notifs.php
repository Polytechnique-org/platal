#!/usr/bin/php4 -q
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
        $Id: watch.notifs.php,v 1.1 2004-11-04 20:27:31 x2000habouzit Exp $
 ***************************************************************************/
/*
 * verifie qu'il n'y a pas d'incoherences dans les tables de jointures
 * 
 * $Id: watch.notifs.php,v 1.1 2004-11-04 20:27:31 x2000habouzit Exp $
*/ 

require('./connect.db.inc.php');

mysql_query("LOCK TABLE watch_ops");

// be smart here

mysql_query("DELETE FROM watch_ops");
mysql_query("UNLOCK TABLE watch_ops");

// send 10238 mails here

?>
