<?php
require("auto.prepend.inc.php");

new_skinned_page('acces_wap.tpl', AUTH_MDP);

if (isset($_REQUEST['op']) && $_REQUEST['op'] == "valid") {
    // on enregistre les modifications des parametres
    $sql="update wap set flags='";
    if (isset($_REQUEST['actif'])) $sql.="actif,";
    if (isset($_REQUEST['useimage'])) $sql.="useimage,";
    if (isset($_REQUEST['ccfrom'])) $sql.="ccfrom,"; 
    if (isset($_REQUEST['IMAPssl'])) $sql.="IMAPssl";   
    $sql.="'";
    if (isset($_REQUEST['screenwidth'])&&is_integer($_REQUEST['screenwidth'])) $sql.=",screenwidth = {$_REQUEST['screenwidth']}";
    if (isset($_REQUEST['screenheight'])&&is_integer($_REQUEST['screenheight'])) $sql.=",screenheight = {$_REQUEST['screenheight']}";
    if (isset($_REQUEST['fromaddr'])&&in_array($_REQUEST['fromaddr'],array("m4x","polytechnique","autre"))) $sql.=",fromaddr = '{$_REQUEST['fromaddr']}'";
    if (isset($_REQUEST['otheraddr'])) $sql.=",otheraddr = '".addslashes($_REQUEST['otheraddr'])."'";
    if (isset($_REQUEST['IMAPserver'])) $sql.=",IMAPserver = '".addslashes($_REQUEST['IMAPserver'])."'";
    if (isset($_REQUEST['IMAPport'])&&is_integer($_REQUEST['IMAPport'])) $sql.=",IMAPport = {$_REQUEST['IMAPport']}";
    if (isset($_REQUEST['IMAPdir'])) $sql.=",IMAPdir = '".addslashes($_REQUEST['IMAPdir'])."'";
    $sql.=" where uid = ".$_SESSION['uid'];
    $globals->db->query($sql);

    $page->assign('OK',true);
    $page->run('OK');
} else { // pas de formulaire soumis
    $sql = "SELECT  FIND_IN_SET('actif',flags) AS actif,
                    FIND_IN_SET('useimage',flags) AS useimage,
                    screenwidth,screenheight,fromaddr,otheraddr,
                    FIND_IN_SET('ccfrom',flags) AS ccfrom,
                    IMAPserver,IMAPport,IMAPdir,
                    FIND_IN_SET('IMAPssl',flags) AS IMAPssl
                    FROM wap WHERE uid = ".$_SESSION['uid'];
    $result = $globals->db->query($sql);

    if (mysql_num_rows($result)==0) {
        //l'utilisateur n'a pas de profil WAP => on le crée
        $globals->db->query("INSERT INTO wap (uid) VALUES (".$_SESSION['uid'].")");
        $result = $globals->db->query($sql);
    }

    $page->assign('wap', mysql_fetch_assoc($result));
    mysql_free_result($result);
    $page->run();
}
?>
