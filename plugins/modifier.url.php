<?php
function smarty_modifier_url($string)
{
    if(strpos($string, "http://")!==false)
	return $string;
    $chemins = Array('', '../', '../../');
    foreach ($chemins as $ch) {
	if (file_exists($ch.'../htdocs/'))
	    return $ch.$string;
    }
    return '';
}
?>
