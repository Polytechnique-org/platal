<?php



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
