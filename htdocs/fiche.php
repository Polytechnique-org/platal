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
new_simple_page('fiche.tpl', AUTH_PUBLIC);
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
$title = $user['prenom'] . ' ' . empty($user['nom_usage']) ? $user['nom'] : $user['nom_usage'];
$page->assign('xorg_title', $title);

// photo

$photo = 'getphoto.php?x='.$user['forlife'].($new ? '&amp;req=true' : '');

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

// manage the public fiche
$page->assign('logged', logged());
if (!logged()) {
    // hide the orange status
    $user['promo_sortie'] = $user['promo'] + 3;
    if ($user['mobile_pub'] != 'public') $user['mobile'] = '';
    if ($user['web_pub'] != 'public') $user['web'] = '';
    if ($user['freetext_pub'] !=  'public') $user['freetext'] = '';
    foreach ($user['adr'] as $i=>$adr) {
        if ($adr['pub'] != 'public' && $adr['tel_pub'] != 'public')
            unset($user['adr'][$i]);
        elseif ($adr['pub'] != 'public') {
            $user['adr'][$i]['adr1'] = '';
            $user['adr'][$i]['adr2'] = '';
            $user['adr'][$i]['adr3'] = '';
            $user['adr'][$i]['city'] = '';
            $user['adr'][$i]['postcode'] = '';
            $user['adr'][$i]['region'] = '';
            $user['adr'][$i]['country'] = '00';
            $user['adr'][$i]['countrytxt'] = '';
        }
        elseif ($adr['tel_pub'] != 'public') {
            $user['adr'][$i]['tel'] = '';
            $user['adr'][$i]['fax'] = '';
        }
    }
    foreach ($user['adr_pro'] as $i=>$adr) {
        if ($adr['pub'] != 'public' && $adr['tel_pub'] != 'public' && $adr['adr_pub'] != 'public' && $adr['email_pub'] != 'public')
            unset($user['adr_pro'][$i]);
        else {
            if ($adr['adr_pub'] != 'public') {
                $user['adr_pro'][$i]['adr1'] = '';
                $user['adr_pro'][$i]['adr2'] = '';
                $user['adr_pro'][$i]['adr3'] = '';
                $user['adr_pro'][$i]['city'] = '';
                $user['adr_pro'][$i]['postcode'] = '';
                $user['adr_pro'][$i]['region'] = '';
                $user['adr_pro'][$i]['country'] = '00';
                $user['adr_pro'][$i]['countrytxt'] = '';
            }
            if ($adr['pub'] != 'public') {
                $user['adr_pro'][$i]['entreprise'] = '';
                $user['adr_pro'][$i]['secteur'] = '';
                $user['adr_pro'][$i]['fonction'] = '';
                $user['adr_pro'][$i]['poste'] = '';
            }
            if ($adr['tel_pub'] != 'public') {
                $user['adr_pro'][$i]['tel'] = '';
                $user['adr_pro'][$i]['fax'] = '';
                $user['adr_pro'][$i]['mobile'] = '';
            }
            if ($adr['email_pub'] != 'public') {
                $user['adr_pro'][$i]['email'] = '';
            }
        }
    }
    if ($user['medals_pub'] != 'public') {
        unset($user['medals']);
    }
    if ($user['photo_pub'] != 'public') {
        $photo = "";
    }
}
foreach($user['adr_pro'] as $i=>$pro) {
    if ($pro['entreprise'] == '' && $pro['fonction'] == ''
        && $pro['secteur'] == '' && $pro['poste'] == ''
        && $pro['adr1'] == '' && $pro['adr2'] == '' && $pro['adr3'] == ''
        && $pro['postcode'] == '' && $pro['city'] == '' && $pro['country'] == ''
        && $pro['tel'] == '' && $pro['fax'] == '' && $pro['mobile'] == ''
        && $pro['email'] == '')
        unset($user['adr_pro'][$i]);
}
if (count($user['adr_pro']) == 0) unset($user['adr_pro']);
if (count($user['adr']) == 0) unset($user['adr']);
$page->assign_by_ref('x', $user);

$page->assign('photo_url', $photo);
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

$page->addJsLink('javascript/close_on_esc.js');
$page->run();

// vim:set et sws=4 sw=4 sts=4:
?>
