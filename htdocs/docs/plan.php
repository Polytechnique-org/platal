<?php

require("auto.prepend.inc.php");

new_skinned_page('docs/plan.tpl',AUTH_PUBLIC);

function page($params, $content, &$smarty) {
    extract($params);
    
    $ret = "<li><a href='$url'>$title</a>\n";

    if(!empty($content)) {
	$t = preg_replace("/[ \t\n]+/", " ", $content);
	$ret .= " [<a href='#' onclick='textpopup(\"".urlencode($t) ."\")'>?</a>]\n";
    }
    return $ret . "</li>\n";
}

$page->register_block('page', 'page');

$page->run();

?>
