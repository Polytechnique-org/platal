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
        $Id: get_poly.inc.php,v 1.2 2004-09-01 21:55:32 x2000habouzit Exp $
 ***************************************************************************/


//declaration des fonctions msarty pour les binets et groupex

$sql = "SELECT  u.nom, u.prenom, u.promo, a.alias as epouse, i.flags, section
          FROM  auth_user_md5  AS u
     LEFT JOIN  identification AS i ON(u.matricule = i.matricule)
     LEFT JOIN  aliases	       AS a ON(u.user_id = a.id AND type='epouse')
         WHERE  user_id=".$_SESSION['uid'];

$result = $globals->db->query($sql);
list($nom, $prenom, $promo, $epouse, $flags, $section) = mysql_fetch_row($result);

replace_ifset($section,'section');

/************* gestion des binets ************/
if (isset($_REQUEST['binet_op'])) {
    // retrait binet
    if($_REQUEST['binet_op']=="retirer" && !empty($_REQUEST['binet_id'])) {
        $globals->db->query("delete from binets_ins where user_id='{$_SESSION['uid']}' and binet_id='{$_REQUEST['binet_id']}'");
    }
    // ajout binet
    if ($_REQUEST['binet_op']=="ajouter" && !empty($_REQUEST['binet_id'])) {
        $globals->db->query("insert into binets_ins (user_id,binet_id) VALUES('{$_SESSION['uid']}','{$_REQUEST['binet_id']}')");
    }
}
/************* gestion des groupes X ************/
if (isset($_REQUEST['groupex_op'])) {
    // retrait groupe X
    if ($_REQUEST['groupex_op']=="retirer" && !empty($_REQUEST['groupex_id'])) {
        $globals->db->query("delete from groupesx_ins where guid='{$_SESSION['uid']}' and gid='{$_REQUEST['groupex_id']}'");
    }
    // ajout groupe X
    if ($_REQUEST['groupex_op']=="ajouter" && !empty($_REQUEST['groupex_id'])) {
        $globals->db->query("insert into groupesx_ins (guid,gid) VALUES('{$_SESSION['uid']}','{$_REQUEST['groupex_id']}')");
    }
}

?>
