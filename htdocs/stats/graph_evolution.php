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

new_skinned_page('index.tpl', AUTH_COOKIE);

//nombre de jours sur le graph
$JOURS=Env::getInt('jours', 364);
define('DUREEJOUR',24*3600);

//recupere le nombre d'inscriptions par jour sur la plage concernée
$res = $globals->xdb->iterRow(
        "SELECT  IF( date_ins>DATE_SUB(NOW(),INTERVAL $JOURS DAY),
                     TO_DAYS(date_ins)-TO_DAYS(NOW()),
                    ".(-($JOURS+1)).") AS jour,
                 COUNT(user_id) AS nb
           FROM  auth_user_md5 
          WHERE  perms IN ('admin','user')
       GROUP BY  jour");

//genere des donnees compatibles avec GNUPLOT
$inscrits='';

// la première ligne contient le total des inscrits avant la date de départ (J - $JOURS)
list(,$init_nb) = $res->next();
$total = $init_nb;

list($numjour, $nb) = $res->next();

for ($i=-$JOURS;$i<=0;$i++) {
    if ($numjour<$i) {
        if(!list($numjour, $nb) = $res->next()) {
            $numjour = 0;
            $nb = 0;
        }
    }
    if ($numjour==$i) $total+=$nb;
    $inscrits .= date('d/m/y',$i*DUREEJOUR+time())." ".$total."\n";
}

//Genere le graphique à la volée avec GNUPLOT
header( "Content-type: image/png");

$ymin = round($init_nb*0.95,0);
$ymax = round($total  *1.05,0);

$gnuplot = <<<EOF2
gnuplot <<EOF

set term png small color
set size 640/480
set xdata time
set timefmt "%d/%m/%y"

set format x "%m/%y"
set yr [$ymin:$ymax]

set title "Nombre d'inscrits"

plot "-" using 1:2 title 'inscrits' with lines;
{$inscrits}
EOF
EOF2;

passthru($gnuplot);
?>
