<?php
require('auto.prepend.inc.php');
new_admin_table_editor('paiement.paiements','id');

$editor->add_join_table('paiement.transactions','ref',true);

$editor->describe('text','intitulé',true);
$editor->describe('url','site web',false);
$editor->describe('montant_def','montant par défaut',false);
$editor->describe('montant_min','montant minimum',false);
$editor->describe('montant_max','montant maximum',false);
$editor->describe('mail','email contact',true);
$editor->describe('confirmation','message confirmation',false,'textarea');
$editor->describe('flags','flags', true, 'set');

$editor->assign('title', 'Gestion des télépaiements');
$editor->run();
?>
