<?php

require_once('tabs.inc.php');

require_once('profil.func.inc.php');

$page->register_modifier('print_html','_print_html_modifier');
$page->register_function('draw_onglets','draw_all_tabs');



?>
