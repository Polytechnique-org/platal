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
        $Id: insert.getNbIns.php,v 1.5 2004-10-31 15:54:45 x2000chevalier Exp $
 ***************************************************************************/

/*
 * Smarty plugin
 * ------------------------------------------------------------- 
 * File:     insert.getNbIns.php
 * Type:     insert
 * Name:     getNbIns
 * Purpose:  
 * -------------------------------------------------------------
 */
function smarty_insert_getNbIns($params, &$smarty)
{
    global $conn;
    $result=mysql_query("SELECT COUNT(*) FROM auth_user_md5 WHERE deces = 0 AND perms != 'non-inscrit'",$conn);
    list($stats_count)=mysql_fetch_row($result);
    mysql_free_result($result);
    return number_format($stats_count, 0, ",", ".");
}
?>
