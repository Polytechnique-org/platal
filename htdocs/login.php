<?php
require("auto.prepend.inc.php");
new_skinned_page('login.tpl', AUTH_COOKIE, 'login.head.tpl');

$param=mysql_query("SELECT date,naissance FROM auth_user_md5 WHERE user_id={$_SESSION['uid']};");
list($date,$naissance) = mysql_fetch_row($param);
mysql_free_result($param);

if ($date=="1999-12-31")  {
    $page->assign('date', $date);
    $page->display('non-inscrit');
    exit;
}

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

$publicite = Array(Array(), Array());
$publicite[0]["motdepassemd5.php"] = "Changer mon mot de passe";
$i = rand(0, 1);
switch ($i) {
    case 0 :
        $publicite[0]["newsletter.php?nl_id=$nb"]="Afficher la dernière newsletter"; break;
    case 1 :
        $publicite[0]["http://asso.polytechnique.org\" target=\"new"]="Vers les autres sites polytechniciens"; break;
}
$i = rand(0, 1);
switch ($i) {
    case 0 :
        $publicite[1]["trombipromo.php?xpromo={$_SESSION["promo"]}"]="Voir le trombi de ma promo"; break;
    case 1 :
        $publicite[1]["banana/"]="Un petit tour du côté des forums !!"; break;
}
$publicite[1]["dons.php"] = "Faire un don à l'association Polytechnique.org";
$page->assign_by_ref('publicite', $publicite);


//affichage des evenements
// annonces promos triées par présence d'une limite sur les promos
// puis par dates croissantes d'expiration
$res = mysql_query(
        "SELECT e.id,e.titre,e.texte,a.username,a.nom,a.prenom,a.promo
        FROM evenements AS e INNER JOIN auth_user_md5 AS a
        ON e.user_id=a.user_id
        WHERE FIND_IN_SET(flags, 'valide') AND peremption >= NOW()
        AND (e.promo_min = 0 || e.promo_min <= {$_SESSION['promo']})
        AND (e.promo_max = 0 || e.promo_max >= {$_SESSION['promo']})
        ORDER BY (e.promo_min != 0 AND  e.promo_max != 0) DESC,  e.peremption");
$evenement = Array();
while($evenement[] = mysql_fetch_assoc($res));
@array_pop($evenement);
mysql_free_result($res);
$page->assign_by_ref('evenement', $evenement);

setlocale(LC_TIME, "fr_FR");
$page->display();
?>
