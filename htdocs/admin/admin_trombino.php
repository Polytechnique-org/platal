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
        $Id: admin_trombino.php,v 1.5 2004/11/22 20:04:36 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
new_admin_page('admin/admin_trombino.tpl');

$q = $globals->db->query("SELECT  a.alias,promo
			    FROM  auth_user_md5 AS u
		      INNER JOIN  aliases       AS a ON ( u.user_id = a.id AND type='a_vie' )
			   WHERE  user_id = '{$_REQUEST['uid']}'");
list($forlife, $promo) = mysql_fetch_row($q);

if (isset($_REQUEST["action"])) {
    switch ($_REQUEST["action"]) {

    case "ecole":
        header("Content-type: image/jpeg");
	readfile("/home/web/trombino/photos".$promo."/".$forlife.".jpg");
        exit;
	break;

    case "valider":
        $handle = fopen ($_FILES['userfile']['tmp_name'], "r");
	$data = fread ($handle, filesize ($_FILES['userfile']['tmp_name']));
	fclose ($handle);
	list($x, $y) = getimagesize($_FILES['userfile']['tmp_name']);
	$mimetype = substr($_FILES['userfile']['type'], 6);
	unlink($_FILES['userfile']['tmp_name']);
        $globals->db->query(
	  "REPLACE INTO photo
	   SET uid='".$_REQUEST["uid"]."',
	   attachmime = '".$mimetype."',
	   attach='".addslashes($data)."',
	   x='".$x."', y='".$y."'");
    	break;

    case "supprimer":
        $globals->db->query("DELETE FROM photo WHERE uid = '".$_REQUEST["uid"]."'");
        break;
    }
}

$page->assign('forlife', $forlife);
$page->run();
?>
