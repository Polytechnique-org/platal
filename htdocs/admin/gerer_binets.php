<?php
require('auto.prepend.inc.php');
new_admin_table_editor('binets_def','id');

$editor->add_join_table('binets_ins','binet_id',true); 

$editor->describe('text','intitulé',true);
$editor->assign('title', 'Gestion des binets');

$editor->run();
?>
