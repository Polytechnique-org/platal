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
    return ($_headers['x-org-id'] == S::v('forlife') or S::has_perms());
}

class ForumsBanana extends Banana
{
    private $forlife;

    public function __construct($forlife, $params = null)
    {
        $this->forlife = $forlife;

        global $globals;
        Banana::$msgedit_canattach = false;
        Banana::$spool_root = $globals->banana->spool_root;
        array_push(Banana::$msgparse_headers, 'x-org-id', 'x-org-mail');
        Banana::$nntp_host = 'news://web_'.$forlife
                           . ":{$globals->banana->password}@{$globals->banana->server}:{$globals->banana->port}/";
        if (S::has_perms()) {
            Banana::$msgshow_mimeparts[] = 'source';
        }
        Banana::$debug_nntp = ($globals->debug & DEBUG_BT);
        Banana::$debug_smarty = ($globals->debug & DEBUG_SMARTY);
        if (!S::v('core_rss_hash')) {
            Banana::$feed_active = false;
        }
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
            $_SESSION['banana_last']     = $time;
        }

        // Get user profile from SQL
        $req = XDB::query("SELECT  nom, mail, sig,
                                   FIND_IN_SET('threads',flags), FIND_IN_SET('automaj',flags)
                             FROM  {$globals->banana->table_prefix}profils
                            WHERE  uid={?}", S::i('uid'));
        if (!(list($nom,$mail,$sig,$disp,$maj) = $req->fetchOneRow())) {
            $nom  = S::v('prenom')." ".S::v('nom');
            $mail = S::v('forlife')."@" . $globals->mail->domain;
            $sig  = $nom." (".S::v('promo').")";
            $disp = 0;
            $maj  = 1;
        }
        if ($maj) {
            $time = time();
        }

        // Build user profile
        $req = XDB::query("
                 SELECT  nom
                   FROM  {$globals->banana->table_prefix}abos
              LEFT JOIN  {$globals->banana->table_prefix}list ON list.fid=abos.fid
                  WHERE  uid={?}", S::i('uid'));
        Banana::$profile['headers']['From']         = "$nom <$mail>";
        Banana::$profile['headers']['Organization'] = make_Organization();
        Banana::$profile['signature']               = $sig;
        Banana::$profile['display']                 = $disp;
        Banana::$profile['autoup']                  = $maj;
        Banana::$profile['lastnews']                = S::v('banana_last');
        Banana::$profile['subscribe']               = $req->fetchColumn();

        // Update the "unread limit"
        if (!is_null($time)) {
            XDB::execute("UPDATE  auth_user_quick
                             SET  banana_last = FROM_UNIXTIME({?})
                           WHERE  user_id={?}",
                         $time, S::i('uid'));
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
        $res = XDB::query('SELECT  nom, prenom, promo, b.alias AS bestalias
                             FROM  auth_user_md5 AS u
                       INNER JOIN  aliases       AS a ON (a.id = u.user_id)
                       INNER JOIN  aliases       AS b ON (b.id = a.id AND FIND_IN_SET(\'bestalias\', b.flags))
                            WHERE  a.alias = {?}', $this->forlife);
        list($nom, $prenom, $promo, $bestalias) = $res->fetchOneRow();
        Banana::$profile['headers']['From']         = "$prenom $nom ($promo) <$bestalias@{$globals->mail->domain}>";
        Banana::$profile['headers']['Organization'] = make_Organization();
        return parent::post($dest, $reply, $subject, $body);
    }

    protected function action_saveSubs($groups)
    {
        global $globals;
        $uid = S::v('uid');

        Banana::$profile['subscribe'] = array();
        XDB::execute("DELETE FROM {$globals->banana->table_prefix}abos WHERE uid={?}", $uid);
        if (!count($groups)) {
            return true;
        }

        $req  = XDB::iterRow("SELECT fid,nom FROM {$globals->banana->table_prefix}list");
        $fids = array();
        while (list($fid,$fnom) = $req->next()) {
            $fids[$fnom] = $fid;
        }

        $diff = array_diff($groups, array_keys($fids));
        foreach ($diff as $g) {
            XDB::execute("INSERT INTO {$globals->banana->table_prefix}list (nom) VALUES ({?})", $g);
            $fids[$g] = XDB::insertId();
        }

        foreach ($groups as $g) {
            XDB::execute("INSERT INTO {$globals->banana->table_prefix}abos (fid,uid) VALUES ({?},{?})",
                         $fids[$g], $uid);
            Banana::$profile['subscribe'][] = $g;
        }
    }

    protected function action_updateProfile()
    {
        global $page, $globals;

        if (Post::has('action') && Post::has('banananame') && Post::has('bananasig')
                && Post::has('bananadisplay') && Post::has('bananamail')
                && Post::has('bananaupdate') && Post::v('action')=="Enregistrer" ) {
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
            if (XDB::execute("REPLACE INTO  forums.profils (uid, sig, mail, nom, flags)
                                VALUES  ({?}, {?}, {?}, {?}, {?})",
                         S::v('uid'), Post::v('bananasig'),
                         Post::v('bananamail'), Post::v('banananame'),
                         $flags)) {
                $page->trigSuccess("Ton profil a été enregistré avec succès.");
            } else {
                $page->trigError("Une erreur s'est produite lors de l'enregistrement de ton profil");
            }
        }

        $req = XDB::query("
            SELECT  nom, mail, sig,
                    FIND_IN_SET('threads', flags),
                    FIND_IN_SET('automaj', flags),
                    FIND_IN_SET('xface', flags)
              FROM  forums.profils
             WHERE  uid = {?}", S::v('uid'));
        if (!(list($nom, $mail, $sig, $disp, $maj, $xface) = $req->fetchOneRow())) {
            $nom   = S::v('prenom').' '.S::v('nom');
            $mail  = S::v('forlife').'@'.$globals->mail->domain;
            $sig   = $nom.' ('.S::v('promo').')';
            $disp  = 0;
            $maj   = 0;
            $xface = 0;
        }
        $page->assign('nom' ,  $nom);
        $page->assign('mail',  $mail);
        $page->assign('sig',   $sig);
        $page->assign('disp',  $disp);
        $page->assign('maj',   $maj);
        $page->assign('xface', $xface);
        return null;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
