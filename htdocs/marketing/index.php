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
        $Id: index.php,v 1.5 2004-11-20 19:07:02 x2000chevalier Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('marketing/index.tpl');

# Quelques statistiques

$sql = "SELECT count(*) as vivants,
	       count(NULLIF(perms!='non-inscrit', 0)) as inscrits,
	       100*count(NULLIF(perms!='non-inscrit', 0))/count(*) as ins_rate,
	       count(NULLIF(promo >= 1972, 0)) as vivants72,
	       count(NULLIF(promo >= 1972 AND perms!='non-inscrit', 0)) as inscrits72,
	       100 * count(NULLIF(promo >= 1972 AND perms!='non-inscrit', 0)) /
                   count(NULLIF(promo >= 1972, 0)) as ins72_rate,
	       count(NULLIF(FIND_IN_SET('femme', flags), 0)) as vivantes,
	       count(NULLIF(FIND_IN_SET('femme', flags) AND perms!='non-inscrit', 0)) as inscrites,
	       100 * count(NULLIF(FIND_IN_SET('femme', flags) AND perms!='non-inscrit', 0)) /
		   count(NULLIF(FIND_IN_SET('femme', flags), 0)) as inse_rate
          FROM auth_user_md5
         WHERE deces = 0";
$res = $globals->db->query($sql);
$stats = mysql_fetch_assoc($res);

$page->assign('stats', $stats);
mysql_free_result($res);

$res = $globals->db->query("SELECT count(*) FROM auth_user_md5 WHERE date_ins > ".date("Ymd", strtotime ("last Monday"))."*1000000");
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
