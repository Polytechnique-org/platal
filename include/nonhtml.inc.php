<?php
ini_set('include_path', ".:..:/home/x2000habouzit/dev/public/include:/home/x2000habouzit/dev/diogenes/lib/:/home/x2000habouzit/dev/smarty/");
require("xorg.globals.inc.php");
require("Smarty.class.php");

$page = new Smarty();

$page->template_dir = $globals->spoolroot . "/templates/";
$page->compile_dir  = $globals->spoolroot . "/templates_c/";
$page->plugins_dir[]= $globals->spoolroot . "/plugins/";
$page->cache_dir    = $globals->spoolroot . "/cache/";
    
$page->config_overwrite=false;
$page->compile_check=true;
$page->caching=false;
?>
