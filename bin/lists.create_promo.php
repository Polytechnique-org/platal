#!/usr/bin/php4 -q
<?php
require_once("xorg.inc.php");
require_once('validations.inc.php');

$opt = getopt('p:o:h');

if(empty($opt['p']) || empty($opt['o']) || isset($opt['h'])) {
    echo <<<EOF
usage: lists.create_promo.php -p promo -o owner
       create mailing list for promo "promo" with initial owner "owner"

EOF;
    exit;
}

$promo = intval($opt['p']);
$owner = $opt['o'];

$req = new ListeReq(0, "promo$promo", "Liste de la promotion $promo",
                    1 /*private*/, 2 /*moderate*/, 0 /*free subscription*/,
                    array($owner), array());
$req->submit();
?>
