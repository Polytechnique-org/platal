<?php
require 'xnet.inc.php';

$res = $globals->xdb->query("SELECT logo, logo_mime FROM groupex.asso WHERE id = {?}", $globals->asso('id'));
list($logo, $logo_mime) = $res->fetchOneRow();

if (!empty($logo)) {
    header("Content-type: $mime");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified:" . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    echo $logo;
} else {
    header("Content-type: image/jpeg");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified:" . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    readfile("../images/dflt_carre.jpg");
}

?>
