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
        $Id: index.php,v 1.2 2004-08-31 10:03:30 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('marketing/index.tpl');

# Quelques statistiques

$sql = "SELECT count(*) as vivants,
	       count(u.matricule) as inscrits,
	       100*count(u.matricule)/count(*) as ins_rate,
	       count(NULLIF(i.promo >= 1972, 0)) as vivants72,
	       count(NULLIF(i.promo >= 1972 AND u.matricule, 0)) as inscrits72,
	       100 * count(NULLIF(i.promo >= 1972 AND u.matricule, 0)) /
                   count(NULLIF(i.promo >= 1972, 0)) as ins72_rate,
	       count(NULLIF(FIND_IN_SET('femme', i.flags), 0)) as vivantes,
	       count(NULLIF(FIND_IN_SET('femme', i.flags) AND u.matricule, 0)) as inscrites,
	       100 * count(NULLIF(FIND_IN_SET('femme', i.flags) AND u.matricule, 0)) /
		   count(NULLIF(FIND_IN_SET('femme', i.flags), 0)) as inse_rate
          FROM identification as i
     LEFT JOIN auth_user_md5 as u USING(matricule)
         WHERE i.deces = 0";
$res = $globals->db->query($sql);
$stats = mysql_fetch_assoc($res);

$page->assign('stats', $stats);
mysql_free_result($res);

$res = $globals->db->query("SELECT count(*) FROM ins_confirmees");
list($nbInsSem) = mysql_fetch_row($res);
mysql_free_result($res);

$page->assign('nbInsSem', $nbInsSem);

$res = $globals->db->query("SELECT count(*) FROM en_cours WHERE loginbis != 'INSCRIT'");
list($nbInsEnCours) = mysql_fetch_row($res);
mysql_free_result($res);
$page->assign('nbInsEnCours', $nbInsEnCours);

$res = $globals->db->query("SELECT count(*) FROM envoidirect as e left join auth_user_md5 as a ON e.matricule = a.matricule WHERE a.nom is null");
list($nbInsEnvDir) = mysql_fetch_row($res);
mysql_free_result($res);
$page->assign('nbInsEnvDir', $nbInsEnvDir);

$page->run();
?>
