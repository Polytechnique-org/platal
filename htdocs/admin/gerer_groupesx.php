<?php
require('auto.prepend.inc.php');
new_admin_table_editor('groupesx_def','id');
$editor->add_join_table('groupesx_ins','gid',true); 

$editor->describe('text','intitulé',true);
$editor->describe('url','site web',false);

$editor->assign('title', 'Gestion des Groupes X');

$editor->run();
?>
