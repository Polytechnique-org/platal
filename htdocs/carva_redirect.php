<?php
require("auto.prepend.inc.php");
new_skinned_page('carva_redirect.tpl', AUTH_MDP);

if (isset($_REQUEST['submit']) and ($_REQUEST['submit'] == "Valider" or $_REQUEST['submit'] == "Modifier") and isset($_REQUEST['url'])) {
    // on change la redirection (attention à http://)
    mysql_query("update auth_user_md5 set redirecturl = '{$_REQUEST['url']}'"
              ." where user_id = '{$_SESSION['uid']}'");
    if (mysql_errno($conn) == 0) {
        $_SESSION['log']->log("carva_add","http://".$_REQUEST['url']);
        $page->assign('message',"<p class='normal'>Redirection activée vers <a href='http://"
                .$_REQUEST['url']."'>{$_REQUEST['url']}</a></p>\n");
    } else {
        $page->assign('message',"<p class='erreur'>Erreur de mise à jour</p>\n");
    }
} elseif (isset($_REQUEST['submit']) and $_REQUEST['submit'] == "Supprimer") {
    // on supprime la redirection
    mysql_query("update auth_user_md5 set redirecturl = '' where user_id = {$_SESSION['uid']}");
    if (mysql_errno($conn) == 0) {
        $_SESSION['log']->log("carva_del",$_REQUEST['url']);
        $_POST['url'] = '';
        $page->assign('message',"<p class='normal'>Redirection supprimée</p>");
    } else {
        $page->assign('message',"<p class='erreur'>Erreur de suppression</p>\n");
    }
}

$result = mysql_query("select alias, redirecturl from auth_user_md5 where user_id={$_SESSION['uid']}");
list($alias, $carva) = mysql_fetch_row($result);
mysql_free_result($result);
$page->assign('carva', $carva);
$page->assign('alias', $alias);

$page->display();
?>
