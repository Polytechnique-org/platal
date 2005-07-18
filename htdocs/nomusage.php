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
require_once("validations.inc.php");
require_once("xorg.misc.inc.php");

new_skinned_page('nomusage.tpl', AUTH_MDP);

$res = $globals->xdb->query(
        "SELECT  u.nom,u.nom_usage,u.flags,e.alias
           FROM  auth_user_md5  AS u
      LEFT JOIN  aliases        AS e ON(u.user_id = e.id)
          WHERE  user_id={?} AND FIND_IN_SET('usage', e.flags)", Session::getInt('uid'));

list($nom,$usage_old,$flags,$alias_old) = $res->fetchOneRow();
$flags = new flagset($flags);
$page->assign('usage_old', $usage_old);
$page->assign('alias_old',  $alias_old);

$nom_usage = replace_accent(trim(Env::get('nom_usage'))); 
$nom_usage = strtoupper($nom_usage);
$page->assign('usage_req', $nom_usage);

if (Env::has('submit') && ($nom_usage != $usage_old)) {
    // on vient de recevoir une requete, differente de l'ancien nom d'usage
    if ($nom_usage == $nom) {
        $page->assign('same', true);
    } else { // le nom de mariage est distinct du nom à l'X
        // on calcule l'alias pour l'afficher
        $myusage = new UsageReq(Session::getInt('uid'), $nom_usage);
        $myusage->submit();
        $page->assign('myusage', $myusage);
    }
}

$page->run();
?>
