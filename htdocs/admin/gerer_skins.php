<?php
require('auto.prepend.inc.php');
new_admin_table_editor('skins','id');
$editor->describe('name','nom',true);
$editor->describe('skin_tpl','nom du template',true);
$editor->describe('auteur','auteur',false);
$editor->describe('comment','commentaire',true,'textarea');
$editor->describe('date','date',false);
$editor->describe('ext','extension du screenshot',false);

$editor->assign('title', 'Gestion des skins');
$editor->run(); 
?>
