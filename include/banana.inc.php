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

function hook_formatDisplayHeader($_header,$_text) {
    global $banana;
    if ($_header == 'x-org-id') {
        return "$_text [<a href=\"../fiche.php?user=$_text\" class='popup2'>fiche</a>]";
    }
}

function hook_headerTranslate($hdr) {
    if ($hdr == 'x-org-id') {
        return 'Identité';
    }
}

function hook_checkcancel($_headers) {
    return ($_headers['x-org-id'] == Session::get('forlife') or has_perms());
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

        $req = $globals->xdb->query(
                "SELECT  nom
                   FROM  {$globals->banana->table_prefix}abos
              LEFT JOIN  {$globals->banana->table_prefix}list ON list.fid=abos.fid
                  WHERE  uid={?}", $uid);
        $this->profile['subscribe'] = $req->fetchColumn();

        array_splice($this->show_hdr,  count($this->show_hdr)  - 2, 0, 'x-org-id');
        array_splice($this->parse_hdr, count($this->parse_hdr) - 2, 0, 'x-org-id');

        $this->host = 'news://web_'.Session::get('forlife')
            .":{$globals->banana->password}@{$globals->banana->server}:{$globals->banana->port}/";

        parent::Banana();
    }

    function run()
    {
        global $banana, $globals;

        if (Get::get('banana') == 'updateall') {
            $globals->xdb->execute('UPDATE auth_user_quick SET banana_last={?} WHERE user_id={?}', gmdate('YmdHis'), Session::getInt('uid'));
            $_SESSION['banana_last'] = time();
            redirect($_SERVER['PHP_SELF']);
        }
        return Banana::run('PlatalBanana');
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
