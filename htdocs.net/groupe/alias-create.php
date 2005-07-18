<?php
require 'xnet.inc.php';

new_groupadmin_page('xnet/groupe/alias-create.tpl');

if(Post::has('submit')) {
    if (!Post::has('liste')) {
        $page->trig_run('champs «addresse souhaitée» vide');
    }
    $liste = Post::get('liste');
    if (!preg_match("/^[a-zA-Z0-9\-]*$/", $liste)) {
        $page->trig_run('le nom de l\'alias ne doit contenir que des lettres, chiffres et tirets');
    }

    $new = $liste.'@'.$globals->asso('mail_domain');
    $res = $globals->xdb->query('SELECT COUNT(*) FROM x4dat.virtual WHERE alias={?}', $new);
    $n   = $res->fetchOneCell();
    if($n) {
        $page->trig_run('cet alias est déjà pris');
    }
  
    $globals->xdb->query('INSERT INTO x4dat.virtual (alias,type) VALUES({?}, "user")', $new);
    header("Location: alias-admin.php?liste=$new");
}

$page->run();

?>
