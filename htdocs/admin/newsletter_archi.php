<?php
require("auto.prepend.inc.php");
new_admin_table_editor('newsletter','id');

$editor->assign('title', 'Gestion des archives de la newsletter');

$editor->describe('date', 'date', true);
$editor->describe('titre', 'titre', true);
$editor->describe('text', 'texte', false, 'textarea');

$editor->run();
?>
