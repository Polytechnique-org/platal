<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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
            S::assert_xsrf_token();

            $res = XDB::query("SELECT asso_id, short_name FROM group_events
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
                foreach (array('-absents@', '-participants@', '-paye@', '-participants-non-paye@') as $v) {
                    XDB::execute("DELETE FROM  virtual
                                        WHERE  type = 'evt' AND alias LIKE {?}",
                                 $tmp[1] . $v . '%');
                }
            }

            // deletes the event items
            XDB::execute('DELETE FROM  group_event_items
                                WHERE  eid = {?}', $eid);

            // deletes the event participants
            XDB::execute('DELETE FROM  group_event_participants
                                WHERE  eid = {?}', $eid);

            // deletes the event
            XDB::execute('DELETE FROM  group_events
                                WHERE  eid = {?} AND asso_id = {?}',
                         $eid, $globals->asso('id'));

            // delete the requests for payments
            XDB::execute("DELETE FROM  requests
                                WHERE  type = 'paiements' AND data LIKE {?}",
                         PayReq::same_event($eid, $globals->asso('id')));
            $globals->updateNbValid();
        }

        if ($action == 'archive') {
            XDB::execute("UPDATE group_events
                             SET archive = 1
                           WHERE eid = {?} AND asso_id = {?}",
                         $eid, $globals->asso('id'));
        }

        if ($action == 'unarchive') {
            XDB::execute("UPDATE group_events
                             SET archive = 0
                           WHERE eid = {?} AND asso_id = {?}",
                         $eid, $globals->asso('id'));
        }

        $page->assign('archive', $archive);
        $evenements = XDB::iterator('SELECT  e.*, LEFT(10, e.debut) AS first_day, LEFT(10, e.fin) AS last_day,
                                             IF(e.deadline_inscription,
                                                     e.deadline_inscription >= LEFT(NOW(), 10),
                                                     1) AS inscr_open,
                                             e.deadline_inscription,
                                             MAX(ep.nb) IS NOT NULL AS inscrit, MAX(ep.paid) AS paid
                                       FROM  group_events              AS e
                                  LEFT JOIN  group_event_participants AS ep ON (ep.eid = e.eid AND ep.uid = {?})
                                      WHERE  asso_id = {?} AND  archive = {?}
                                   GROUP BY  e.eid
                                   ORDER BY  inscr_open DESC, debut DESC',
                                     S::i('uid'), $globals->asso('id'), $archive ? 1 : 0);

        $evts = array();
        $undisplayed_events = 0;
        $this->load('xnetevents.inc.php');

        while ($e = $evenements->next()) {
            if (!is_member() && !may_update() && !$e['accept_nonmembre']) {
                $undisplayed_events ++;
                continue;
            }

            $e['show_participants'] = ($e['show_participants'] && (is_member() || may_update()));
            $e['moments'] = XDB::fetchAllAssoc('SELECT  titre, details, montant, ei.item_id, nb, ep.paid
                                                  FROM  group_event_items AS ei
                                             LEFT JOIN  group_event_participants AS ep
                                                           ON (ep.eid = ei.eid AND ep.item_id = ei.item_id AND ep.uid = {?})
                                                 WHERE ei.eid = {?}',
                                                S::i('uid'), $e['eid']);

            $e['topay'] = 0;
            $e['paid']  = $e['moments'][0]['paid'];
            foreach ($e['moments'] as $m) {
                $e['topay'] += $m['nb'] * $m['montant'];
            }

            $query = XDB::query(
                "SELECT amount
                   FROM payment_transactions AS t
                 WHERE ref = {?} AND uid = {?}", $e['paiement_id'], S::v('uid'));
            $montants = $query->fetchColumn();

            foreach ($montants as $m) {
                $p = strtr(substr($m, 0, strpos($m, 'EUR')), ',', '.');
                $e['paid'] += trim($p);
            }

            make_event_date($e);

            if (Env::has('updated') && $e['eid'] == Env::i('updated')) {
                $page->assign('updated', $e);
            }
            $evts[] = $e;
        }

        $page->assign('evenements', $evts);
        $page->assign('undisplayed_events', $undisplayed_events);
    }

    function handler_sub(&$page, $eid = null)
    {
        $this->load('xnetevents.inc.php');
        $page->changeTpl('xnetevents/subscribe.tpl');

        $evt = get_event_detail($eid);
        if (is_null($evt)) {
            return PL_NOT_FOUND;
        }
        if ($evt === false) {
            global $globals, $platal;
            $url = $globals->asso('sub_url');
            if (empty($url)) {
                $url = $platal->ns . 'subscribe';
            }
            $page->kill('Cet événement est reservé aux membres du groupe ' . $globals->asso('nom') .
                        '. Pour devenir membre, rends-toi sur la page de <a href="' . $url . '">demande d\'inscripton</a>.');
        }

        if (!$evt['inscr_open']) {
            $page->kill('Les inscriptions pour cet événement sont closes');
        }
        if (!$evt['accept_nonmembre'] && !is_member() && !may_update()) {
            $page->kill('Cet événement est fermé aux non-membres du groupe');
        }

        global $globals;
        $res = XDB::query("SELECT  stamp
                             FROM  requests
                            WHERE  type = 'paiements' AND data LIKE {?}",
                           PayReq::same_event($evt['eid'], $globals->asso('id')));
        $page->assign('validation', $res->numRows());
        $page->assign('event', $evt);

        if (!Post::has('submit')) {
            return;
        } else {
            S::assert_xsrf_token();
        }

        $moments = Post::v('moment',    array());
        $pers    = Post::v('personnes', array());
        $subs    = array();

        foreach ($moments as $j => $v) {
            $subs[$j] = intval($v);

            // retreive ohter field when more than one person
            if ($subs[$j] == 2) {
                if (!isset($pers[$j]) || !is_numeric($pers[$j]) || $pers[$j] < 0) {
                    $page->trigError("Tu dois choisir un nombre d'invités correct&nbsp;!");
                    return;
                }
                $subs[$j] = 1 + $pers[$j];
            }
        }

        // impossible to unsubscribe if you already paid sthing
        if (!array_sum($subs) && $evt['paid'] != 0) {
            $page->trigError("Impossible de te désinscrire complètement " .
                            "parce que tu as fait un paiement par " .
                            "chèque ou par liquide. Contacte un " .
                            "administrateur du groupe si tu es sûr de " .
                            "ne pas venir.");
            return;
        }

        // update actual inscriptions
        $updated = false;
        $total   = 0;
        $paid    = $evt['paid'] ? $evt['paid'] : 0;
        $telepaid= $evt['telepaid'] ? $evt['telepaid'] : 0;
        foreach ($subs as $j => $nb) {
            if ($nb >= 0) {
                XDB::execute('INSERT INTO  group_event_participants (eid, uid, item_id, nb, flags, paid)
                                   VALUES  ({?}, {?}, {?}, {?}, {?}, {?})
                  ON DUPLICATE KEY UPDATE  nb = VALUES(nb), flags = VALUES(flags), paid = VALUES(paid)',
                             $eid, S::v('uid'), $j, $nb, (Env::has('notify_payment') ? 'notify_payment' : ''),
                             ($j == 1 ? $paid - $telepaid : 0));
                $updated = $eid;
            } else {
                XDB::execute(
                    "DELETE FROM  group_event_participants
                           WHERE  eid = {?} AND uid = {?} AND item_id = {?}",
                    $eid, S::v("uid"), $j);
                $updated = $eid;
            }
            $total += $nb;
        }
        if ($updated !== false) {
            $page->trigSuccess('Ton inscription à l\'événement a été mise à jour avec succès.');
            subscribe_lists_event(S::i('uid'), $evt, ($total > 0 ? 1 : 0), 0);

            if ($evt['subscription_notification'] != 'nobody') {
                $mailer = new PlMailer('xnetevents/subscription-notif.mail.tpl');
                if ($evt['subscription_notification'] != 'creator') {
                    $admins = $globals->asso()->iterAdmins();
                    while ($admin = $admins->next()) {
                        $mailer->addTo($admin);
                    }
                }
                if ($evt['subscription_notification'] != 'animator') {
                    $mailer->addTo($evt['organizer']);
                }
                $mailer->assign('group', $globals->asso('nom'));
                $mailer->assign('event', $evt['intitule']);
                $mailer->assign('subs', $subs);
                $mailer->assign('moments', $evt['moments']);
                $mailer->assign('name', S::user()->fullName('promo'));
                $mailer->send();
            }
        }
        $page->assign('event', get_event_detail($eid));
    }

    function handler_csv(&$page, $eid = null, $item_id = null)
    {
        $this->load('xnetevents.inc.php');

        if (!is_numeric($item_id)) {
            $item_id = null;
        }

        $evt = get_event_detail($eid, $item_id);
        if (!$evt) {
            return PL_NOT_FOUND;
        }

        pl_content_headers("text/x-csv");
        $page->changeTpl('xnetevents/csv.tpl', NO_SKIN);

        $admin = may_update();

        $tri = (Env::v('order') == 'alpha' ? UserFilter::sortByPromo() : UserFilter::sortByName());

        $page->assign('participants',
                      get_event_participants($evt, $item_id, $tri));

        $page->assign('admin', $admin);
        $page->assign('moments', $evt['moments']);
        $page->assign('money', $evt['money']);
        $page->assign('telepayment', $evt['paiement_id']);
        $page->assign('tout', !Env::v('item_id', false));
    }

    function handler_ical(&$page, $eid = null)
    {
        global $globals;

        $this->load('xnetevents.inc.php');
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
            $page->assign('participants', get_event_participants($evt, null, UserFilter::sortByPromo()));
        }
        $page->register_function('display_ical', 'display_ical');
        $page->assign_by_ref('e', $evt);

        pl_content_headers("text/calendar");
    }

    function handler_edit(&$page, $eid = null)
    {
        global $globals;

        // get eid if the the given one is a short name
        if (!is_null($eid) && !is_numeric($eid)) {
            $res = XDB::query("SELECT eid
                                 FROM group_events
                                WHERE asso_id = {?} AND short_name = {?}",
                              $globals->asso('id'), $eid);
            if ($res->numRows()) {
                $eid = (int)$res->fetchOneCell();
            }
        }

        // check the event is in our group
        if (!is_null($eid)) {
            $res = XDB::query("SELECT short_name
                                 FROM group_events
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
            S::assert_xsrf_token();

            $this->load('xnetevents.inc.php');
            $short_name = event_change_shortname($page, $eid,
                                                 $infos['short_name'],
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

            $trivial = array('intitule', 'descriptif', 'noinvite', 'subscription_notification',
                             'show_participants', 'accept_nonmembre', 'uid');
            foreach ($trivial as $k) {
                $evt[$k] = Post::v($k);
            }
            if (!$eid) {
                $evt['uid'] = S::v('uid');
            }

            if (Post::v('deadline')) {
                $evt['deadline_inscription'] = Post::v('inscr_Year').'-'
                                             . Post::v('inscr_Month').'-'
                                             . Post::v('inscr_Day');
            } else {
                $evt['deadline_inscription'] = null;
            }

            // Store the modifications in the database
            XDB::execute('INSERT INTO  group_events (eid, asso_id, uid, intitule, paiement_id,
                                                     descriptif, debut, fin, show_participants,
                                                     short_name, deadline_inscription, noinvite,
                                                     accept_nonmembre, subscription_notification)
                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})
              ON DUPLICATE KEY UPDATE  asso_id = VALUES(asso_id), uid = VALUES(uid), intitule = VALUES(intitule),
                                       paiement_id = VALUES(paiement_id), descriptif = VALUES(descriptif), debut = VALUES(debut),
                                       fin = VALUES(fin), show_participants = VALUES(show_participants), short_name = VALUES(short_name),
                                       deadline_inscription = VALUES(deadline_inscription), noinvite = VALUES(noinvite),
                                       accept_nonmembre = VALUES(accept_nonmembre), subscription_notification = VALUES(subscription_notification)',
                         $evt['eid'], $evt['asso_id'], $evt['uid'],
                         $evt['intitule'], $evt['paiement_id'], $evt['descriptif'],
                         $evt['debut'], $evt['fin'], $evt['show_participants'],
                         $evt['short_name'], $evt['deadline_inscription'],
                         $evt['noinvite'], $evt['accept_nonmembre'], $evt['subscription_notification']);

            // if new event, get its id
            if (!$eid) {
                $eid = XDB::insertId();
            }

            foreach ($moments as $i) {
                if (Post::v('titre' . $i)) {
                    $nb_moments++;

                    $montant = strtr(Post::v('montant' . $i), ',', '.');
                    $money_defaut += (float)$montant;
                    XDB::execute('INSERT INTO  group_event_items (eid, item_id, titre, details, montant)
                                       VALUES  ({?}, {?}, {?}, {?}, {?})
                      ON DUPLICATE KEY UPDATE  titre = VALUES(titre), details = VALUES(details), montant = VALUES(montant)',
                                 $eid, $i, Post::v('titre' . $i), Post::v('details' . $i), $montant);
                } else {
                    XDB::execute('DELETE FROM  group_event_items
                                        WHERE  eid = {?} AND item_id = {?}', $eid, $i);
                }
            }
            // request for a new payment
            if (Post::v('paiement_id') == -1 && $money_defaut >= 0) {
                $p = new PayReq(S::user(),
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
                XDB::execute("INSERT INTO group_event_items
                                   VALUES ({?}, {?}, '', '', 0)", $eid, 1);
            }

            if (!$error) {
                pl_redirect('events');
            }
        }

        // get a list of all the payment for this asso
        $res = XDB::iterator("SELECT id, text
                                FROM payments
                               WHERE asso_id = {?}", $globals->asso('id'));
        $paiements = array();
        while ($a = $res->next()) $paiements[$a['id']] = $a['text']; {
            $page->assign('paiements', $paiements);
        }

        // when modifying an old event retreive the old datas
        if ($eid) {
            $res = XDB::query(
                    "SELECT  eid, intitule, descriptif, debut, fin, uid,
                             show_participants, paiement_id, short_name,
                             deadline_inscription, noinvite, accept_nonmembre, subscription_notification
                       FROM  group_events
                      WHERE eid = {?}", $eid);
            $evt = $res->fetchOneAssoc();
            // find out if there is already a request for a payment for this event
            $res = XDB::query("SELECT  stamp
                                 FROM  requests
                                WHERE  type = 'paiements' AND data LIKE {?}",
                               PayReq::same_event($eid, $globals->asso('id')));
            $stamp = $res->fetchOneCell();
            if ($stamp) {
                $evt['paiement_id']  = -2;
                $evt['paiement_req'] = $stamp;
            }
            $page->assign('evt', $evt);
            // get all the different moments infos
            $res = XDB::iterator(
                    "SELECT  item_id, titre, details, montant
                       FROM  group_event_items AS ei
                 INNER JOIN  group_events AS e ON(e.eid = ei.eid)
                      WHERE  e.eid = {?}
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

        $this->load('xnetevents.inc.php');

        $evt = get_event_detail($eid, $item_id);
        if (!$evt) {
            return PL_NOT_FOUND;
        }

        $page->changeTpl('xnetevents/admin.tpl');
        if (!$evt['show_participants'] && !may_update()) {
            return PL_FORBIDDEN;
        }

        if (may_update() && Post::v('adm')) {
            S::assert_xsrf_token();

            $member = User::getSilent(Post::v('mail'));
            if (!$member) {
                $page->trigError("Membre introuvable");
            }

            // change the price paid by a participant
            if (Env::v('adm') == 'prix' && $member) {
                $amount = strtr(Env::v('montant'), ',', '.');
                XDB::execute("UPDATE group_event_participants
                                 SET paid = paid + {?}
                               WHERE uid = {?} AND eid = {?} AND item_id = 1",
                             $amount, $member->uid, $evt['eid']);
                subscribe_lists_event($member->uid, $evt, 1, $amount);
            }

            // change the number of personns coming with a participant
            if (Env::v('adm') == 'nbs' && $member) {
                $res = XDB::query("SELECT paid
                                     FROM group_event_participants
                                    WHERE uid = {?} AND eid = {?}",
                                  $member->uid, $evt['eid']);

                $paid = intval($res->fetchOneCell());
                $nbs  = Post::v('nb', array());

                foreach ($nbs as $id => $nb) {
                    $nb = max(intval($nb), 0);
                    XDB::execute('INSERT INTO  group_event_participants (eid, uid, item_id, nb, flags, paid)
                                       VALUES  ({?}, {?}, {?}, {?}, {?}, {?})
                      ON DUPLICATE KEY UPDATE  nb = VALUES(nb), flags = VALUES(flags), paid = VALUES(paid)',
                                 $evt['eid'], $member->uid, $id, $nb, '', ($id == 1 ? $paid : 0));
                }

                $res = XDB::query('SELECT  COUNT(uid) AS cnt, SUM(nb) AS nb
                                     FROM  group_event_participants
                                    WHERE  uid = {?} AND eid = {?}
                                 GROUP BY  uid',
                                  $member->uid, $evt['eid']);
                $u = $res->fetchOneAssoc();
                if ($u['cnt'] == 1 && $paid == 0 && Post::v('cancel')) {
                    XDB::execute("DELETE FROM group_event_participants
                                        WHERE uid = {?} AND eid = {?}",
                                    $member->uid, $evt['eid']);
                    $u = 0;
                    subscribe_lists_event($member->uid, $evt, -1, $paid);
                } else {
                    $u = $u['cnt'] ? $u['nb'] : null;
                    subscribe_lists_event($member->uid, $evt, ($u > 0 ? 1 : 0), $paid);
                }
            }

            $evt = get_event_detail($eid, $item_id);
        }

        $page->assign_by_ref('evt', $evt);
        $page->assign('tout', is_null($item_id));

        if (count($evt['moments'])) {
            $page->assign('moments', $evt['moments']);
        }

        if ($evt['paiement_id']) {
            $infos = User::getBulkUsersWithUIDs(
                            XDB::fetchAllAssoc('SELECT  t.uid, t.amount
                                                  FROM  payment_transactions AS t
                                             LEFT JOIN  group_event_participants AS ep ON(ep.uid = t.uid AND ep.eid = {?})
                                                 WHERE  t.ref = {?} AND ep.uid IS NULL',
                                               $evt['eid'], $evt['paiement_id']),
                            'uid', 'user');
            $page->assign('oublis', count($infos));
            $page->assign('oubliinscription', $infos);
        }

        $absents = User::getBulkUsersFromDB('SELECT  p.uid
                                               FROM  group_event_participants AS p
                                          LEFT JOIN  group_event_participants AS p2 ON (p2.uid = p.uid
                                                                                               AND p2.eid = p.eid
                                                                                               AND p2.nb != 0)
                                              WHERE  p.eid = {?} AND p2.eid IS NULL
                                           GROUP BY  p.uid', $evt['eid']);

        $ofs = Env::i('offset');
        $tot = (is_null($evt['nb_tot']) ? $evt['nb'] : $evt['nb_tot']);
        $nbp = ceil($tot / NB_PER_PAGE);
        if ($nbp > 1) {
            $links = array();
            if ($ofs) {
                $links['précédent'] = $ofs - 1;
            }
            for ($i = 1 ; $i <= $nbp; $i++) {
                $links[(string)$i] = $i - 1;
            }
            if ($ofs < $nbp - 1) {
                $links['suivant'] = $ofs+1;
            }
            $page->assign('links', $links);
        }

        $page->assign('absents', $absents);
        $page->assign('participants',
                      get_event_participants($evt, $item_id, UserFilter::sortByName(),
                                             NB_PER_PAGE, $ofs * NB_PER_PAGE));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
