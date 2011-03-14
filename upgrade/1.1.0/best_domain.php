#!/usr/bin/php5
<?php

require_once 'connect.db.inc.php';

$globals->debug = 0; // Do not store backtraces.

$domains = array(
    'x'      => 'polytechnique.org',
    'master' => 'master.polytechnique.org',
    'phd'    => 'doc.polytechnique.org'
);
$domains_ids = XDB::fetchAllAssoc('name', 'SELECT  name, id
                                             FROM  email_virtual_domains
                                            WHERE  name IN {?}',
                                  $domains);
foreach ($domains as $type => $domain) {
    XDB::execute('UPDATE  accounts
                     SET  best_domain = {?}
                   WHERE  type = {?}',
                 $domains_ids[$domain], $type);
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
