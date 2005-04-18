<?php
    require 'xnet.inc.php';

    XnetSession::destroy();

    new_page('xnet/deconnexion.tpl', AUTH_PUBLIC);
    $page->useMenu();
    $page->run();

?>
