<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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


require_once("xorg.inc.php");
new_admin_page('admin/synchro_ax.tpl');

require_once('user.func.inc.php');
require_once('synchro_ax.inc.php');

if (Env::has('user')) {
    $login = get_user_forlife(Env::get('user'));
    if ($login === false) {
        $page->kill("");
    }
}

if (Env::has('mat')) {
    $res = $globals->xdb->query(
            "SELECT  alias 
               FROM  aliases       AS a
         INNER JOIN  auth_user_md5 AS u ON (a.id=u.user_id AND a.type='a_vie')
              WHERE  matricule={?}", Env::getInt('mat'));
    $login = $res->fetchOneCell();
}

if ($login) {
    $new   = Env::get('modif') == 'new';
    $user  = get_user_details($login, Session::getInt('uid'));
    $userax= get_user_ax($user['user_id']);

    if (Env::has('importe')) {

        $adr_dels = array();
        foreach ($user['adr'] as $adr) {
            if (Env::has('del_address'.$adr['adrid'])) {
                $adr_dels[] = $adr['adrid'];
            }
        }

        $adr_adds = array();
        foreach ($userax['adr'] as $i => $adr) {
            if (Env::has('add_address'.$i)) {
                $adr_adds[] = $i;
            }
        }

        $pro_dels = array();
        foreach ($user['adr_pro'] as $pro) {
            if (Env::has('del_pro'.$pro['entrid'])) {
                $pro_dels[] = $pro['entrid'];
            }
        }

        $pro_adds = array();
        foreach ($userax['adr_pro'] as $i => $pro) {
            if (Env::has('add_pro'.$i)) {
                $pro_adds[] = $i;
            }
        }

        import_from_ax($userax, Env::has('nom_usage'), Env::has('mobile'), $adr_dels, $adr_adds, $pro_dels, $pro_adds, Env::has('nationalite'));

    }

    $user  = get_user_details($login, Session::getInt('uid'));
    
    if ($userax) {
        $user['matricule_ax'] = $userax['matricule_ax'];
        unset($userax['matricule_ax']);
        $user['nom'] = ucwords(strtolower($user['nom']));
        $user['nom_usage'] = ucwords(strtolower($user['nom_usage']));
    }

    $page->assign('watch_champs',array('nom', 'nom_usage', 'prenom', 'nationalite', 'mobile'));
    $page->assign('modifiables', array(0,1,0,1,1));

    $page->assign('x', $user);
    $page->assign('ax', $userax); 
}
$page->run();

// vim:set et sts=4 sws=4 sw=4:
?>
