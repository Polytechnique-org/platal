<?php

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
