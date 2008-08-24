#!/usr/bin/php5 -q
<?php

require_once(dirname(__FILE__) . '/connect.db.inc.php');
require_once('validations.inc.php');

global $globals;

$opt = getopt('p:o:h');

if(empty($opt['p']) || empty($opt['o']) || isset($opt['h'])) {
    echo <<<EOF
usage: lists.create_promo.php -p promo -o owner
       create mailing list for promo "promo" with initial owner "owner"

EOF;
    exit;
}

// Retrieves list parameters.
$promo = intval($opt['p']);
$owner = $opt['o'];

$owner_user = User::getSilent($owner);
if (!$owner_user) {
    echo "Supplied owner is not valid, aborting.\n";
    exit 1;
}

// Creates the list.
$req = new ListeReq($owner_user, false, "promo$promo", $globals->mail->domain, "Liste de la promotion $promo",
                    1 /*private*/, 2 /*moderate*/, 0 /*free subscription*/,
                    array($owner), array());
$req->submit();
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
