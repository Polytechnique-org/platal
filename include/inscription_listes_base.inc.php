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
        $Id: inscription_listes_base.inc.php,v 1.2 2004-08-31 11:16:48 x2000habouzit Exp $
 ***************************************************************************/




/** inscrit l'uid donnée à la promo
 * @param $uid UID
 * @param $promo promo
 * @return reponse MySQL
 * @see admin/RegisterNewUser.php
 * @see step4.php
 */
function inscription_liste_promo($uid,$promo) {
  global $globals;
  // récupération de l'id de la liste promo
  $result=$globals->db->query("select id from aliases where alias = 'promo$promo' and type = 'liste'");
  if (!list($Lid)=mysql_fetch_row($result)) { // pas de liste promo, il faut la créer
        $mymail = new TplMailer('listes.promo.tpl');
        $mymail->assign('promo', $promo);
        $mymail->send();
        $Lid=false;
  }
  mysql_free_result($result);
  if ($Lid) {
    $globals->db->query("insert into listes_ins set idl=$Lid, idu=$uid");
    $res = !($globals->db->err());
  } else  $res = false;
  return $res;
}



/** inscription à la newsletter
 * @param $uid UID
 * @return reponse MySQL
 * @see admin/RegisterNewUser.php
 * @see step4.php
 */
function inscription_newsletter($uid) {
    global $globals;
    $result=$globals->db->query("select id from aliases where alias = 'newsletter' and type = 'liste'");
    if (list($Lid)=mysql_fetch_row($result)) {
        $globals->db->query("insert into listes_ins set idl=$Lid, idu=$uid");
        $res = !($globals->db->err());
    } else $res = false;
    mysql_free_result($result);
    return $res;
}

?>
