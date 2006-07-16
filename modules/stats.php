<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

function serv_to_str($params) {
    $flags = explode(',',$params);
    $trad = Array('web' => 'site web', 'mail'=> 'redirection mail',
                  'smtp' => 'serveur sécurisé d\'envoi de mails',
                  'nntp' => 'serveur des forums de discussion');
    $ret = Array();
    foreach ($flags as $flag) {
        $ret[] = $trad[$flag];
    }
    return implode(', ',$ret);
}

class StatsModule extends PLModule
{
    function handlers()
    {
        return array(
            'stats'           => $this->make_hook('stats',     AUTH_COOKIE),
            'stats/evolution' => $this->make_hook('evolution', AUTH_COOKIE),
            'stats/graph'     => $this->make_hook('graph',     AUTH_COOKIE),
            'stats/graph/evolution'
                              => $this->make_hook('graph_evo', AUTH_COOKIE),
            'stats/promos'    => $this->make_hook('promos',    AUTH_COOKIE),

            'stats/coupures'  => $this->make_hook('coupures',  AUTH_PUBLIC),
        );
    }

    function handler_stats(&$page)
    {
        $page->changeTpl('stats/index.tpl');
    }

    function handler_evolution(&$page, $jours = 365)
    {
        $page->changeTpl('stats/evolution_inscrits.tpl');
        $page->assign('jours', $jours);
    }

