<?php
require('auto.prepend.inc.php');
new_admin_table_editor('skins','id');
$editor->describe('name','nom',true);
$editor->describe('skin_tpl','nom du template',true);
$editor->describe('auteur','auteur',false);
$editor->describe('comment','commentaire',true,'textarea');
$editor->describe('date','date',false);
$editor->describe('bgtable','<em>OBSOLETE</strong> bgtable',false);
$editor->describe('typelogo','<em>OBSOLETE</strong> type logo',false);
$editor->describe('typeban','<em>OBSOLETE</strong> type ban',false);
$editor->describe('typelesX','<em>OBSOLETE</strong> type les X',false);
$editor->describe('type','type',true,'set');

#FIXME enlever les entrées obsoletes

$editor->assign('title', 'Gestion des skins');
$editor->run(); 
?>
