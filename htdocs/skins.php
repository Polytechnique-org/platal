<?php
require("auto.prepend.inc.php");
new_skinned_page('skins.tpl', AUTH_COOKIE);

if (isset($_REQUEST['submit']))  {  // formulaire soumis, traitons les données envoyées
    $globals->db->query("UPDATE auth_user_md5
                SET skin={$_REQUEST['newskin']}
                WHERE user_id={$_SESSION['uid']}");
    set_skin();
}

$sql = "SELECT s.*,auteur,count(*) AS nb
          FROM skins AS s
     LEFT JOIN auth_user_md5 AS a ON s.id=a.skin
         WHERE skin_tpl != '' AND ext != ''
      GROUP BY id ORDER BY s.date DESC";
$page->mysql_assign($sql, 'skins');
$page->assign('stochaskin',SKIN_STOCHASKIN_ID);

$page->run();
?>
