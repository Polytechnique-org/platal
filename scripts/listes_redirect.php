<?php
preg_match("/^\/(moderate|admin|members)\/(.*)_([^_]*)$/", $_SERVER["REQUEST_URI"], $matches);
if (empty($matches)) {
    exit();
} else {
    $action = $matches[1];
    $mbox   = $matches[2];
    $fqdn   = strtolower($matches[3]);
    if ($fqdn == 'polytechnique.org') {
        header("Location: https://www.polytechnique.org/listes/$action?liste=$mbox");
    } else {
        require("../include/xorg.inc.php");
        $res = $globals->xdb->query("select diminutif from groupex.asso where mail_domain = {?}", $fqdn);
        if ($gpx = $res->fetchOneCell()) {
            header("Location: http://www.polytechnique.net/$gpx/listes-$action?liste=$mbox");
        }
    }
}
?>
