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
        $Id: antispam.php,v 1.8 2004-09-04 14:40:02 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('antispam.tpl', AUTH_MDP);

require("mtic.inc.php");

if (isset($_REQUEST['filtre']) and isset($_REQUEST['statut_filtre'])) {
    
    $new_filter = intval($_REQUEST['statut_filtre']);
    
    $res = $globals->db->query("SELECT COUNT(*) FROM emails WHERE uid={$_SESSION['uid']} AND find_in_set('filter', flags)");
    list($was_filter) = mysql_fetch_row($res);
    mysql_free_result($res);

    if ($new_filter == 0 && $was_filter) {
	$globals->db->query("DELETE FROM emails WHERE uid = {$_SESSION['uid']} AND find_in_set('filter', flags)");
    }

    if ($new_filter != 0) {
	$pipe = $new_filter == 2 ? 'drop_spams' : 'tag_spams';
	if($was_filter) {
	    $globals->db->query("UPDATE  emails
				    SET  email = '\"|maildrop /var/mail/.maildrop_filters/$pipe {$_SESSION['uid']}\"'
				  WHERE  uid = {$_SESSION['uid']} AND flags = 'filter'");
	} else {
	    $globals->db->query("INSERT INTO emails
	                                 SET uid = {$_SESSION['uid']},
				             email = '\"|maildrop /var/mail/.maildrop_filters/$pipe {$_SESSION['uid']}\"',
					     flags = 'filter'");
	}
    }
}

$result = $globals->db->query("SELECT email LIKE '%drop_spams%'
				 FROM emails
				WHERE uid = {$_SESSION['uid']} AND find_in_set('filter', flags)");
if(mysql_num_rows($result)) {
    list($n) = mysql_fetch_row($result);
    $page->assign('filtre',intval($n)+1);
} else {
    $page->assign('filtre',0);
}
mysql_free_result($result);


$page->run();
?>
