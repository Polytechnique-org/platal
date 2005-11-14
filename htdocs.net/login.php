<?php
    require 'xnet.inc.php';

    if (logged()) {
        redirect("index.php");
    }

    new_page('index.tpl', AUTH_MDP);
    $page->run();
?>
