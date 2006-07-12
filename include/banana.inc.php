<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

require_once('banana/banana.inc.php');

function hook_formatDisplayHeader($_header, $_text) {
    global $globals, $banana;
    if ($_header == 'from') {
        $id = $banana->post->headers['x-org-id'];
        $_text = formatFrom($_text);
        return $_text . ' <a href="' . $globals->baseurl . '/profile/' 
             . $id . '" class="popup2" title="' . $id . '">'
             . '<img src="' . $globals->baseurl . '/images/loupe.gif" alt="fiche" title="fiche" />'
             . '</a>';
    }
}

function hook_checkcancel($_headers) {
    return ($_headers['x-org-id'] == Session::get('forlife') or has_perms());
}

function hook_makeLink($params) {
    global $globals;
    $base = $globals->baseurl . '/banana'; 
    if ($params['subscribe'] == 1) {
        return $base . '/subscription';
    }
    if (isset($params['xface'])) {
        return $base . '/xface/' . $params['xface'];
    }

    if (!isset($params['group'])) {
        return $base;
    }
    $base .= '/' . $params['group'];

    if (isset($params['first'])) {
        return $base . '/from/' . $params['first'];
    }
    if (isset($params['artid'])) {
        if ($params['action'] == 'new') {
            $base .= '/reply';
        } elseif ($params['action'] == 'cancel') {
            $base .= '/cancel';
        } else {
            $base .= '/read';
        }
        return $base . '/' . $params['artid'];
    }
    
    if ($params['action'] == 'new') {
        return $base . '/new';
    }
    return $base;
}

function hook_makeImg($img, $alt, $height, $width)
{
    global $globals;
    $url = $globals->baseurl . '/images/banana/' . $img . '.gif';

    if (!is_null($width)) {
        $width = ' width="' . $width . '"';
    }
    if (!is_null($height)) {
        $height = ' height="' . $height . '"';
    }
    
    return '<img src="' . $url . '"' . $height . $width . ' alt="' . $alt . '" />';
}

function hook_getSubject(&$subject)
{
    if (preg_match('!(.*\S)\s*\[=> ([^\]\s]+)\]!', $subject, $matches)) {
        $subject = $matches[1];
        global $banana;
        if ($banana->state['group'] == $matches[2]) {
            return ' [=> ' . $matches[2] . ']';
        } else {
            return ' [=> ' . makeHREF(Array('group' => $matches[2]), $matches[2]) . ']';
        }
    }
    return null;
}

class PlatalBanana extends Banana
{
    var $profile    = Array( 'name' => '', 'sig'  => '', 'org'  => 'Utilisateur de Polytechnique.org',
            'customhdr' =>'', 'display' => 0, 'lastnews' => 0, 'locale'  => 'fr_FR', 'subscribe' => array());
    var $can_attach = false;

    function PlatalBanana()
    {
        global $globals;
    
        $uid = Session::getInt('uid');
        $req = $globals->xdb->query(
                "SELECT  nom, mail, sig, FIND_IN_SET('threads',flags), FIND_IN_SET('automaj',flags)
                   FROM  {$globals->banana->table_prefix}profils
                  WHERE  uid={?}", $uid);

        if (!(list($nom,$mail,$sig,$disp,$maj) = $req->fetchOneRow())) {
            $nom  = Session::get('prenom')." ".Session::get('nom');
            $mail = Session::get('forlife')."@polytechnique.org";
            $sig  = $nom." (".Session::getInt('promo').")";
            $disp = 0;
            $maj  = 1;
        }
        $this->profile['name']      = "$nom <$mail>";
        $this->profile['sig']       = $sig;
        $this->profile['display']   = $disp;
        $this->profile['autoup']    = $maj;
        $this->profile['lastnews']  = Session::get('banana_last');
        
        if ($maj) {
            $globals->xdb->execute("UPDATE auth_user_quick SET banana_last={?} WHERE user_id={?}", gmdate("YmdHis"), $uid);
        }

        $req = $globals->xdb->query("
                 SELECT  nom
                   FROM  {$globals->banana->table_prefix}abos
              LEFT JOIN  {$globals->banana->table_prefix}list ON list.fid=abos.fid
                  WHERE  uid={?}", $uid);
        $this->profile['subscribe'] = $req->fetchColumn();

        array_splice($this->show_hdr,  count($this->show_hdr)  - 2, 0);
        array_splice($this->parse_hdr, count($this->parse_hdr) - 2, 0, 'x-org-id');

        $this->host = 'news://web_'.Session::get('forlife')
            .":{$globals->banana->password}@{$globals->banana->server}:{$globals->banana->port}/";

        parent::Banana();
    }

    function run($params = null)
    {
        global $banana, $globals;

        if (Get::get('banana') == 'updateall'
                || (!is_null($params) && isset($params['banana']) && $params['banana'] == 'updateall')) {
            $globals->xdb->execute('UPDATE auth_user_quick SET banana_last={?} WHERE user_id={?}', gmdate('YmdHis'), Session::getInt('uid'));
            $_SESSION['banana_last'] = time();
        }
        return Banana::run('PlatalBanana', $params);
    }

    function action_saveSubs()
    {
        global $globals;
        $uid = Session::getInt('uid');

        $this->profile['subscribe'] = Array();
        $globals->xdb->execute("DELETE FROM {$globals->banana->table_prefix}abos WHERE uid={?}", $uid);
        if (!count($_POST['subscribe'])) {
            return true;
        }
        
        $req  = $globals->xdb->iterRow("SELECT fid,nom FROM {$globals->banana->table_prefix}list");
        $fids = array();
        while (list($fid,$fnom) = $req->next()) {
            $fids[$fnom] = $fid;
        }

        $diff = array_diff($_POST['subscribe'], array_keys($fids));
        foreach ($diff as $g) {
            $globals->xdb->execute("INSERT INTO {$globals->banana->table_prefix}list (nom) VALUES ({?})", $g);
            $fids[$g] = mysql_insert_id();
        }

        foreach ($_POST['subscribe'] as $g) {
            $globals->xdb->execute("INSERT INTO {$globals->banana->table_prefix}abos (fid,uid) VALUES ({?},{?})", $fids[$g], $uid);
            $this->profile['subscribe'][] = $g;
        }
    }
}

?>
