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
        $Id: insert.mkStats.php,v 1.12 2004-11-21 23:10:47 x2000habouzit Exp $
 ***************************************************************************/


/*
 * Smarty plugin
 * ------------------------------------------------------------- 
 * File:     insert.mkStats.php
 * Type:     insert
 * Name:     mkStats
 * Purpose:  
 * -------------------------------------------------------------
 */
function smarty_insert_mkStats($params, &$smarty)
{
    $req = mysql_query("select count(*) from requests");
    list($stats_req) = mysql_fetch_row($req);
    mysql_free_result($req);
    return ($stats_req ? $stats_req : "-");
}
?>
