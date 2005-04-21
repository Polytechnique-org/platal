<?php

require_once 'xnet.inc.php';

new_group_page('xnet/groupe/listes.tpl');
$page->setType($globals->asso('cat'));
$page->useMenu();
$page->assign('asso', $globals->asso());

require_once('lists.inc.php');
$client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'), $globals->asso('mail_domain'));


if(Get::has('del')) {
    $client->unsubscribe(Get::get('del'));
    header('Location: listes.php');
}
if(Get::has('add')) {
    $client->subscribe(Get::get('add'));
    header('Location: listes.php');
}
if(Post::has('promo_add')) {
    $promo = Post::getInt('promo_add');
    if ($promo>=1900 and $promo<2100) {
        $client->subscribe("promo$promo");
    } else {
        $page->trig("promo incorrecte, il faut une promo sur 4 chiffres.");
    }
}


if (Post::has('del_alias')) {
    $globals->xdb->query(
            'DELETE FROM  x4dat.virtual_redirect, x4dat.virtual
                   USING  x4dat.virtual AS v
	      INNER JOIN  x4dat.virtual_redirect USING(vid)
	           WHERE  v.alias={?}', Post::get('del_alias'));
    $page->trig(Post::get('del_alias')." supprimé !");
}

$listes = $client->get_lists();
$page->assign('listes',$listes);

$alias  = $globals->xdb->iterator(
        'SELECT  alias,type
           FROM  x4dat.virtual
          WHERE  alias
           LIKE  {?} AND type="user"
       ORDER BY  alias', '%@'.$globals->asso('mail_domain'));
$page->assign('alias', $alias);

$page->assign('may_update', may_update());

$page->run();
?>