    function handler_graph_evo(&$page, $jours = 365)
    {
        define('DUREEJOUR',24*3600);

        //recupere le nombre d'inscriptions par jour sur la plage concernée
        $res = XDB::iterRow(
                "SELECT  IF( date_ins>DATE_SUB(NOW(),INTERVAL $jours DAY),
                             TO_DAYS(date_ins)-TO_DAYS(NOW()),
                            ".(-($jours+1)).") AS jour,
                         COUNT(user_id) AS nb
                   FROM  auth_user_md5 
                  WHERE  perms IN ('admin','user')
               GROUP BY  jour");

        //genere des donnees compatibles avec GNUPLOT
        $inscrits='';

        // la première ligne contient le total des inscrits avant la date de départ (J - $jours)
        list(,$init_nb) = $res->next();
        $total = $init_nb;

        list($numjour, $nb) = $res->next();

        for ($i = -$jours; $i<=0; $i++) {
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

        $delt = ($total - $init_nb)/10;
        $ymin = round($init_nb - $delt,0);
        $ymax = round($total   + $delt,0);

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
        exit;
    }

    function handler_graph(&$page, $promo = null)
    {
        if ($promo == 'all') {
            // date de départ
            $depart = 1920;

            //recupere le nombre d'inscriptions par jour sur la plage concernée
            $res = XDB::iterRow(
                    "SELECT  promo, SUM(perms IN ('admin', 'user')) / COUNT(*) * 100
                       FROM  auth_user_md5
                      WHERE  promo >= $depart AND deces = 0
                   GROUP BY  promo");

            //genere des donnees compatibles avec GNUPLOT
            $inscrits='';

            // la première ligne contient le total des inscrits avant la date de départ
            list($annee, $nb) = $res->next();

            for ($i = $depart; $i <= date("Y"); $i++) {
                if ($annee < $i) {
                    if(!list($annee, $nb) = $res->next()) {
                        $annee = 0;
                        $nb = 0;
                    }
                }
                if ($nb > 0 || $i < date('Y'))
                    $inscrits .= $i.' '.$nb."\n";
            }

            //Genere le graphique à la volée avec GNUPLOT
            $fin = $i+2;

            $gnuplot = <<<EOF2
gnuplot <<EOF

set term png small color
set size 640/480
set timefmt "%d/%m/%y"

set xr [$depart:$fin]
set yr [0:100]

set title "Nombre d'inscrits par promotion depuis $depart."

plot "-" using 1:2 title 'inscrits' with boxes;
{$inscrits}
EOF
EOF2;

        } else {
            //nombre de jours sur le graph
            $jours = 365;
            define('DUREEJOUR',24*3600);
            $res = XDB::query("SELECT min(TO_DAYS(date_ins)-TO_DAYS(now()))
                                           FROM auth_user_md5
                                          WHERE promo = {?}
                                                AND perms IN ('admin', 'user')",
                                        $promo);
            $jours = -$res->fetchOneCell();

            //recupere le nombre d'inscriptions par jour sur la plage concernée
            $res = XDB::iterRow(
                    "SELECT  IF( date_ins>DATE_SUB(NOW(),INTERVAL $jours DAY),
                                 TO_DAYS(date_ins)-TO_DAYS(NOW()),
                                ".(-($jours+1)).") AS jour,
                             COUNT(user_id) AS nb
                       FROM  auth_user_md5 
                      WHERE  promo = {?} AND perms IN ('admin','user')
                   GROUP BY  jour", $promo);

            //genere des donnees compatibles avec GNUPLOT
            $inscrits='';

            // la première ligne contient le total des inscrits avant la date de départ (J - $jours)
            list(,$init_nb) = $res->next();
            $total = $init_nb;

            list($numjour, $nb) = $res->next();

            for ($i = -$jours;$i<=0;$i++) {
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
            $delt = ($total - $init_nb) / 10;
            $delt += ($delt < 1);
            $ymin = round($init_nb - $delt,0);
            $ymax = round($total   + $delt,0);

            $gnuplot = <<<EOF2
gnuplot <<EOF

set term png small color
set size 640/480
set xdata time
set timefmt "%d/%m/%y"

set format x "%m/%y"
set yr [$ymin:$ymax]

set title "Nombre d'inscrits de la promotion $promo."

plot "-" using 1:2 title 'inscrits' with lines;
{$inscrits}e
EOF
EOF2;
        }

        header('Content-type: image/png');
        passthru($gnuplot);
        exit;
    }

    function handler_promos(&$page, $promo = null)
    {
        $page->changeTpl('stats/nb_by_promo.tpl');

        $res = XDB::iterRow(
                "SELECT  promo,COUNT(*)
                   FROM  auth_user_md5
                  WHERE  promo > 1900 AND perms IN ('admin','user')
               GROUP BY  promo
               ORDER BY  promo");
        $max=0; $min=3000;

        while (list($p,$nb) = $res->next()) {
            $p = intval($p);
            if(!isset($nbpromo[$p/10])) {
                $nbpromo[$p/10] = Array('','','','','','','','','',''); // tableau de 10 cases vides
            }
            $nbpromo[$p/10][$p%10]=Array('promo' => $p, 'nb' => $nb);
        }

        $page->assign_by_ref('nbs', $nbpromo);
        $page->assign('min', $min-$min % 10);
        $page->assign('max', $max+10-$max%10);
        $page->assign('promo', $promo);
    }

    function handler_coupures(&$page, $cp_id = null)
    {
        $page->changeTpl('stats/coupure.tpl');

        if (!is_null($cp_id)) {
            $res = XDB::query("SELECT  UNIX_TIMESTAMP(debut) AS debut,
                                                 TIME_FORMAT(duree,'%kh%i') AS duree,
                                                 resume, description, services
                                           FROM  coupures
                                          WHERE  id = {?}", $cp_id);
            $cp  = $res->fetchOneAssoc();
        }

        if($cp) {
            $cp['lg_services'] = serv_to_str($cp['services']);
            $page->assign_by_ref('cp',$cp);
        } else {
            $beginning_date = date("Ymd", time() - 3600*24*21) . "000000";
            $sql = "SELECT  id, UNIX_TIMESTAMP(debut) AS debut, resume, services
                      FROM  coupures where debut > '$beginning_date' order by debut desc";
            $page->assign('coupures', XDB::iterator($sql));
        }
    }
}

?>
