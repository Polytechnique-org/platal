<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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
            'stats'                 => $this->make_hook('stats',     AUTH_COOKIE, 'user'),
            'stats/evolution'       => $this->make_hook('evolution', AUTH_COOKIE, 'user'),
            'stats/graph'           => $this->make_hook('graph',     AUTH_COOKIE, 'user'),
            'stats/graph/evolution' => $this->make_hook('graph_evo', AUTH_COOKIE, 'user'),
            'stats/promos'          => $this->make_hook('promos',    AUTH_COOKIE, 'user'),

            'stats/coupures'        => $this->make_hook('coupures',  AUTH_PUBLIC),
        );
    }

    function handler_stats($page)
    {
        $page->changeTpl('stats/index.tpl');
    }

    function handler_evolution($page, $days = 365)
    {
        $page->changeTpl('stats/evolution_inscrits.tpl');
        $page->assign('days', $days);
    }

    function handler_graph_evo($page, $days = 365)
    {
        $day_length = 24 * 3600;

        // Retrieve the registration count per days during the given date range.
        $res = XDB::iterRow('SELECT  IF(registration_date > DATE_SUB(NOW(), INTERVAL {?} DAY),
                                        TO_DAYS(registration_date) - TO_DAYS(NOW()),
                                        - {?}) AS day,
                                     COUNT(a.uid) AS nb
                               FROM  accounts         AS a
                         INNER JOIN  account_profiles AS ap ON (ap.uid = a.uid AND FIND_IN_SET(\'owner\', ap.perms))
                         INNER JOIN  profiles         AS p  ON (ap.pid = p.pid)
                              WHERE  a.state = \'active\' AND p.deathdate IS NULL
                           GROUP BY  day',
                            (int)$days, 1 + (int)$days);

        // The first contains the registration count before the starting date (J - $days)
        list(, $init_nb) = $res->next();
        $total   = $init_nb;
        $num_day = - $days - 1;

        $registered = '';
        for ($i = -$days; $i <= 0; ++$i) {
            if ($num_day < $i) {
                if(!list($num_day, $nb) = $res->next()) {
                    $num_day = 0;
                    $nb = 0;
                }
            }
            if ($num_day == $i) {
                $total += $nb;
            }
            $registered .= date('d/m/y', $i * $day_length + time()) . ' ' . $total . "\n";
        }

        //Genere le graphique à la volée avec GNUPLOT
        pl_cached_dynamic_content_headers("image/png");

        $delt = ($total - $init_nb) / 10;
        $delt = $delt ? $delt : 5;
        $ymin = round($init_nb - $delt, 0);
        $ymax = round($total   + $delt, 0);

        $gnuplot = <<<EOF2
gnuplot <<EOF

set term png small color
set size 640/480
set xdata time
set timefmt "%d/%m/%y"

set format x "%d/%m\\n%Y"
set yr [$ymin:$ymax]

set title "Nombre d'inscrits"

plot "-" using 1:2 title 'inscrits' with lines;
{$registered}
EOF
EOF2;

        passthru($gnuplot);
        exit;
    }

    function handler_graph($page, $promo = null)
    {
        if (in_array($promo, array(Profile::DEGREE_X, Profile::DEGREE_M, Profile::DEGREE_D))) {
            $cycle = Profile::$cycles[$promo] . 's';
            $res = XDB::iterRow("SELECT  pe.promo_year, SUM(a.state = 'active') / COUNT(*) * 100
                                   FROM  accounts                      AS a
                             INNER JOIN  account_profiles              AS ap  ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
                             INNER JOIN  profiles                      AS p   ON (p.pid = ap.pid)
                             INNER JOIN  profile_education             AS pe  ON (pe.pid = ap.pid AND FIND_IN_SET('primary', pe.flags))
                             INNER JOIN  profile_education_degree_enum AS ped ON (pe.degreeid = ped.id)
                                  WHERE  p.deathdate IS NULL AND ped.degree = {?}
                               GROUP BY  pe.promo_year",
                                $promo);

            list($promo, $count) = $res->next();
            $first = $promo;
            $registered = $promo . ' ' . $count . "\n";
            while ($next = $res->next()) {
                list($promo, $count) = $next;
                $registered .= $promo . ' ' . $count . "\n";
            }
            $last = $promo + 2;

            // Generate drawing thanks to Gnuplot.
            $gnuplot = <<<EOF2
gnuplot <<EOF

set term png small color
set size 640/480
set timefmt "%d/%m/%y"

set xr [$first:$last]
set yr [0:100]

set title "Proportion de $cycle inscrits par promotion, en %."
set key left top

plot "-" using 1:2 title 'inscrits' with boxes;
{$registered}
EOF
EOF2;

        } else {
            $day_length = 24 * 3600;
            $days = 365;

            $res = XDB::query("SELECT  MIN(TO_DAYS(a.registration_date) - TO_DAYS(NOW()))
                                 FROM  accounts         AS a
                           INNER JOIN  account_profiles AS ap ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
                           INNER JOIN  profile_display  AS pd ON (ap.pid = pd.pid)
                                WHERE  pd.promo = {?} AND a.state = 'active'",
                              $promo);
            $days = -$res->fetchOneCell();

            // Retrieve the registration count per days during the given date range.
            $res = XDB::iterRow("SELECT  IF(a.registration_date > DATE_SUB(NOW(), INTERVAL {?} DAY),
                                            TO_DAYS(a.registration_date) - TO_DAYS(NOW()),
                                            - {?}) AS day,
                                         COUNT(a.uid) AS nb
                                   FROM  accounts         AS a
                             INNER JOIN  account_profiles AS ap ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
                             INNER JOIN  profile_display  AS pd ON (ap.pid = pd.pid)
                                  WHERE  pd.promo = {?} AND a.state = 'active'
                               GROUP BY  day",
                                (int)$days, 1 + (int)$days, $promo);

            // The first line contains the registration count before starting date (D - $days).
            list(, $init_nb) = $res->next();
            $total = $init_nb;
            $registered = '';

            list($num_day, $nb) = $res->next();

            for ($i = -$days; $i <= 0; ++$i) {
                if ($num_day < $i) {
                    if(!list($num_day, $nb) = $res->next()) {
                        $num_day = 0;
                        $nb = 0;
                    }
                }
                if ($num_day == $i) {
                    $total += $nb;
                }
                $registered .= date('d/m/y', $i * $day_length + time()) . ' ' . $total . "\n";
            }

            // Generate drawing thanks to Gnuplot.
            $delt = ($total - $init_nb) / 10;
            $delt += ($delt < 1);
            $ymin = round($init_nb - $delt, 0);
            $ymax = round($total   + $delt, 0);

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
{$registered}e
EOF
EOF2;
        }

        pl_cached_dynamic_content_headers("image/png");
        passthru($gnuplot);
        exit;
    }

    function handler_promos($page, $required_promo = null)
    {
        $page->changeTpl('stats/nb_by_promo.tpl');
        $cycles = array('X' => 'Polytechniciens', 'M' => 'Masters', 'D' => 'Docteurs');

        $res = XDB::iterRow('SELECT  pd.promo, COUNT(*)
                               FROM  accounts         AS a
                         INNER JOIN  account_profiles AS ap ON (ap.uid = a.uid AND FIND_IN_SET(\'owner\', ap.perms))
                         INNER JOIN  profiles         AS p  ON (p.pid = ap.pid)
                         INNER JOIN  profile_display  AS pd ON (pd.pid = ap.pid)
                              WHERE  a.state = \'active\' AND p.deathdate IS NULL AND pd.promo != \'D (en cours)\'
                           GROUP BY  pd.promo
                           ORDER BY  pd.promo LIKE \'D%\', pd.promo LIKE \'M%\', pd.promo LIKE \'X%\', pd.promo');

        $nbpromo = array();
        while (list($promo, $count) = $res->next()) {
            $prefix = substr($promo, 0, 4) . '-';
            $unit = substr($promo, -1);
            if(!isset($nbpromo[$cycles[$promo{0}]][$prefix])) {
                $nbpromo[$cycles[$promo{0}]][$prefix] = array('', '', '', '', '', '', '', '', '', ''); // Empty array containing 10 cells.
            }
            $nbpromo[$cycles[$promo{0}]][$prefix][$unit] = array('promo' => $promo, 'nb' => $count);
        }

        $count = XDB::fetchOneCell('SELECT  COUNT(*)
                                      FROM  accounts         AS a
                                INNER JOIN  account_profiles AS ap ON (ap.uid = a.uid AND FIND_IN_SET(\'owner\', ap.perms))
                                INNER JOIN  profiles         AS p  ON (p.pid = ap.pid)
                                INNER JOIN  profile_display  AS pd ON (pd.pid = ap.pid)
                                     WHERE  a.state = \'active\' AND p.deathdate IS NULL AND pd.promo = \'D (en cours)\'');
        $nbpromo[$cycles['D']]['D (en cours)'][0] = array('promo' => 'D (en cours)', 'nb' => $count);

        $page->assign_by_ref('nbs', $nbpromo);
        $page->assign('promo', $required_promo);
    }

    function handler_coupures($page, $cp_id = null)
    {
        $page->changeTpl('stats/coupure.tpl');

        if (!is_null($cp_id)) {
            $res = XDB::query("SELECT  debut,
                                       TIME_FORMAT(duree,'%kh%i') AS duree,
                                       resume, description, services
                                 FROM  downtimes
                                WHERE  id = {?}", $cp_id);
            $cp  = $res->fetchOneAssoc();
        }

        if(@$cp) {
            $cp['lg_services'] = serv_to_str($cp['services']);
            $page->assign_by_ref('cp',$cp);
        } else {
            $beginning_date = date("Ymd", time() - 3600*24*21) . "000000";
            $sql = "SELECT  id, debut, resume, services
                      FROM  downtimes where debut > '$beginning_date' order by debut desc";
            $page->assign('coupures', XDB::iterator($sql));
            $res = XDB::iterator("SELECT  host, text
                                    FROM  mx_watch
                                   WHERE  state != 'ok'");
            $page->assign('mxs', $res);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
