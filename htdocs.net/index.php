<?php
    require 'xnet.inc.php';
    require 'xnet/page.inc.php';

    new_skinned_page('xnet/index.tpl', AUTH_PUBLIC);
    $page->run();
?>
