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
        $Id: emails.php,v 1.5 2004-09-02 22:27:05 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('emails.tpl',AUTH_COOKIE);

// on regarde si on a affaire à un homonyme
$sql = "SELECT  alias, (type='a_vie') AS a_vie
          FROM  aliases
         WHERE  id='{$_SESSION['uid']}'
      ORDER BY  type!='a_vie'";
$page->mysql_assign($sql, 'aliases');

$sql = "SELECT email
        FROM emails
        WHERE uid = {$_SESSION["uid"]} AND num != 0 AND (FIND_IN_SET('active', flags) OR FIND_IN_SET('filtre', flags))";
$page->mysql_assign($sql, 'mails', 'nb_mails');


// on regarde si l'utilisateur a un alias et si oui on l'affiche !
$sql = "SELECT domain FROM groupex.aliases WHERE id=12 AND email like '".$_SESSION['forlife']."'";
$result = $globals->db->query($sql);
if ($result && list($aliases) = mysql_fetch_row($result))
    $page->assign('melix', substr($aliases,0,-3));
mysql_free_result($result);

$page->run();
?> 
