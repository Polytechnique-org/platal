<?php
require('auto.prepend.inc.php');
new_admin_table_editor('groupesx_auth','id');

$editor->describe('name','nom',true);
$editor->describe('privkey','clé privée',false);
$editor->describe('datafields','champs renvoyés',true);

$editor->assign('title', 'Gestion de l\'authentification centralisée');

$editor->run();
?>
