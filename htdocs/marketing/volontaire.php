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
 ***************************************************************************/

require_once("xorg.inc.php");
new_admin_page('marketing/volontaire.tpl');

// traitement des paramètres éventuels
if (!empty($_GET["del"])) {
    $globals->db->query("DELETE FROM marketing WHERE id ='{$_GET['del']}'");
    $page->trigger("Entrée effacée");
}
if (!empty($_GET["done"])) {
    $globals->db->query("UPDATE marketing SET flags = CONCAT(flags,',envoye') WHERE id ='{$_GET['done']}'");
    $page->trigger("Entrée mise à jour");
}

$sql = "SELECT  m.id, m.expe, m.dest, m.email, 
		i.promo, i.nom, i.prenom, i.last_known_email, 
		u.promo AS spromo, u.nom AS snom, u.prenom AS sprenom, a.alias AS forlife,
                FIND_IN_SET('mail_perso', m.flags) AS mailperso
          FROM  marketing     AS m
    INNER JOIN  auth_user_md5 AS i  ON i.matricule = m.dest
    INNER JOIN  auth_user_md5 AS u ON u.user_id = m.expe
    INNER JOIN  aliases       AS a ON (u.user_id = a.id AND a.type='a_vie')
         WHERE  NOT FIND_IN_SET('envoye', m.flags)";

$page->mysql_assign($sql, 'neuves');


$sql = "SELECT  a.promo, a.nom, a.prenom,
                m.email, a.perms!='pending' AS inscrit,
                sa.promo AS spromo, sa.nom AS snom, sa.prenom AS sprenom
          FROM  marketing     AS m
    INNER JOIN  auth_user_md5 AS a  ON a.matricule = m.dest
    INNER JOIN  auth_user_md5 AS sa ON sa.user_id = m.expe
         WHERE  FIND_IN_SET('envoye', m.flags)";

$page->mysql_assign($sql, 'used', 'nbused');

$sql = "SELECT  COUNT(a.perms != 'pending') AS j,
                COUNT(i.matricule) AS i,
                100 * COUNT(a.nom) / COUNT(i.matricule) as rate
          FROM  marketing     AS m
    INNER JOIN  auth_user_md5 AS i  ON i.matricule = m.dest
    INNER JOIN  auth_user_md5 AS sa ON sa.user_id = m.expe
    LEFT  JOIN  auth_user_md5 AS a  ON (a.matricule = m.dest AND a.perms!='pending')
         WHERE  FIND_IN_SET('envoye', m.flags)";
$res = $globals->db->query($sql);

$page->assign('rate', mysql_fetch_assoc($res));
mysql_free_result($res);

$page->run();
?>
