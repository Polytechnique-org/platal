<?php
require('auto.prepend.inc.php');
new_admin_table_editor('coupures','id');

$editor->describe('debut','date',true,'timestamp');
$editor->describe('duree','durée',false);
$editor->describe('resume','résumé',true);
$editor->describe('services','services affectés',true,'set');
$editor->describe('description','description',false,'textarea');

$editor->assign('title', 'Gestion des coupures');

$editor->run();
?>
