<?php
require('auto.prepend.inc.php');
new_admin_table_editor('logger.actions','id');
$editor->add_join_table('logger.events','action',true);

$editor->describe('text','intitulé',true);
$editor->describe('description','description',true);

$editor->assign('title', 'Gestion des actions de logger');

$editor->run();
?>
