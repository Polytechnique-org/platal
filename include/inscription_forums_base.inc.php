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
        $Id: inscription_forums_base.inc.php,v 1.2 2004-08-31 11:16:48 x2000habouzit Exp $
 ***************************************************************************/


/** inscrit l'uid donnée au forum promo 
 * @param $uid UID
 * @param $promo promo
 * @return la reponse MySQL
 * @see step4.php
 */
function inscription_forum_promo($uid,$promo) {
  global $globals;
  // récupération de l'id du forum promo
  $result=$globals->db->query("SELECT fid FROM forums.list WHERE nom='xorg.promo.x$promo'");
  if (!list($fid)=mysql_fetch_row($result)) { // pas de forum promo, il faut le créer
    $req_au=$globals->db->query("SELECT count(*) FROM auth_user_md5 WHERE promo='$promo'");
    list($effau) = mysql_fetch_row($req_au);
    $req_id=$globals->db->query("SELECT count(*) FROM identification WHERE promo='$promo'");
    list($effid) = mysql_fetch_row($req_id);
    if (5*$effau>$effid) { // + de 20% d'inscrits
        $mymail = new TplMailer('forums.promo.tpl');
        $mymail->assign('promo', $promo);
        $mymail->send();
    }
    $fid = false; 
  }
  mysql_free_result($result);
  if ($fid) {
    $globals->db->query("INSERT INTO forums.abos (fid,uid) VALUES ('$fid','$uid')");
    $res = !($globals->db->err());
  } else  $res = false;
  return $res;
} 


/** inscrit UID aux forums par défaut
 * @param $uid UID
 * @return la reponse MySQL globale
 * @see step4.php
 */
function inscription_forums($uid) {
    global $globals;
    $res = true;
    $cible = array('xorg.general','xorg.pa.emploi','xorg.pa.divers','xorg.pa.logements');
    while (list ($key, $val) = each ($cible)) {
        $result=$globals->db->query("SELECT fid FROM forums.list WHERE nom='$val'");
        list($fid)=mysql_fetch_row($result);
        $globals->db->query("INSERT INTO forums.abos (fid,uid) VALUES ('$fid','$uid')");
        $res = $res and !($globals->db->err());
    }
    return $res;
}



?>
