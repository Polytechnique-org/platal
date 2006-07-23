#! /usr/bin/php4
<?php
/********************************************************************************
 * spoolgen.php : spool generation
 * --------------
 *
 * This file is part of the banana distribution
 * Copyright: See COPYING files that comes with this distribution
 ********************************************************************************/

require_once 'connect.db.inc.php';
require_once dirname(__FILE__).'/../modules/banana/banana.inc.php';

class MyBanana extends Banana
{
    function MyBanana()
    {
        global $globals;
        $this->host = "http://{$globals->banana->web_user}:{$globals->banana->web_pass}@{$globals->banana->server}:{$globals->banana->port}/";
        parent::Banana();
    }

    function createAllSpool()
    {
        $this->_require('groups');
        $this->_require('spool');
        $this->_require('misc');

        $groups = new BananaGroups(BANANA_GROUP_ALL);

        foreach (array_keys($groups->overview) as $g) {
            print "Generating spool for $g : ";
            $spool = new BananaSpool($g);
            print "done.\n";
            unset($spool);
        }
        $this->nntp->quit();
    }
}

$banana = new MyBanana();
$banana->createAllSpool();
system("chown -R www-data:www-data /var/spool/banana");
?>
