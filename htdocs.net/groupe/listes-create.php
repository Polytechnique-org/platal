<?php
require 'xnet.inc.php';

new_groupadmin_page('xnet/groupe/listes-create.tpl');

if (Post::has('submit')) {
    if (!Post::has('liste')) {
        $page->trig_run('champs «addresse souhaitée» vide');
    }

    $liste = Post::get('liste');
    
    if (!preg_match("/^[a-zA-Z0-9\-]*$/", $liste)) {
        $page->trig_run('le nom de la liste ne doit contenir que des lettres, chiffres et tirets');
    }

    $new = $liste.'@'.$globals->asso('mail_domain');
    $res = $globals->xdb->query('SELECT COUNT(*) FROM x4dat.virtual WHERE alias={?}', $new);
    $n   = $res->fetchOneCell();
    
    if($n) {
        $page->trig_run('cet alias est déjà pris');
    }
    if(!Post::get('desc')) {
        $page->trig_run('le sujet est vide');
    }

    require('xml-rpc-client.inc.php');
    require_once('lists.inc.php');
    $client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'), $globals->asso('mail_domain'));
    $ret    = $client->create_list($liste, Post::get('desc'), Post::get('advertise'), Post::get('modlevel'), Post::get('inslevel'), array(Session::get('forlife')), array());

    $dom    = strtolower($globals->asso("mail_domain"));
    $red    = $dom.'_'.$liste;

    if($ret) {
        $globals->xdb->execute('INSERT INTO x4dat.virtual (alias,type) VALUES({?},{?})',              $liste.'@'.$dom, 'list');
        $globals->xdb->execute('INSERT INTO x4dat.virtual_redirect (vid,redirect) VALUES ({?}, {?})', mysql_insert_id(), "$red+post@listes.polytechnique.org");
        $globals->xdb->execute('INSERT INTO x4dat.virtual (alias,type) VALUES({?},{?})',              $liste.'-owner@'.$dom, 'list');
        $globals->xdb->execute('INSERT INTO x4dat.virtual_redirect (vid,redirect) VALUES ({?}, {?})', mysql_insert_id(), "$red+owner@listes.polytechnique.org");
        $globals->xdb->execute('INSERT INTO x4dat.virtual (alias,type) VALUES({?},{?})',              $liste.'-admin@'.$dom, 'list');
        $globals->xdb->execute('INSERT INTO x4dat.virtual_redirect (vid,redirect) VALUES ({?}, {?})', mysql_insert_id(), "$red+admin@listes.polytechnique.org");
        $globals->xdb->execute('INSERT INTO x4dat.virtual (alias,type) VALUES({?},{?})',              $liste.'-bounces@'.$dom, 'list');
        $globals->xdb->execute('INSERT INTO x4dat.virtual_redirect (vid,redirect) VALUES ({?}, {?})', mysql_insert_id(), "$red+bounces@listes.polytechnique.org");
        header("Location: listes-admin.php?liste=$liste");
    } else {
        $page->kill("Un problème est survenu, contacter <a href='mailto:support@m4x.org'>support@m4x.org</a>");
    }
}

$page->run()

?>
