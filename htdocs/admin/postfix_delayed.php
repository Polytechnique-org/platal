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
        $Id: postfix_delayed.php,v 1.1 2004-10-08 12:04:04 x2000habouzit Exp $
 ***************************************************************************/

require('auto.prepend.inc.php');
new_admin_page('admin/postfix_delayed.tpl');


if (isset($_REQUEST["del"])) {
    mysql_query("UPDATE postfix_mailseen SET release = 'del' WHERE crc = '".$_REQUEST["crc"]."'");
    $page->assign('res', $_REQUEST["crc"]." verra tous ses mails supprimés !");
} else if (isset($_REQUEST["ok"])) {
    mysql_query("UPDATE postfix_mailseen SET release = 'ok' WHERE crc = '".$_REQUEST["crc"]."'");
    $page->assign('res', $_REQUEST["crc"]." a le droit de passer !");
}

$sql = "SELECT  crc, nb, update_time, create_time,
	        FIND_IN_SET('del', release) AS del,
	        FIND_IN_SET('ok', release) AS ok
	  FROM  postfix_mailseen
	 WHERE  nb >= 30
      ORDER BY  release != ''";

$page->mysql_assign($sql,'mails');

$page->run();
?>
