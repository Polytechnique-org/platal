<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

require_once 'banana/banana.inc.php';
require_once 'banana/hooks.inc.php';

class PlatalBananaMLPage extends PlatalBananaPage
{
    public function __construct()
    {
        parent::__construct();
        global $platal;
        $this->handler = 'BananaMLHandler';
        $this->base    = $platal->pl_self(1);
    }
}

class BananaMLHandler extends BananaHandler
{
    public function template()
    {
        return 'lists/archives.tpl';
    }
}

class MLBanana extends Banana
{
    static public $listname;
    static public $domain;

    function __construct($forlife, $params = null)
    {
        global $globals;
        Banana::$spool_root = $globals->banana->spool_root;
        Banana::$spool_boxlist = false;
        Banana::$msgedit_canattach = true;
        Banana::$debug_mbox = ($globals->debug & DEBUG_BT);
        Banana::$debug_smarty = ($globals->debug & DEBUG_SMARTY);
        Banana::$mbox_helper = $globals->banana->mbox_helper;
        Banana::$feed_updateOnDemand = true;
        if (S::has_perms()) {
            Banana::$msgshow_mimeparts[] = 'source';
        }
        array_push(Banana::$msgparse_headers, 'x-org-id', 'x-org-mail');
        if (!S::v('core_rss_hash')) {
            Banana::$feed_active = false;
        }

        MLBanana::$listname = $params['listname'];
        MLBanana::$domain   = $params['domain'];
        $params['group'] = $params['listname'] . '@' . $params['domain'];
        parent::__construct($params, 'MLArchive', 'PlatalBananaMLPage');
    }

    public function run()
    {
        global $platal, $globals;

        $nom  = S::v('prenom') . ' ' . S::v('nom');
        $mail = S::v('bestalias') . '@' . $globals->mail->domain;
        $sig  = $nom . ' (' . S::v('promo') . ')';
        Banana::$msgedit_headers['X-Org-Mail'] = S::v('forlife') . '@' . $globals->mail->domain;

        // Tree color
        $req = XDB::query("SELECT  tree_unread, tree_read
                             FROM  {$globals->banana->table_prefix}profils
                            WHERE  uid={?}", S::i('uid'));
        if (!(list($unread, $read) = $req->fetchOneRow())) {
            $unread = 'o';
            $read = 'dg';
        }
        Banana::$tree_unread = $unread;
        Banana::$tree_read = $read;

        // Build user profile
        Banana::$profile['headers']['From']         = "$nom <$mail>";
        Banana::$profile['headers']['Organization'] = make_Organization();
        Banana::$profile['signature']               = $sig;

        // Page design
        Banana::$page->killPage('forums');
        Banana::$page->killPage('subscribe');

        // Run Banana
        return parent::run();
    }
}

require_once('banana/mbox.inc.php');

class BananaMLArchive extends BananaMBox
{
    public function name()
    {
        return 'MLArchives';
    }

    public function getBoxList($mode = Banana::BOXES_ALL, $since = 0, $withstats = false)
    {
        global $globals;
        $spool = $globals->lists->spool . '/';
        $list = glob($spool . '*.mbox/*.mbox');
        if ($list === false) {
            return array();
        }
        $groups = array();
        foreach ($list as $path) {
            $path = substr($path, strpos($path, 'mbox/') + 5);
            $path = substr($path, 0, -5);
            list($domain, $listname) = explode($globals->lists->vhost_sep, $path, 2);
            $group = $listname . '@' . $domain;
            $groups[$group] = array('desc' => null, 'msgnum' => null, 'unread' => null);
        }
        return $groups;
    }

    public function filename()
    {
        if (MLBanana::$listname) {
            $listname = MLBanana::$listname;
            $domain   = MLBanana::$domain;
        } else {
            list($listname, $domain) = explode('@', Banana::$group);
        }
        return $domain . '_' . $listname;
    }

    protected function getFileName()
    {
        global $globals;
        $base = $globals->lists->spool;
        if (MLBanana::$listname) {
            $listname = MLBanana::$listname;
            $domain   = MLBanana::$domain;
        } else {
            list($listname, $domain) = explode('@', Banana::$group);
        }
        $file = $domain . $globals->lists->vhost_sep . $listname . '.mbox';
        return "$base/$file/$file";
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
