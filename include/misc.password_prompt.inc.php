<?php
function smarty_insert_getName() {
    $pre = strtok($_COOKIE['ORGlogin'],".");
    $pre1=strtok($pre,"-");
    $pre2=strtok(" ");
    $pre1=ucfirst($pre1);
    $pre2=ucfirst($pre2);
    if ($pre2) {
        $prenom = $pre1."-".$pre2;
    } else {
        $prenom = $pre1;
    }
    return $prenom;
}

function smarty_insert_getUsername() {
    return isset($_SESSION['username'])
        ? $_SESSION['username']
        : (isset($_COOKIE['ORGlogin']) ? $_COOKIE['ORGlogin'] : "");
}
?>
