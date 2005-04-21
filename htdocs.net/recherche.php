<?php

require 'xnet.inc.php';
require_once 'exalead/exalead.parser.inc.php';

new_page('xnet/recherche.tpl', AUTH_PUBLIC);
$page->setType('recherche');
$page->useMenu();
$page->addCssLink('exalead.css');


$query_exa = "http://murphy:10000/cgi/poly.net_devel";

$exalead = new Exalead($query_exa);

if ($exalead->query('query')) {
    $page->assign_by_ref('exalead_data', $exalead->data);
}

$page->run();

?>
