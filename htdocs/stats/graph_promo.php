<?php
require("auto.prepend.inc.php");
new_skinned_page('index.tpl',AUTH_COOKIE);


// genere le graph de l'evolution du nombre d'inscrits dans une promotion 

$promo = (isset($_REQUEST['promo']) ? intval($_REQUEST["promo"]) : $_SESSION["promo"]);

//nombre de jours sur le graph
$JOURS=364;
define('DUREEJOUR',24*3600);

//recupere le nombre d'inscriptions par jour sur la plage concernée
$donnees=$globals->db->query("SELECT if(date_ins>DATE_SUB(NOW(),INTERVAL $JOURS DAY), TO_DAYS(date_ins)-TO_DAYS(NOW()), ".(-($JOURS+1)).") AS jour,
                             count(username) AS nb
                      FROM auth_user_md5 WHERE promo = $promo GROUP BY jour");

//genere des donnees compatibles avec GNUPLOT
$inscrits='';

// la première ligne contient le total des inscrits avant la date de départ (J - $JOURS)
list(,$init_nb)=mysql_fetch_row($donnees);
$total = $init_nb;

list($numjour, $nb) = mysql_fetch_row($donnees);
for ($i=-$JOURS;$i<=0;$i++) {
    if ($numjour<$i) {
        if(!list($numjour, $nb) = mysql_fetch_row($donnees)) {
            $numjour = 0;
            $nb = 0;
        }
    }
    if ($numjour==$i) $total+=$nb;
    $inscrits .= date('d/m/y',$i*DUREEJOUR+time())." ".$total."\n";
}

//Genere le graphique à la volée avec GNUPLOT
header( "Content-type: image/png");

$gnuplot="gnuplot <<EOF\n";
$param1="set term png small color\nset size 640/480\nset xdata time\nset timefmt \"%d/%m/%y\"\n";
$param2="set format x \"%m/%y\"\nset yr [".round($init_nb*0.90,0).":]\n";
$title="set title \"Nombre d'inscrits de la promotion ".$promo."\"\n";
$plot="plot \"-\" using 1:2 title 'inscrits' with lines;\n".$inscrits."e\nEOF\n";
$plot_command=$gnuplot.$param1.$param2.$title.$plot;

passthru($plot_command);
?>
