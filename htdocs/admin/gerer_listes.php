<?php
require('auto.prepend.inc.php');
new_admin_table_editor('listes_def','id');

$editor->add_join_table('aliases','id','aliases.type=\'liste\'');

$editor->add_join_field('aliases','alias','alias','','liste',true);
$editor->describe('topic','topic',true);
$editor->describe('type','type',true,'set');

$editor->assign('title', 'Gestion des liste des diffusion');

$editor->run();
?>
