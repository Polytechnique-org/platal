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
 ***************************************************************************
        $Id: epouse.php,v 1.9 2004-10-19 22:05:09 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
require("validations.inc.php");
require("xorg.misc.inc.php");

new_skinned_page('epouse.tpl', AUTH_MDP);

$res = $globals->db->query(
    "SELECT  u.nom,u.epouse,i.flags,e.alias
       FROM  auth_user_md5  AS u
  LEFT JOIN  identification AS i USING(matricule)
  LEFT JOIN  aliases        AS e ON(u.user_id = e.id)
      WHERE  user_id=".$_SESSION['uid']);

list($nom,$epouse_old,$flags,$alias_old) = mysql_fetch_row($res);
$flags=new flagset($flags);
$page->assign('is_femme',$flags->hasflag("femme"));
$page->assign('epouse_old',$epouse_old);
$page->assign('alias_old',$alias_old);

$epouse = replace_accent(trim(clean_request('epouse'))); 
$epouse = strtoupper($epouse);
$page->assign('epouse_req',$epouse);

if (!empty($_REQUEST['submit']) && ($epouse != $epouse_old)) {
    // on vient de recevoir une requete, differente de l'ancien nom de mariage
    if ($epouse == $nom) {
        $page->assign('same',true);
    } else { // le nom de mariage est distinct du nom à l'X
        // on calcule l'alias pour l'afficher
        $myepouse = new EpouseReq($_SESSION['uid'], $_SESSION['forlife'], $epouse);
        $myepouse->submit();
        $page->assign('myepouse',$myepouse);
    }
}

$page->run($flags->hasflag("femme") ? '' : 'not_femme');
?>
