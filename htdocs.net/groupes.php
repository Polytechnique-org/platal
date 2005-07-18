<?php
    require 'xnet.inc.php';

    if (!($cat = Get::get('cat'))) {
        header("Location:index.php");
        exit;
    }

    $_GET['cat'] = strtolower($cat);

    new_page('xnet/groupes.tpl', AUTH_PUBLIC);

    $res  = $globals->xdb->query("SELECT id,nom FROM groupex.dom WHERE FIND_IN_SET({?}, cat) ORDER BY nom", $cat);
    $doms = $res->fetchAllAssoc();
    $page->assign('doms', $doms);
    
    if (empty($doms)) {
        $res = $globals->xdb->iterator("SELECT diminutif, nom FROM groupex.asso WHERE FIND_IN_SET({?}, cat) ORDER BY nom", $cat);
    } elseif (Get::has('dom')) {
        $res = $globals->xdb->iterator("SELECT diminutif, nom FROM groupex.asso WHERE FIND_IN_SET({?}, cat) AND dom={?} ORDER BY nom", $cat, Get::getInt('dom'));
    } else {
        $res = null;
    }
    $page->assign('gps', $res);

    $page->useMenu();
    $page->setType($cat);
    $page->run();
?>
