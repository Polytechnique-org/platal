<?php
ini_set('include_path', ".:..:/home/x2000habouzit/dev/public/include:/home/x2000habouzit/dev/diogenes/lib/:/home/x2000habouzit/dev/smarty/");
require("xorg.common.inc.php");
require("Smarty.class.php");


function mysql_assign(&$page,$sql_query,$var_name,$var_nb_name='') {
    $sql = mysql_query($sql_query);
    if(mysql_errno())
        return(mysql_error($sql));

    $array = Array();
    while($array[] = mysql_fetch_assoc($sql));
    array_pop($array);
    mysql_free_result($sql);

    $page->assign_by_ref($var_name,$array);
    if(!empty($var_nb_name))
        $page->assign($var_nb_name, count($array));
    return 0;
}

$page = new Smarty();

$page->template_dir = $globals->spoolroot . "/templates/";
$page->compile_dir  = $globals->spoolroot . "/templates_c/";
$page->plugins_dir[]= $globals->spoolroot . "/plugins/";
$page->cache_dir    = $globals->spoolroot . "/cache/";
    
$page->config_overwrite=false;
$page->compile_check=true;
$page->caching=false;
?>
