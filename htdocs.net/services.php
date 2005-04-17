<?php
    require 'xnet.inc.php';
    require 'xnet/page.inc.php';

    new_skinned_page('xnet/services.tpl', AUTH_PUBLIC);
    $page->run();
?>
