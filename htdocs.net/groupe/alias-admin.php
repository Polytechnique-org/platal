<?php
require 'xnet.inc.php';

new_groupadmin_page('xnet/groupe/alias-admin.tpl');

if (!Get::has('liste')) {
    redirect("listes.php");
}

$lfull = strtolower(Get::get('liste'));

if (Env::has('add_member')) {
    $add = Env::get('add_member');
    if(strstr($add, '@')) {
	list($mbox,$dom) = explode('@', strtolower($add));
    } else {
	$mbox = $add;
	$dom = 'm4x.org';
    }
    if($dom == 'polytechnique.org' || $dom == 'm4x.org') {
	$res = $globals->xdb->query(
                "SELECT  a.alias, b.alias
                   FROM  x4dat.aliases AS a
              LEFT JOIN  x4dat.aliases AS b ON (a.id=b.id AND b.type = 'a_vie')
                  WHERE  a.alias={?} AND a.type!='homonyme'", $mbox);
	if (list($alias, $blias) = $res->fetchOneRow()) {
	    $alias = empty($blias) ? $alias : $blias;
            $globals->xdb->query(
                "INSERT INTO  x4dat.virtual_redirect (vid,redirect)
                      SELECT  vid, {?}
                        FROM  x4dat.virtual
                       WHERE  alias={?}", "$alias@m4x.org", $lfull);
           $page->trig("$alias@m4x.org ajouté");
	} else {
            $page->trig("$mbox@polytechnique.org n'existe pas.");
	}
    } else {
        $globals->xdb->query(
                "INSERT INTO  x4dat.virtual_redirect (vid,redirect)
                      SELECT  vid,{?}
                        FROM  x4dat.virtual
                       WHERE  alias={?}", "$mbox@$dom", $lfull);
        $page->trig("$mbox@$dom ajouté");
    }
}

if (Env::has('del_member')) {
    $globals->xdb->query(
            "DELETE FROM  x4dat.virtual_redirect
                   USING  x4dat.virtual_redirect
              INNER JOIN  x4dat.virtual USING(vid)
                   WHERE  redirect={?} AND alias={?}", Env::get('del_member'), $lfull);
    redirect("?liste=$lfull");
}

$res = $globals->xdb->iterator(
        "SELECT  redirect
           FROM  x4dat.virtual_redirect AS vr
     INNER JOIN  x4dat.virtual          AS v  USING(vid)
          WHERE  v.alias={?}
       ORDER BY  redirect", $lfull);
$page->assign('mem', $res);

$page->run();
?>
