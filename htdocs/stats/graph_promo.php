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
        $Id: graph_promo.php,v 1.5 2004-11-14 16:29:53 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('index.tpl',AUTH_COOKIE);


// genere le graph de l'evolution du nombre d'inscrits dans une promotion 

$promo = (isset($_REQUEST['promo']) ? intval($_REQUEST["promo"]) : $_SESSION["promo"]);

//nombre de jours sur le graph
$JOURS=364;
define('DUREEJOUR',24*3600);

//recupere le nombre d'inscriptions par jour sur la plage concernée
$donnees=$globals->db->query("SELECT  IF( date_ins>DATE_SUB(NOW(),INTERVAL $JOURS DAY),
				          TO_DAYS(date_ins)-TO_DAYS(NOW()),
					  ".(-($JOURS+1)).") AS jour,
                                      count(user_id) AS nb
				FROM  auth_user_md5 
			       WHERE  promo = $promo AND perms IN ('admin','user')
			    GROUP BY  jour");

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
