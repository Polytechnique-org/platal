<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

define('NB_PER_PAGE', 25);

class XnetEventsModule extends PLModule
{
    function handlers()
    {
        return array(
            '%grp/events'       => $this->make_hook('events',  AUTH_MDP),
            '%grp/events/sub'   => $this->make_hook('sub',     AUTH_MDP),
            '%grp/events/csv'   => $this->make_hook('csv',     AUTH_MDP, 'user', NO_HTTPS),
            '%grp/events/ical'  => $this->make_hook('ical',    AUTH_MDP, 'user', NO_HTTPS),
            '%grp/events/edit'  => $this->make_hook('edit',    AUTH_MDP, 'groupadmin'),
            '%grp/events/admin' => $this->make_hook('admin',   AUTH_MDP, 'groupmember'),
        );
    }

    function handler_events(&$page, $archive = null)
    {
        global $globals;

        $page->changeTpl('xnetevents/index.tpl');
        $action = null;
        $archive = ($archive == 'archive' && may_update());

        if (Post::has('del')) {
            $action = 'del';
            $eid = Post::v('del');
        } elseif (Post::has('archive')) {
            $action = 'archive';
            $eid = Post::v('archive');
        } elseif (Post::has('unarchive')) {
            $action = 'unarchive';
            $eid = Post::v('unarchive');
        }

        if (!is_null($action)) {
            if (!may_update()) {
                return PL_FORBIDDEN;
            }

            $res = XDB::query("SELECT asso_id, short_name FROM groupex.evenements
                                WHERE eid = {?} AND asso_id = {?}",
                              $eid, $globals->asso('id'));

            $tmp = $res->fetchOneRow();
            if (!$tmp) {
                return PL_FORBIDDEN;
            }
        }

        if ($action == 'del') {
            // deletes the event mailing aliases
            if ($tmp[1]) {
                XDB::execute(
                    "DELETE FROM virtual WHERE type = 'evt' AND alias LIKE {?}",
                    $tmp[1].'-absents@%');
                XDB::execute(
                    "DELETE FROM virtual WHERE type = 'evt' AND alias LIKE {?}",
                    $tmp[1].'-participants@%');
            }

            // deletes the event items
            XDB::execute("DELETE FROM groupex.evenements_items WHERE eid = {?}", $eid);

            // deletes the event participants
            XDB::execute("DELETE FROM groupex.evenements_participants
                                    WHERE eid = {?}", $eid);

            // deletes the event
            XDB::execute("DELETE FROM groupex.evenements
                                    WHERE eid = {?} AND asso_id = {?}",
                                   $eid, $globals->asso('id'));

            // delete the requests for payments
            require_once 'validations.inc.php';
            XDB::execute("DELETE FROM requests
                                    WHERE type = 'paiements' AND data LIKE {?}",
                                   PayReq::same_event($eid, $globals->asso('id')));
        }

        if ($action == 'archive') {
            XDB::execute("UPDATE groupex.evenements
                             SET archive = 1
                           WHERE eid = {?} AND asso_id = {?}",
                         $eid, $globals->asso('id'));
        }

        if ($action == 'unarchive') {
            XDB::execute("UPDATE groupex.evenements
                             SET archive = 0
                           WHERE eid = {?} AND asso_id = {?}",
                         $eid, $globals->asso('id'));
        }

        $page->assign('archive', $archive);
        $evenements = XDB::iterator(
                "SELECT  e.*, LEFT(10, e.debut) AS debut_day, LEFT(10, e.fin) AS fin_day,
                         IF(e.deadline_inscription, e.deadline_inscription >= LEFT(NOW(), 10),
                            1) AS inscr_open, e.deadline_inscription,
                         u.nom, u.prenom, u.promo, a.alias,
                         MAX(ep.nb) IS NOT NULL AS inscrit, MAX(ep.paid) AS paid
                  FROM  groupex.evenements  AS e
            INNER JOIN  x4dat.auth_user_md5 AS u ON u.user_id = e.organisateur_uid
            INNER JOIN  x4dat.aliases       AS a ON (a.type = 'a_vie' AND a.id = u.user_id)
             LEFT JOIN  groupex.evenements_participants AS ep ON (ep.eid = e.eid AND ep.uid = {?})
                 WHERE  asso_id = {?}
                   AND  archive = " . ($archive ? "1 " : "0 ")
            . (is_member() || may_update() ? "" : " AND accept_nonmembre != 0 ")
              . "GROUP BY  e.eid
                 ORDER BY  inscr_open DESC, debut DESC", S::v('uid'), $globals->asso('id'));

        $evts = array();

        while ($e = $evenements->next()) {
            $e['show_participants'] = ($e['show_participants'] && (is_member() || may_update()));
            $res = XDB::query(
                "SELECT titre, details, montant, ei.item_id, nb, ep.paid
                   FROM groupex.evenements_items AS ei
              LEFT JOIN groupex.evenements_participants AS ep
                        ON (ep.eid = ei.eid AND ep.item_id = ei.item_id AND uid = {?})
                  WHERE ei.eid = {?}",
                    S::v('uid'), $e['eid']);
            $e['moments'] = $res->fetchAllAssoc();

            $e['topay'] = 0;
            $e['paid']  = $e['moments'][0]['paid'];
            foreach ($e['moments'] as $m) {
                $e['topay'] += $m['nb'] * $m['montant'];
            }

            $query = XDB::query(
                "SELECT montant
                   FROM {$globals->money->mpay_tprefix}transactions AS t
                 WHERE ref = {?} AND uid = {?}", $e['paiement_id'], S::v('uid'));
            $montants = $query->fetchColumn();

            foreach ($montants as $m) {
                $p = strtr(substr($m, 0, strpos($m, 'EUR')), ',', '.');
                $e['paid'] += trim($p);
            }

            if (Env::has('updated') && $e['eid'] == Env::i('updated')) {
                $page->assign('updated', $e);
            }
            $evts[] = $e;
        }

        $page->assign('evenements', $evts);
    }

    function handler_sub(&$page, $eid = null)
    {
        require_once dirname(__FILE__).'/xnetevents/xnetevents.inc.php';
        $page->changeTpl('xnetevents/subscribe.tpl');

        $evt = get_event_detail($eid);
        if (!$evt) {
            return PL_NOT_FOUND;
        }

        if (!$evt['inscr_open']) {
            $page->kill('Les inscriptions pour cet événement sont closes');
        }
        if (!$evt['accept_nonmembre'] && !is_member() && !may_update()) {
            $page->kill('Cet événement est fermé aux non-membres du groupe');
        }

        global $globals;
        $res = XDB::query("SELECT  stamp FROM requests
                            WHERE  type = 'paiements' AND data LIKE {?}",
                           PayReq::same_event($evt['eid'], $globals->asso('id')));
        $page->assign('validation', $res->numRows());
        $page->assign('event', $evt);

        if (!Post::has('submit')) {
            return;
        }

        $moments = Post::v('moment',    array());
        $pers    = Post::v('personnes', array());
        $subs    = array();

        foreach ($moments as $j => $v) {
            $subs[$j] = intval($v);

            // retreive ohter field when more than one person
            if ($subs[$j] == 2) {
                if (!isset($pers[$j]) || !is_numeric($pers[$j])
                ||  $pers[$j] < 0)
                {
                    $page->trig('Tu dois choisir un nombre d\'invités correct !');
                    return;
                }
                $subs[$j] = 1 + $pers[$j];
            }
        }

        // impossible to unsubscribe if you already paid sthing
        if (!array_sum($subs) && $evt['paid'] != 0) {
            $page->trig("Impossible de te désinscrire complètement ".
                        "parce que tu as fait un paiement par ".
                        "chèque ou par liquide. Contacte un ".
                        "administrateur du groupe si tu es sûr de ".
                        "ne pas venir");
            return;
        }

        // update actual inscriptions
        $updated = false;
        $total   = 0;
        $paid    = $evt['paid'] ? $evt['paid'] : 0;
        foreach ($subs as $j => $nb) {
            if ($nb >= 0) {
                XDB::execute(
                    "REPLACE INTO  groupex.evenements_participants
                           VALUES  ({?}, {?}, {?}, {?}, {?}, {?})",
                    $eid, S::v('uid'), $j, $nb, Env::has('notify_payment') ? 'notify_payment' : '', $paid);
                $updated = $eid;
            } else {
                XDB::execute(
                    "DELETE FROM  groupex.evenements_participants
                           WHERE  eid = {?} AND uid = {?} AND item_id = {?}",
                    $eid, S::v("uid"), $j);		
                $updated = $eid;
            }
            $total += $nb;
        }
        if ($updated !== false) {
            subscribe_lists_event($total, S::i('uid'), $evt);
        }
        $page->assign('event', get_event_detail($eid));
    }

    function handler_csv(&$page, $eid = null, $item_id = null)
    {
        require_once dirname(__FILE__).'/xnetevents/xnetevents.inc.php';

        if (!is_numeric($item_id)) {
            $item_id = null;
        }

        $evt = get_event_detail($eid, $item_id);
        if (!$evt) {
            return PL_NOT_FOUND;
        }

        header('Content-type: text/x-csv; encoding=UTF-8');
        header('Pragma: ');
        header('Cache-Control: ');

        $page->changeTpl('xnetevents/csv.tpl', NO_SKIN);

        $admin = may_update();

        $tri = (Env::v('order') == 'alpha' ? 'promo, nom, prenom' : 'nom, prenom, promo');

        $page->assign('participants',
                      get_event_participants($evt, $item_id, $tri));

        $page->assign('admin', $admin);
        $page->assign('moments', $evt['moments']);
        $page->assign('money', $evt['money']);
        $page->assign('tout', !Env::v('item_id', false));
    }

    function handler_ical(&$page, $eid = null)
    {
        global $globals;

        require_once dirname(__FILE__).'/xnetevents/xnetevents.inc.php';
        $evt = get_event_detail($eid);
        if (!$evt) {
            return PL_FORBIDDEN;
        }
        $evt['debut'] = preg_replace('/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/', "\\1\\2\\3T\\4\\5\\6", $evt['debut']);
        $evt['fin'] = preg_replace('/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/', "\\1\\2\\3T\\4\\5\\6", $evt['fin']);

        foreach ($evt['moments'] as $m) {
            $evt['descriptif'] .= "\n\n** " . $m['titre'] . " **\n" . $m['details'];
        }

        $page->changeTpl('xnetevents/calendar.tpl', NO_SKIN);

        require_once('ical.inc.php');
        $page->assign('asso', $globals->asso());
        $page->assign('timestamp', time());
        $page->assign('admin', may_update());

        if (may_update()) {
            $page->assign('participants', get_event_participants($evt, null, 'promo, nom, prenom'));
        }
        $page->register_function('display_ical', 'display_ical');
        $page->assign_by_ref('e', $evt);

        header('Content-Type: text/calendar; charset=utf-8');
    }

    function handler_edit(&$page, $eid = null)
    {
        global $globals;

        // get eid if the the given one is a short name
        if (!is_null($eid) && !is_numeric($eid)) {
            $res = XDB::query("SELECT eid
                                 FROM groupex.evenements
                                WHERE asso_id = {?} AND short_name = {?}",
                              $globals->asso('id'), $eid);
            if ($res->numRows()) {
                $eid = (int)$res->fetchOneCell();
            }
        }

        // check the event is in our group
        if (!is_null($eid)) {
            $res = XDB::query("SELECT short_name
                                 FROM groupex.evenements
                                WHERE eid = {?} AND asso_id = {?}",
                              $eid, $globals->asso('id'));
            if ($res->numRows()) {
                $infos = $res->fetchOneAssoc();
            } else {
                return PL_FORBIDDEN;
            }
        }

        $page->changeTpl('xnetevents/edit.tpl');

        $moments    = range(1, 4);
        $error      = false;
        $page->assign('moments', $moments);

        if (Post::v('intitule')) {
            require_once dirname(__FILE__).'/xnetevents/xnetevents.inc.php';
            $short_name = event_change_shortname($page, $infos['short_name'],
                                                 Env::v('short_name', ''));
            if ($short_name != Env::v('short_name')) {
                $error = true;
            }
            $evt = array(
                'eid'              => $eid,
                'asso_id'          => $globals->asso('id'),
                'paiement_id'      => Post::v('paiement_id') > 0 ? Post::v('paiement_id') : null,
                'debut'            => Post::v('deb_Year').'-'.Post::v('deb_Month')
                                      .'-'.Post::v('deb_Day').' '.Post::v('deb_Hour')
                                      .':'.Post::v('deb_Minute').':00',
                'fin'              => Post::v('fin_Year').'-'.Post::v('fin_Month')
                                      .'-'.Post::v('fin_Day').' '.Post::v('fin_Hour')
                                      .':'.Post::v('fin_Minute').':00',
                'short_name'       => $short_name,
            );

            $trivial = array('intitule', 'descriptif', 'noinvite',
                             'show_participants', 'accept_nonmembre', 'organisateur_uid');
            foreach ($trivial as $k) {
                $evt[$k] = Post::v($k);
            }
            if (!$eid) {
                $evt['organisateur_uid'] = S::v('uid');
            }

            if (Post::v('deadline')) {
                $evt['deadline_inscription'] = Post::v('inscr_Year').'-'
                                             . Post::v('inscr_Month').'-'
                                             . Post::v('inscr_Day');
            } else {
                $evt['deadline_inscription'] = null;
            }

            // Store the modifications in the database
            XDB::execute('REPLACE INTO groupex.evenements
                SET eid={?}, asso_id={?}, organisateur_uid={?}, intitule={?},
                    paiement_id = {?}, descriptif = {?}, debut = {?},
                    fin = {?}, show_participants = {?}, short_name = {?},
                    deadline_inscription = {?}, noinvite = {?},
                    accept_nonmembre = {?}',
                    $evt['eid'], $evt['asso_id'], $evt['organisateur_uid'],
                    $evt['intitule'], $evt['paiement_id'], $evt['descriptif'],
                    $evt['debut'], $evt['fin'], $evt['show_participants'],
                    $evt['short_name'], $evt['deadline_inscription'],
                    $evt['noinvite'], $evt['accept_nonmembre']);

            // if new event, get its id
            if (!$eid) {
                $eid = XDB::insertId();
            }

            $nb_moments   = 0;
            $money_defaut = 0;

            foreach ($moments as $i) {
                if (Post::v('titre'.$i)) {
                    $nb_moments++;

                    $montant = strtr(Post::v('montant'.$i), ',', '.');
                    $money_defaut += (float)$montant;
                    XDB::execute("
                        REPLACE INTO groupex.evenements_items
                        VALUES ({?}, {?}, {?}, {?}, {?})",
                        $eid, $i, Post::v('titre'.$i),
                        Post::v('details'.$i), $montant);
                } else {
                    XDB::execute("DELETE FROM groupex.evenements_items
                                            WHERE eid = {?} AND item_id = {?}", $eid, $i);
                }
            }
            // request for a new payment
            if (Post::v('paiement_id') == -1 && $money_defaut >= 0) {
                require_once 'validations.inc.php';
                $p = new PayReq(S::v('uid'),
                                Post::v('intitule')." - ".$globals->asso('nom'),
                                Post::v('site'), $money_defaut,
                                Post::v('confirmation'), 0, 999,
                                $globals->asso('id'), $eid);
                if ($p->accept()) {
                    $p->submit();
                } else {
                    $page->assign('paiement_message', Post::v('confirmation'));
                    $page->assign('paiement_site', Post::v('site'));
                    $error = true;
                }
            }

            // events with no sub-event: add a sub-event with no name
            if ($nb_moments == 0) {
                XDB::execute("INSERT INTO groupex.evenements_items
                                   VALUES ({?}, {?}, '', '', 0)", $eid, 1);
            }

            if (!$error) {
                pl_redirect('events');
            }
        }

        // get a list of all the payment for this asso
        $res = XDB::iterator("SELECT id, text
                                FROM {$globals->money->mpay_tprefix}paiements
                               WHERE asso_id = {?}", $globals->asso('id'));
        $paiements = array();
        while ($a = $res->next()) $paiements[$a['id']] = $a['text']; {
            $page->assign('paiements', $paiements);
        }

        // when modifying an old event retreive the old datas
        if ($eid) {
            $res = XDB::query(
                    "SELECT	eid, intitule, descriptif, debut, fin, organisateur_uid,
                            show_participants, paiement_id, short_name,
                            deadline_inscription, noinvite, accept_nonmembre
                       FROM	groupex.evenements
                      WHERE eid = {?}", $eid);
            $evt = $res->fetchOneAssoc();
            // find out if there is already a request for a payment for this event
            require_once 'validations.inc.php';
            $res = XDB::query("SELECT stamp FROM requests
                                WHERE type = 'paiements' AND data LIKE {?}",
                               PayReq::same_event($eid, $globals->asso('id')));
            $stamp = $res->fetchOneCell();
            if ($stamp) {
                $evt['paiement_id']  = -2;
                $evt['paiement_req'] = $stamp;
            }
            $page->assign('evt', $evt);
            // get all the different moments infos
            $res = XDB::iterator(
                    "SELECT item_id, titre, details, montant
                       FROM groupex.evenements_items AS ei
                 INNER JOIN groupex.evenements AS e ON(e.eid = ei.eid)
                      WHERE e.eid = {?}
                   ORDER BY item_id", $eid);
            $items = array();
            while ($item = $res->next()) {
                $items[$item['item_id']] = $item;
            }
            $page->assign('items', $items);
        }
        $page->assign('url_ref', $eid);
    }

    function handler_admin(&$page, $eid = null, $item_id = null)
    {
        global $globals;

        require_once dirname(__FILE__).'/xnetevents/xnetevents.inc.php';

        $evt = get_event_detail($eid, $item_id);
        if (!$evt) {
            return PL_NOT_FOUND;
        }

        $page->changeTpl('xnetevents/admin.tpl');
        if (!$evt['show_participants'] && !may_update()) {
            return PL_FORBIDDEN;
        }

        if (may_update() && Post::v('adm')) {
            $member = get_infos(Post::v('mail'));
            if (!$member) {
                $page->trig("Membre introuvable");
            }

            // change the price paid by a participant
            if (Env::v('adm') == 'prix' && $member) {
                XDB::execute("UPDATE groupex.evenements_participants
                                 SET paid = IF(paid + {?} > 0, paid + {?}, 0)
                               WHERE uid = {?} AND eid = {?} AND item_id = 1",
                        strtr(Env::v('montant'), ',', '.'),
                        strtr(Env::v('montant'), ',', '.'),
                        $member['uid'], $evt['eid']);
            }

            // change the number of personns coming with a participant
            if (Env::v('adm') == 'nbs' && $member) {
                $res = XDB::query("SELECT paid
                                     FROM groupex.evenements_participants
                                    WHERE uid = {?} AND eid = {?}",
                                  $member['uid'], $evt['eid']);

                $paid = intval($res->fetchOneCell());
                $nbs  = Post::v('nb', array());

                foreach ($nbs as $id => $nb) {
                    $nb = max(intval($nb), 0);
                    XDB::execute("REPLACE INTO groupex.evenements_participants
                                        VALUES ({?}, {?}, {?}, {?}, {?}, {?})",
                                  $evt['eid'], $member['uid'], $id, $nb, '', $paid);
                }

                $res = XDB::query("SELECT COUNT(uid) AS cnt, SUM(nb) AS nb
                                     FROM groupex.evenements_participants
                                    WHERE uid = {?} AND eid = {?}
                                 GROUP BY uid",
                                            $member['uid'], $evt['eid']);
                $u = $res->fetchOneAssoc();
                $u = $u['cnt'] ? null : $u['nb'];
                subscribe_lists_event($u, $member['uid'], $evt);
            }

            $evt = get_event_detail($eid, $item_id);
        }

        $page->assign('evt', $evt);
        $page->assign('tout', is_null($item_id));

        if (count($evt['moments'])) {
            $page->assign('moments', $evt['moments']);
        }

        $tri = (Env::v('order') == 'alpha' ? 'promo, nom, prenom' : 'nom, prenom, promo');
        $whereitemid = is_null($item_id) ? '' : "AND ep.item_id = $item_id";
        $res = XDB::iterRow(
                    'SELECT  UPPER(SUBSTRING(IF(u.nom IS NULL, m.nom,
                                                IF(u.nom_usage<>"", u.nom_usage, u.nom)), 1, 1)),
                             COUNT(DISTINCT ep.uid)
                       FROM  groupex.evenements_participants AS ep
                 INNER JOIN  groupex.evenements AS e ON (ep.eid = e.eid)
                  LEFT JOIN  groupex.membres AS m ON ( ep.uid = m.uid AND e.asso_id = m.asso_id)
                  LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = ep.uid )
                      WHERE  ep.eid = {?} '.$whereitemid . '
                   GROUP BY  UPPER(SUBSTRING(IF(u.nom IS NULL,m.nom,u.nom), 1, 1))', $evt['eid']);

        $alphabet = array();
        $nb_tot = 0;
        while (list($char, $nb) = $res->next()) {
            $alphabet[ord($char)] = $char;
            $nb_tot += $nb;
            if (Env::has('initiale') && $char == strtoupper(Env::v('initiale'))) {
                $tot = $nb;
            }
        }
        ksort($alphabet);
        $page->assign('alphabet', $alphabet);

        if ($evt['paiement_id']) {
            $res = XDB::iterator(
                "SELECT IF(u.nom_usage<>'', u.nom_usage, u.nom) AS nom, u.prenom,
                        u.promo, a.alias AS email, t.montant
                   FROM {$globals->money->mpay_tprefix}transactions AS t
             INNER JOIN auth_user_md5 AS u ON(t.uid = u.user_id)
             INNER JOIN aliases AS a ON (a.id = t.uid AND a.type='a_vie' )
              LEFT JOIN groupex.evenements_participants AS ep ON(ep.uid = t.uid AND ep.eid = {?})
                  WHERE t.ref = {?} AND ep.uid IS NULL",
                  $evt['eid'], $evt['paiement_id']);
            $page->assign('oublis', $res->total());
            $page->assign('oubliinscription', $res);
        }

        $absents = XDB::iterator("SELECT  p.uid,
                                          IF(m.origine = 'X', IF(u.nom_usage != '', u.nom_usage, u.nom), m.nom) AS nom,
                                          IF(m.origine = 'X', u.prenom, u.prenom) AS prenom,
                                          IF(m.origine = 'X', u.promo, m.origine) AS promo,
                                          IF(m.origine = 'X', FIND_IN_SET('femme', u.flags), m.sexe) AS sexe,
                                          IF(m.origine = 'X', a.alias, m.email) AS email
                                    FROM  groupex.evenements_participants AS p
                              INNER JOIN  groupex.membres                 AS m USING(uid)
                               LEFT JOIN  groupex.evenements_participants AS p2 ON (p2.uid = m.uid AND p2.eid = p.eid
                                                                                    AND p2.nb != 0)
                               LEFT JOIN  auth_user_md5                   AS u ON (u.user_id = m.uid)
                               LEFT JOIN  aliases                         AS a ON (a.id = u.user_id AND a.type = 'a_vie')
                                   WHERE  p.eid = {?} AND p2.eid IS NULL
                                       " . (Env::v('initiale') ? " AND IF(u.nom IS NULL, m.nom,
                                          IF(u.nom_usage<>'', u.nom_usage, u.nom)) LIKE '" . Env::v('initiale') . "%'"
                                         : "") . "
                                GROUP BY  m.uid
                                ORDER BY  nom, prenom, promo", $evt['eid']);

        $ofs   = Env::i('offset');
        $tot   = (Env::v('initiale') ? $tot : $nb_tot) - $absents->total();
        $nbp   = intval(($tot-1)/NB_PER_PAGE);
        $links = array();
        if ($ofs) {
            $links['précédent'] = $ofs-1;
        }
        for ($i = 0; $i <= $nbp; $i++) {
            $links[(string)($i+1)] = $i;
        }
        if ($ofs < $nbp) {
            $links['suivant'] = $ofs+1;
        }
        if (count($links)>1) {
            $page->assign('links', $links);
        }


        $page->assign('absents', $absents);
        $page->assign('participants',
                      get_event_participants($evt, $item_id, $tri,
                                             "LIMIT ".($ofs*NB_PER_PAGE).", ".NB_PER_PAGE));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
