<?php
    require 'xnet.inc.php';
    require 'xnet/page.inc.php';

    if (logged()) {
        header("Location: index.php");
    }

    new_skinned_page('index.tpl', AUTH_MDP);
    $page->run();
?>
