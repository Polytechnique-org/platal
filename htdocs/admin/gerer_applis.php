<?php
require('auto.prepend.inc.php');
new_admin_table_editor('applis_def','id');
$editor->add_join_table('applis_ins','aid',true); 

$editor->describe('text','intitulé',true);
$editor->describe('type','type',true,'set');
$editor->describe('url','site web',false);

$editor->assign('title', 'Gestion des formations');

$editor->run();
?>
