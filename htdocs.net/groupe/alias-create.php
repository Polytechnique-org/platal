<?php
require 'xnet.inc.php';

new_groupadmin_page('xnet/groupe/alias-create.tpl');
$page->useMenu();
$page->setType($globals->asso('cat'));

$page->assign('asso', $globals->asso());

if(Post::has('submit')) {
    if (!Post::has('liste')) {
        $page->kill('champs «addresse souhaitée» vide');
    }
    $liste = Post::get('liste');
    if (!preg_match("/^[a-zA-Z0-9\-]*$/", $liste)) {
        $page->kill('le nom de l\'alias ne doit contenir que des lettres, chiffres et tirets');
    }

    $new = $liste.'@'.$globals->asso('mail_domain');
    $res = $globals->xdb->query('SELECT COUNT(*) FROM x4dat.virtual WHERE alias={?}', $new);
    $n   = $res->fetchOneCell();
    if($n) {
        $page->kill('cet alias est déjà pris');
    }
  
    $globals->xdb->query('INSERT INTO x4dat.virtual (alias,type) VALUES({?}, "user")', $new);
    header("Location: alias-admin.php?liste=$new");
}

$page->run();

?>
