<?php
require("auto.prepend.inc.php");
new_skinned_page('login.tpl', AUTH_COOKIE, true);

$param=mysql_query("SELECT date,naissance FROM auth_user_md5 WHERE user_id={$_SESSION['uid']};");
list($date,$naissance) = mysql_fetch_row($param);
mysql_free_result($param);

if ($naissance==0)  {
    $page->assign('ask_naissance', true);
    $page->display('ask-naissance');
    exit;
}

// incitation à mettre à jour la fiche

$res = mysql_query("SELECT date FROM auth_user_md5 WHERE user_id=".$_SESSION["uid"]);
list($d) = mysql_fetch_row($res);
$date_maj = mktime(0, 0, 0, substr($d, 5, 2), substr($d, 8, 2), substr($d, 0, 4));
if(( (time() - $date_maj) > 60 * 60 * 24 * 400)) { // si fiche date de + de 400j;
    $page->assign('fiche_incitation', $d);
}

// incitation à mettre une photo

$res = mysql_query("SELECT 1 FROM photo WHERE uid=".$_SESSION["uid"]);
if (mysql_num_rows($res) == 0)
    $page->assign('photo_incitation', true);
mysql_free_result($res);

// affichage de la boîte avec quelques liens

$res = mysql_query("SELECT id FROM newsletter ORDER BY date DESC");
list($nb) = mysql_fetch_row($res);
mysql_free_result($res);

include('login.conf.php') ;
$pub_nbElem = $pub_nbLig * $pub_nbCol ;
if (count($pub_tjs) <= $pub_nbElem)
    $publicite = array_slice ($pub_tjs,0,$pub_nbElem) ;
else
    $publicite = $pub_tjs ;
$nbAlea = $pub_nbElem - count($publicite) ;
if ($nbAlea > 0) {
    $choix = array_rand($pub_rnd,$nbAlea) ;
    foreach ($choix as $url)
        $publicite[$url] = $pub_rnd[$url] ;
    }
$publicite = array_chunk( $publicite , $pub_nbLig , true ) ;
$page->assign_by_ref('publicite', $publicite);


// affichage des evenements
// annonces promos triées par présence d'une limite sur les promos
// puis par dates croissantes d'expiration
$sql = "SELECT e.id,e.titre,e.texte,a.username,a.nom,a.prenom,a.promo
        FROM evenements AS e INNER JOIN auth_user_md5 AS a
        ON e.user_id=a.user_id
        WHERE FIND_IN_SET(flags, 'valide') AND peremption >= NOW()
        AND (e.promo_min = 0 || e.promo_min <= {$_SESSION['promo']})
        AND (e.promo_max = 0 || e.promo_max >= {$_SESSION['promo']})
        ORDER BY (e.promo_min != 0 AND  e.promo_max != 0) DESC,  e.peremption";
$page->mysql_assign($sql, 'evenement');

$page->display();
?>
