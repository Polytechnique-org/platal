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
new_simple_page('fiche.tpl',AUTH_COOKIE);
require_once('user.func.inc.php');

if (!Env::has('user') && !Env::has('mat')) {
    $page->kill("cette page n'existe pas");
}

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
    if (empty($login)) {
        $page->kill("cette page n'existe pas");
    }
}

$new   = Env::get('modif') == 'new';
$user  = get_user_details($login, Session::getInt('uid'));
$title = $user['prenom'] . ' ' . empty($user['epouse']) ? $user['nom'] : $user['epouse'];
$page->assign('xorg_title', $title);

// photo

$photo = 'getphoto.php?x='.($new ? $user['user_id'].'&amp;req=true' : $user['forlife']);

if(!isset($user['y']) and !isset($user['x'])) {
    list($user['x'], $user['y']) = getimagesize("images/none.png");
}
if(!isset($user['y']) or $user['y'] < 1) $user['y']=1;
if(!isset($user['x']) or $user['x'] < 1) $user['x']=1;
if($user['x'] > 240){
    $user['y'] = (integer)($user['y']*240/$user['x']);
    $user['x'] = 240;
}
if($user['y'] > 300){
    $user['x'] = (integer)($user['x']*300/$user['y']);
    $user['y'] = 300;
}
if($user['x'] < 160){
    $user['y'] = (integer)($user['y']*160/$user['x']);
    $user['x'] = 160;
}
$page->assign('photo_url', $photo);
$page->assign_by_ref('x', $user);

// alias virtual
$res = $globals->xdb->query(
	"SELECT alias
	   FROM virtual
     INNER JOIN virtual_redirect USING(vid)
     INNER JOIN auth_user_quick  ON ( user_id = {?} AND emails_alias_pub = 'public' )
          WHERE ( redirect={?} OR redirect={?} )
	        AND alias LIKE '%@{$globals->mail->alias_dom}'",
        Session::getInt('uid'), $user['forlife'].'@'.$globals->mail->domain, $user['forlife'].'@'.$globals->mail->domain2);
$page->assign('virtualalias', $res->fetchOneCell());
$page->run();

?>
