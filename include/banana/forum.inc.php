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

function hook_checkcancel($_headers)
{
    return ($_headers['x-org-id'] == S::v('hruid') or S::has_perms());
}

class ForumsBanana extends Banana
{
    private $user;

    public function __construct(User &$user, $params = null)
    {
        $this->user = &$user;

        global $globals;
        Banana::$msgedit_canattach = false;
        Banana::$spool_root = $globals->banana->spool_root;
        array_push(Banana::$msgparse_headers, 'x-org-id', 'x-org-mail');
        Banana::$nntp_host = 'news://web_' . $user->login()
                           . ":{$globals->banana->password}@{$globals->banana->server}:{$globals->banana->port}/";
        if (S::has_perms()) {
            Banana::$msgshow_mimeparts[] = 'source';
        }
        Banana::$debug_nntp = ($globals->debug & DEBUG_BT);
        Banana::$debug_smarty = ($globals->debug & DEBUG_SMARTY);
        Banana::$feed_active = S::hasAuthToken();

        parent::__construct($params, 'NNTP', 'PlatalBananaPage');
        if (@$params['action'] == 'profile') {
            Banana::$action = 'profile';
        }
    }

    public function run()
    {
        global $platal, $globals;

        // Update last unread time
        $time = null;
        if (!is_null($this->params) && isset($this->params['updateall'])) {
            $time = intval($this->params['updateall']);
            S::set('banana_last', $time);
        }

        // Get user profile from SQL
        $req = XDB::query("SELECT  name, mail, sig,
                                   FIND_IN_SET('threads',flags), FIND_IN_SET('automaj',flags),
                                   tree_unread, tree_read
                             FROM  forum_profiles
                            WHERE  uid = {?}", $this->user->id());
        if (!(list($nom, $mail, $sig, $disp, $maj, $unread, $read) = $req->fetchOneRow()) || !$nom) {
            $nom  = S::v('prenom')." ".S::v('nom');
            $mail = $this->user->forlifeEmail();
            $sig  = $nom." (".S::v('promo').")";
            $disp = 0;
            $maj  = 1;
            $unread = 'o';
            $read   = 'dg';
        }
        if ($maj) {
            $time = time();
        }

        // Build user profile
        $req = XDB::query("SELECT  name
                             FROM  forum_subs AS fs
                        LEFT JOIN  forums AS f ON (f.fid = fs.fid)
                            WHERE  uid={?}", $this->user->id());
        Banana::$profile['headers']['From']         = "$nom <$mail>";
        Banana::$profile['headers']['Organization'] = make_Organization();
        Banana::$profile['signature']               = $sig;
        Banana::$profile['display']                 = $disp;
        Banana::$profile['autoup']                  = $maj;
        Banana::$profile['lastnews']                = S::v('banana_last');
        Banana::$profile['subscribe']               = $req->fetchColumn();
        Banana::$tree_unread = $unread;
        Banana::$tree_read = $read;

        // Update the "unread limit"
        if (!is_null($time)) {
            XDB::execute('UPDATE  forum_profiles
                             SET  last_seen = FROM_UNIXTIME({?})
                           WHERE  uid = {?}',
                         $time, $this->user->id());
            if (XDB::affectedRows() == 0) {
                XDB::execute('INSERT INTO  forum_profiles (uid, last_seen)
                                   VALUES  ({?}, FROM_UNIXTIME({?}))',
                             $this->user->id(), $time);
            }
        }

        if (!empty($GLOBALS['IS_XNET_SITE'])) {
            Banana::$page->killPage('forums');
            Banana::$page->killPage('subscribe');
            Banana::$spool_boxlist = false;
        } else {
            // Register custom Banana links and tabs
            if (!Banana::$profile['autoup']) {
                Banana::$page->registerAction('<a href=\'javascript:dynpostkv("'
                                    . $platal->path . '", "updateall", ' . time() . ')\'>'
                                    . 'Marquer tous les messages comme lus'
                                    . '</a>', array('forums', 'thread', 'message'));
            }
            Banana::$page->registerPage('profile', 'Préférences', null);
        }

        // Run Bananai
        if (Banana::$action == 'profile') {
            Banana::$page->run();
            return $this->action_updateProfile();
        } else {
            return parent::run();
        }
    }

    public function post($dest, $reply, $subject, $body)
    {
        global $globals;
        Banana::$profile['headers']['From']         = $this->user->fullName() .  ' <' . $this->user->bestEmail() . '>';
        Banana::$profile['headers']['Organization'] = make_Organization();
        return parent::post($dest, $reply, $subject, $body);
    }

    protected function action_saveSubs($groups)
    {
        global $globals;
        $uid = $this->user->id();

        Banana::$profile['subscribe'] = array();
        XDB::execute('DELETE FROM  forum_subs
                            WHERE  uid = {?}', $this->user->id());
        if (!count($groups)) {
            return true;
        }

        $req  = XDB::iterRow('SELECT  fid, name
                                FROM  forums');
        $fids = array();
        while (list($fid, $fnom) = $req->next()) {
            $fids[$fnom] = $fid;
        }

        $diff = array_diff($groups, array_keys($fids));
        foreach ($diff as $g) {
            XDB::execute('INSERT INTO  forums (name)
                               VALUES  ({?})', $g);
            $fids[$g] = XDB::insertId();
        }

        foreach ($groups as $g) {
            XDB::execute('INSERT INTO  forum_subs (fid, uid)
                               VALUES  ({?}, {?})',
                         $fids[$g], $uid);
            Banana::$profile['subscribe'][] = $g;
        }
    }

    protected function action_updateProfile()
    {
        global $globals;
        $page =& Platal::page();

        $colors = glob(dirname(__FILE__) . '/../../htdocs/images/banana/m2*.gif');
        foreach ($colors as $key=>$path) {
            $path = basename($path, '.gif');
            $colors[$key] = substr($path, 2);
        }
        $page->assign('colors', $colors);

        if (Post::has('action') && Post::v('action') == 'Enregistrer') {
            S::assert_xsrf_token();
            $flags = new PlFlagSet();
            if (Post::b('bananadisplay')) {
                $flags->addFlag('threads');
            }
            if (Post::b('bananaupdate')) {
                $flags->addFlag('automaj');
            }
            if (Post::b('bananaxface')) {
                $flags->addFlag('xface');
            }
            $unread = Post::s('unread');
            $read = Post::s('read');
            if (!in_array($unread, $colors) || !in_array($read, $colors)) {
                $page->trigError('Le choix de type pour l\'arborescence est invalide');
            } else {
                $last_seen = XDB::query('SELECT  last_seen
                                           FROM  forum_profiles
                                          WHERE  uid = {?}', $this->user->id());
                if ($last_seen->numRows() > 0) {
                    $last_seen = $last_seen->fetchOneCell();
                } else {
                    $last_seen = '0000-00-00';
                }
                XDB::execute('REPLACE INTO  forum_profiles (uid, sig, mail, name, flags, tree_unread, tree_read, last_seen)
                                    VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                             $this->user->id(), Post::v('bananasig'),
                             Post::v('bananamail'), Post::v('banananame'),
                             $flags, $unread, $read, $last_seen);
                $page->trigSuccess('Ton profil a été mis à jour');
            }
        }

        $req = XDB::query('SELECT  name, mail, sig,
                                   FIND_IN_SET(\'threads\', flags),
                                   FIND_IN_SET(\'automaj\', flags),
                                   FIND_IN_SET(\'xface\', flags),
                                   tree_unread, tree_read
                             FROM  forum_profiles
                            WHERE  uid = {?}', $this->user->id());
        if (!((list($nom, $mail, $sig, $disp, $maj, $xface, $unread, $read) = $req->fetchOneRow())) || !$nom) {
            $nom   = $this->user->fullName();
            $mail  = $this->user->forlifeEmail();
            $sig   = $this->user->displayName();
            $disp  = 0;
            $maj   = 0;
            $xface = 0;
            $unread = 'o';
            $read  = 'dg';
        }
        $page->assign('nom' ,  $nom);
        $page->assign('mail',  $mail);
        $page->assign('sig',   $sig);
        $page->assign('disp',  $disp);
        $page->assign('maj',   $maj);
        $page->assign('xface', $xface);
        $page->assign('unread', $unread);
        $page->assign('read', $read);
        return null;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
