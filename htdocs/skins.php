<?php
require("auto.prepend.inc.php");
new_skinned_page('skins.tpl', AUTH_COOKIE);

if (isset($_REQUEST['submit']))  {  // formulaire soumis, traitons les données envoyées
    mysql_query("UPDATE auth_user_md5
                SET skin={$_REQUEST['newskin']}
                WHERE user_id={$_SESSION['uid']}");
    set_skin();
}

$res = mysql_query("SELECT id,skin_tpl,skin_popup,snapshot,name,s.date,comment,auteur,count(*) AS nb FROM skins AS s
                    LEFT JOIN auth_user_md5 AS a ON s.id=a.skin
                    WHERE !FIND_IN_SET('cachee',type) AND skin_tpl != ''
                    GROUP BY id ORDER BY s.date DESC");
echo mysql_error();
$skins = Array();
while($skins[] = mysql_fetch_assoc($res));
mysql_free_result($res);
array_pop($skins);

$page->assign_by_ref('skins',$skins);
$page->assign('stochaskin',SKIN_STOCHASKIN_ID);

$page->display();
?>
