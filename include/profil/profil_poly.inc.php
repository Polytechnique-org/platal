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
        $Id: profil_poly.inc.php,v 1.3 2004-08-31 13:59:43 x2000habouzit Exp $
 ***************************************************************************/


//declaration des fonctions msarty pour les binets et groupex

function _print_binet_smarty($params){
  if(!isset($params['uid'])) return;
  $result = $globals->db->query("select * from binets_ins, binets_def "
    ."where binets_def.id=binets_ins.binet_id and user_id='{$params['uid']}'");
  while ($myrow2=mysql_fetch_array($result)) { ?>
          <span class="valeur"><?php echo $myrow2['text'];?></span>
        </td>
        <td class="cold">
	  <span class="lien">
	  <a href="javascript:binet_del(<?php echo $myrow2['id']; ?>);">retirer
	  </a></span>
        </td>
      </tr>
      <tr>
        <td class="colg">
	  &nbsp;
        </td>
        <td class="colm">
<?php
  }  
  mysql_free_result($result);
}
$page->register_function('print_binets','_print_binet_smarty');

function _print_groupex_smarty($params){
  if(!isset($params['uid'])) return;
  $result = $globals->db->query("select * from groupesx_ins, groupesx_def where groupesx_def.id=groupesx_ins.gid and guid='{$params['uid']}'");
  while ($myrow2=mysql_fetch_array($result)) {
  ?>
	<td class="colm">
	  <span class="valeur"><?php echo $myrow2['text']; ?></span>
        </td>
        <td class="cold">
	  <span class="lien">
	  <a href="javascript:groupex_del(<?php echo $myrow2['id']; ?>);">
	  retirer</a></span>
        </td>
      </tr>
      <tr>
        <td class="colg">
        </td>
  <?php
  }
  mysql_free_result($result);
}
$page->register_function('print_groupex','_print_groupex_smarty');


$sql = "SELECT u.nom, u.prenom".
	", u.promo, epouse, i.flags, section".
	" FROM auth_user_md5 AS u".
	" LEFT  JOIN identification AS i ON(u.matricule = i.matricule) ".
	" WHERE user_id=".$_SESSION['uid'];

$result = $globals->db->query($sql);
list($nom, $prenom,
     $promo, $epouse, $flags, $section) = mysql_fetch_row($result);

//TODO : ne plus afficher directement les erreurs mysql...
//if(mysql_errno($conn) !=0) echo mysql_errno($conn).": ".mysql_error($conn);

replace_ifset($section,"section");
$page->assign_by_ref('section', $section);

/************* gestion des binets ************/
if (isset($_REQUEST['binet_op']) && !$no_update_bd) {
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
if (isset($_REQUEST['groupex_op']) && !$no_update_bd) {
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
