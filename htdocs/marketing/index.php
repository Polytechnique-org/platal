<?php
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
