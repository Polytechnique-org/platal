<?php
    require 'xnet.inc.php';
    require 'xnet/page.inc.php';

    if (!($cat = Get::get('cat'))) {
        header("Location:index.php");
        exit;
    }

    new_skinned_page('xnet/groupes.tpl', AUTH_PUBLIC);

    $res = $globals->xdb->iterator("SELECT id,nom FROM groupex.dom WHERE FIND_IN_SET({?}, cat) ORDER BY nom", $cat);
    $page->assign('doms', $res);
    
    if (!$res->total()) {
        $res = $globals->xdb->iterator("SELECT diminutif, nom FROM groupex.asso WHERE FIND_IN_SET({?}, cat) ORDER BY nom", $cat);
    } elseif (Get::has('dom')) {
        $res = $globals->xdb->iterator("SELECT diminutif, nom FROM groupex.asso WHERE FIND_IN_SET({?}, cat) AND dom={?} ORDER BY nom", $cat, Get::getInt('dom'));
    } else {
        $res = null;
    }
    $page->assign('gps', $res);

    $page->setType($cat);
    $page->run();
?>
