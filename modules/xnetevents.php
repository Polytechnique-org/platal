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

class XnetEventsModule extends PLModule
{
    function handlers()
    {
        return array(
            'grp/events'      => $this->make_hook('events',  AUTH_MDP),
            'grp/events/csv'  => $this->make_hook('csv',     AUTH_MDP),
        );
    }

    function handler_events(&$page)
    {
        global $globals;

        new_group_page('xnet/groupe/evenements.tpl');

        /**** manage inscriptions ****/
        // inscription to events
        if (Env::has('ins')) {
            for ($i=1; Env::has('evt_'.$i); $i++) {
                $eid = Env::get('evt_'.$i);
                $res = $globals->xdb->query("
                    SELECT  deadline_inscription,
                            LEFT(NOW(), 10) AS now,
                            noinvite,
                            membres_only
                    FROM    groupex.evenements
                    WHERE   eid = {?}", $eid);
                $e = $res->fetchOneAssoc();
                // impossible to change inscription: either inscription closed or members only
                if ($e['deadline_inscription'] && $e['deadline_inscription'] < $e['now'])
                {
                    $page->trig("Les inscriptions sont closes");
                    continue;
                }

                if ($e['membres_only'] && !is_member())
                {
                    $page->trig("Les inscriptions à cet événement ne sont pas publiques");
                    continue;
                }

                // impossible to unsubscribe if you already paid sthing
                $total_inscr = 0;
                $inscriptions = array();
                for ($j=1; Env::has('moment'.$eid.'_'.$j); $j++)
                {
                    $inscriptions[$j] = Env::get('moment'.$eid.'_'.$j);
                    // retreive ohter field when more than one person
                    if ($inscriptions[$j] == 2)
                        $inscriptions[$j] = 1 + Env::get('personnes'.$eid.'_'.$j,0);
                    // avoid negative count if other field incorrect
                    if ($inscriptions[$j] < 0)
                        $inscriptions[$j] = 0;
                    // avoid floating count if other field incorrect
                    $inscriptions[$j] = floor($inscriptions[$j]);
                    // avoid invite if no invite allowed
                    if ($inscriptions[$j] > 1 && $e['noinvite'])
                        $inscriptions[$j] = 1;
                    $total_inscr += $inscriptions[$j];
                }
                $unsubscribing = ($total_inscr == 0);

                // retreive the amount already paid for this event in cash
                $res  = $globals->xdb->query("
                    SELECT  paid
                    FROM    groupex.evenements_participants
                    WHERE   eid = {?} AND uid = {?}
                    LIMIT   1",
                        $eid, Session::get("uid"));
                $paid = $res->fetchOneCell();
                if (!$paid) $paid = 0;

                if ($unsubscribing && $paid != 0) {
                    $page->trig("Impossible de te désinscrire complètement ".
                                "parce que tu as fait un paiement par ".
                                "chèque ou par liquide. Contacte un ".
                                "administrateur du groupe si tu es sûr de ".
                                "ne pas venir");
                    continue;
                }

                // update actual inscriptions
                foreach ($inscriptions as $j=>$nb) {
                    if ($nb > 0) {
                        $globals->xdb->execute(
                            "REPLACE INTO  groupex.evenements_participants
                                   VALUES  ({?}, {?}, {?}, {?}, {?})",
                            $eid, Session::get("uid"), $j, $nb, $paid);
                    } else {
                        $globals->xdb->execute(
                            "DELETE FROM  groupex.evenements_participants
                                   WHERE  eid = {?} AND uid = {?} AND item_id = {?}",
                            $eid, Session::get("uid"), $j);		
                    }
                }
            }
        }

        /**** retreive all infos about all events ****/
        $page->assign('logged', logged());
        $page->assign('admin', may_update());

        $evenements = $globals->xdb->iterator(
            "SELECT  e.eid, 
                     IF(e.intitule = '', ' ', e.intitule) AS intitule,
                     IF(e.descriptif = '', ' ', e.descriptif) AS descriptif,
                     e.debut, e.fin,
                     LEFT(10,e.debut) AS debut_day,
                     LEFT(10,e.fin) AS fin_day,
                     e.paiement_id, e.membres_only, e.noinvite,
                     e.show_participants, u.nom, u.prenom, u.promo, a.alias, MAX(ep.nb) AS inscrit,
                     MAX(ep.paid) AS paid,
                     e.short_name,
                     IF(e.deadline_inscription, e.deadline_inscription >= LEFT(NOW(), 10),
                        1) AS inscr_open, e.deadline_inscription
                  FROM  groupex.evenements AS e
            INNER JOIN  x4dat.auth_user_md5 AS u ON u.user_id = e.organisateur_uid
             LEFT JOIN  x4dat.aliases AS a ON (a.type = 'a_vie' AND a.id = u.user_id)
             LEFT JOIN  groupex.evenements_participants AS ep ON (ep.eid = e.eid AND ep.uid = {?})
                 WHERE  asso_id = {?}
              GROUP BY  e.eid
              ORDER BY  debut",Session::get('uid'),$globals->asso('id'));

        $evts = array();
        while ($e = $evenements->next()) {
           $e['moments'] = $globals->xdb->iterator(
                "SELECT titre, details, montant, ei.item_id, nb
                   FROM groupex.evenements_items AS ei
              LEFT JOIN groupex.evenements_participants AS ep
                        ON (ep.eid = ei.eid AND ep.item_id = ei.item_id AND uid = {?})
                  WHERE ei.eid = {?}",
                    Session::get('uid'), $e['eid']);
            $query = $globals->xdb->query(
                "SELECT montant
                   FROM {$globals->money->mpay_tprefix}transactions AS t
                 WHERE ref = {?} AND uid = {?}", $e['paiement_id'], Session::get('uid'));
            $montants = $query->fetchColumn();
            foreach ($montants as $m) {
                $p = strtr(substr($m, 0, strpos($m, "EUR")), ",", ".");
                $e['paid'] += trim($p);
            }
            $evts[] = $e;
        }

        $page->assign('evenements', $evts);
        $page->assign('is_member', is_member());
    }

    function handler_csv(&$page, $eid = null, $item_id = null)
    {
        require_once('xnet/evenements.php');

        $evt = get_event_detail($eid, $item_id);
        if (!$evt) {
            return PL_NOT_FOUND;
        }

        header('Content-type: text/x-csv');
        header('Pragma: ');
        header('Cache-Control: ');

        new_nonhtml_page('xnet/groupe/evt-csv.tpl');

        $admin = may_update();

        $tri = (Env::get('order') == 'alpha' ? 'promo, nom, prenom' : 'nom, prenom, promo');

        if (Env::has('initiale')) {
            $ini = 'AND IF(u.nom IS NULL, m.nom,
                           IF(u.nom_usage<>"", u.nom_usage, u.nom))
                    LIKE "'.addslashes(Env::get('initiale')).'%"';
        } else {
            $ini = '';
        }

        $participants = get_event_participants($eid, $item_id, $ini, $tri, "",
                                               $evt['money'] && $admin,
                                               $evt['paiement_id']);

        $page->assign('participants', $participants);
        $page->assign('admin', $admin);
        $page->assign('moments', $evt['moments']);
        $page->assign('money', $evt['money']);
        $page->assign('tout', !Env::get('item_id', false));
    }
}

?>
