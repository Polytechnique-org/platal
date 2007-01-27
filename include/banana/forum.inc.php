<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

function hook_makeLink($params)
{
    global $globals;
    $base = $globals->baseurl . '/banana';
    if (isset($params['page'])) {
        return $base . '/' . $params['page'];
    }
    if (@$params['action'] == 'subscribe') {
        return $base . '/subscription';
    }

    if (!isset($params['group'])) {
        return $base;
    }
    $base .= '/' . $params['group'];

    return $base . hook_platalMessageLink($params);
}

class ForumsBanana extends Banana
{
    function __construct($params = null)
    {
        global $globals;
        Banana::$msgedit_canattach = false;
        array_push(Banana::$msgparse_headers, 'x-org-id', 'x-org-mail');
        Banana::$nntp_host = 'news://web_'.S::v('forlife')
                           . ":{$globals->banana->password}@{$globals->banana->server}:{$globals->banana->port}/";
        if (S::has_perms()) {
            Banana::$msgshow_mimeparts[] = 'source';
        }
        parent::__construct($params);
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
            $mail = S::v('forlife')."@polytechnique.org";
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
        Banana::$profile['headers']['From']         = utf8_encode("$nom <$mail>");
        Banana::$profile['headers']['Organization'] = 'Utilisateur de Polytechnique.org';
        Banana::$profile['signature']               = utf8_encode($sig);
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

        // Register custom Banana links and tabs
        if (!Banana::$profile['autoup']) {
            Banana::$page->registerAction('<a href=\'javascript:dynpostkv("'
                                . $platal->path . '", "updateall", ' . time() . ')\'>'
                                . 'Marquer tous les messages comme lus'
                                . '</a>', array('forums', 'thread', 'message'));
        }   
        Banana::$page->registerPage('profile', utf8_encode('Préférences'), null);
        

        // Run Banana
        return parent::run();
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
}

?>
