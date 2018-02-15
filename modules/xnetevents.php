<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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
            '%grp/events'       => $this->make_hook('events', AUTH_PASSWD, 'groups'),
            '%grp/events/sub'   => $this->make_hook('sub',    AUTH_PASSWD, 'groups'),
            '%grp/events/csv'   => $this->make_hook('csv',    AUTH_PASSWD, 'groups', NO_HTTPS),
            '%grp/events/ical'  => $this->make_hook('ical',   AUTH_PASSWD, 'groups', NO_HTTPS),
            '%grp/events/edit'  => $this->make_hook('edit',   AUTH_PASSWD, 'groupadmin'),
            '%grp/events/admin' => $this->make_hook('admin',  AUTH_PASSWD, 'groupmember'),
        );
    }

    function handler_events($page, $archive = null)
    {
        global $globals;

        $page->changeTpl('xnetevents/index.tpl');
        $this->load('xnetevents.inc.php');

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
                require_once 'emails.inc.php';
                foreach (explode(',', $globals->xnet->event_lists) as $suffix) {
                    delete_list_alias($tmp[1] . $suffix, $globals->xnet->evts_domain, 'event');
                }
            }

            // archive le paiement associé si il existe
            $pay_id = XDB::fetchOneCell("SELECT paiement_id
                                           FROM group_events
                                          WHERE eid = {?} AND asso_id = {?}",
                                        $eid, $globals->asso('id')); 
            if (!$pay_id=='') {
                XDB::execute("UPDATE payments
                                 SET flags = 'old'
                               WHERE id = {?}",
                             $pay_id);
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
            $pay_id = XDB::fetchOneCell("SELECT paiement_id 
                                           FROM group_events
                                          WHERE eid = {?} AND asso_id = {?}",
                                        $eid, $globals->asso('id'));
            if (!$pay_id=='') {
                XDB::execute("UPDATE payments
                                 SET flags = 'old'
                               WHERE id = {?}",
                               $pay_id);
            }
            XDB::execute("UPDATE group_events
                             SET archive = 1
                           WHERE eid = {?} AND asso_id = {?}",
                           $eid, $globals->asso('id'));
        }

        if ($action == 'unarchive') {
            $pay_id = XDB::fetchOneCell("SELECT paiement_id FROM group_events
                                     WHERE eid = {?} AND asso_id = {?}",
                                   $eid, $globals->asso('id'));
            if (!$pay_id=='') {
                XDB::execute("UPDATE payments
                                 SET flags = ''
                               WHERE id = {?}",
                               $pay_id);
            }
            XDB::execute("UPDATE group_events
                             SET archive = 0
                           WHERE eid = {?} AND asso_id = {?}",
                         $eid, $globals->asso('id'));
        }

        $page->assign('archive', $archive);

        if (Post::has('order')) {
            $order = Post::v('order');
            XDB::execute("UPDATE groups
                             SET event_order = {?}
                           WHERE id = {?}",
                          $order, $globals->asso('id'));
        }
        $order = get_event_order($globals->asso('id'));
        $evts = get_events($globals->asso('id'), $order, $archive);
        $page->assign('order', $order);

        $undisplayed_events = 0;
        foreach ($evts as $eid => &$e) {
            if (!is_member() && !may_update() && !$e['accept_nonmembre']) {
                $undisplayed_events ++;
                continue;
            }

            $e['show_participants'] = ($e['show_participants'] && (is_member() || may_update()));
            $e['items'] = get_event_items($eid);
            $e['topay'] = 0;
            $e['paid']  = 0;
            $sub = get_event_subscription($eid, S::i('uid'));
            if (empty($sub)) {
                $e['inscrit'] = false;
            } else {
                $e['inscrit'] = true;
                foreach ($e['items'] as $item_id => $m) {
                    if (isset($sub[$item_id])) {
                        $e['topay'] += $sub[$item_id]['nb'] * $m['montant'];
                        $e['paid'] += $sub[$item_id]['paid'];
                    }
                }
            }
            $e['sub'] = $sub;

            $telepaid = get_event_telepaid($eid, S::i('uid'));
            $e['paid'] += $telepaid;

            $e['date'] = make_event_date($e['debut'], $e['fin']);
            // Add 24 hours to the deadline as it is a date which goes until 23:59:59
            if (!is_null($e['deadline_inscription']) && strtotime($e['deadline_inscription']) + 86400 < time()) {
                $e['inscr_open'] = false;
            } else {
                $e['inscr_open'] = true;
            }

            if (Env::has('updated') && $e['eid'] == Env::i('updated')) {
                $page->assign('updated', $e);
            }
        }

        $page->assign('evenements', $evts);
        $page->assign('undisplayed_events', $undisplayed_events);
    }

    function handler_sub($page, $eid = null)
    {
        $this->load('xnetevents.inc.php');
        $page->changeTpl('xnetevents/subscribe.tpl');

        $evt = get_event($eid);
        if (is_null($evt)) {
            return PL_NOT_FOUND;
        }

        global $globals;

        if (!$evt['inscr_open']) {
            $page->kill('Les inscriptions pour cet événement sont closes');
        }
        if (!$evt['accept_nonmembre'] && !is_member() && !may_update()) {
            $url = $globals->asso('sub_url');
            if (empty($url)) {
                $url = $platal->ns . $globals->asso('diminutif') . "/" . 'subscribe';
            }
            $page->kill('Cet événement est réservé aux membres du groupe ' . $globals->asso('nom') .
                        '. Pour devenir membre, rends-toi sur la page de <a href="' . $url . '">demande d\'inscripton</a>.');
        }

        $res = XDB::query("SELECT  stamp
                             FROM  requests
                            WHERE  type = 'paiements' AND data LIKE {?}",
                           PayReq::same_event($eid, $globals->asso('id')));
        $page->assign('validation', $res->numRows());

        $page->assign('eid', $eid);
        $page->assign('event', $evt);

        $items = get_event_items($eid);
        $subs = get_event_subscription($eid, S::v('uid'));

        if (Post::has('submit')) {
            S::assert_xsrf_token();
            $moments = Post::v('moment',    array());
            $pers    = Post::v('personnes', array());
            $old_subs = $subs;
            $subs    = array();

            foreach ($moments as $j => $v) {
                $subs[$j] = intval($v);

                // retrieve other field when more than one person
                if ($subs[$j] == 2) {
                    if (!isset($pers[$j]) || !is_numeric($pers[$j]) || $pers[$j] < 0) {
                        $page->trigError("Tu dois choisir un nombre d'invités correct&nbsp;!");
                        return;
                    }
                    $subs[$j] = $pers[$j];
                }
            }

            // count what the user must pay, and what he manually paid
            $manual_paid = 0;
            foreach ($items as $item_id => $item) {
                if (array_key_exists($item_id, $old_subs)) {
                    $manual_paid += $old_subs[$item_id]['paid'];
                }
            }
            // impossible to unsubscribe if you already paid sthing
            if (!array_sum($subs) && $manual_paid != 0) {
                $page->trigError("Impossible de te désinscrire complètement " .
                                "parce que tu as fait un paiement par " .
                                "chèque ou par liquide. Contacte un " .
                                "administrateur du groupe si tu es sûr de " .
                                "ne pas venir.");
                $updated = false;
            } else {
                // update actual inscriptions
                $updated = subscribe(S::v('uid'), $eid, $subs);
            }
            if ($updated) {
                $evt = get_event_detail($eid);
                if ($evt['topay'] > 0) {
                    $page->trigSuccess('Ton inscription à l\'événement a été mise à jour avec succès, tu peux payer ta participation en cliquant ci-dessous');
                } else {
                    $page->trigSuccess('Ton inscription à l\'événement a été mise à jour avec succès.');
                }

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
        }
        $subs = get_event_subscription($eid, S::v('uid'));
        // count what the user must pay
        $topay = 0;
        $manually_paid = 0;
        foreach ($items as $item_id => $item) {
            if (array_key_exists($item_id, $subs)) {
                $topay += $item['montant']*$subs[$item_id]['nb'];
                $manually_paid += $subs[$item_id]['paid'];
            }
        }
        $paid = $manually_paid + get_event_telepaid($eid, S::v('uid'));
        $page->assign('moments', $items);
        $page->assign('subs', $subs);
        $page->assign('topay', $topay);
        $page->assign('paid', $paid);
    }

    function handler_csv($page, $eid = null, $item_id = null)
    {
        $this->load('xnetevents.inc.php');

        if (!is_numeric($item_id)) {
            $item_id = null;
        }

        $evt = get_event_detail($eid, $item_id);
        if (!$evt) {
            return PL_NOT_FOUND;
        }

        pl_cached_content_headers('text/x-csv', 'iso-8859-1', 1);
        $page->changeTpl('xnetevents/csv.tpl', NO_SKIN);

        $admin = may_update();
        $tri = (Env::v('order') == 'alpha' ? UserFilter::sortByPromo() : UserFilter::sortByName());
        $all = !Env::v('item_id', false);

        $participants = get_event_participants($evt, $item_id, $tri);
        $title = 'Nom;Prénom;Promotion;Email';
        if ($admin) {
            $title .=';Société;Poste';
        }
        if ($all) {
            foreach ($evt['moments'] as $moment) {
                $title .= ';' . $moment['titre'];
            }
        }
        if ($admin && $evt['money']) {
            $title .= ';À payer;';
            if ($evt['paiement_id']) {
                $title .= 'Télépaiement;Liquide/Chèque;';
            }
            $title .= 'Payé';
        } else {
            $title .= ';Nombre';
        } 
        echo utf8_decode($title) . "\n";

        if ($participants) {
            foreach ($participants as $participant) {
                $user = $participant['user'];
                $line = $user->lastName() . ';' . $user->firstName() . ';' . $user->promo() . ';' . $user->bestEmail();
                if ($admin && $user->hasProfile()) {
                    $line .= ';' . $user->profile()->getMainJob()->company->name . ';' . $user->profile()->getMainJob()->description;
                } else  {
                    $line .= ';;';
                }
                if ($all) {
                    foreach ($evt['moments'] as $moment) {
                        $line .= ';' . $participant[$moment['item_id']];
                    }
                }
                if ($admin && $evt['money']) {
                    $line .= ';' . $participant['montant'] . ';';
                    if ($evt['paiement_id']) {
                        $line .= $participant['telepayment'] . ';' . $participant['adminpaid'] . ';';
                    }
                    $line .= $participant['paid'];
                } else {
                    $line .= ';' . $participant['nb'];
                }

                echo utf8_decode($line) . "\n";
            }
        }
        exit();
    }

    function handler_ical($page, $eid = null)
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

    function handler_edit($page, $eid = null)
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
                                $globals->asso('nom')." - ".Post::v('intitule'),
                                Post::v('site'), $money_defaut,
                                Post::v('confirmation'), 0, 999,
                                $globals->asso('id'), $eid, Post::v('payment_public') == 'yes');
                if ($p->accept()) {
                    $p->submit();
                } else {
                    $page->assign('payment_message', Post::v('confirmation'));
                    $page->assign('payment_site', Post::v('site'));
                    $page->assign('payment_public', Post::v('payment_public') == 'yes');
                    $page->assign('error', true);
                    $error = true;
                }
            }

            // events with no sub-event: add a sub-event with default name
            if ($nb_moments == 0) {
                XDB::execute("INSERT INTO group_event_items
                                   VALUES ({?}, {?}, 'Événement', '', 0)", $eid, 1);
            }

            if (!$error) {
                pl_redirect('events');
            }
        }

        // get a list of all the payment for this asso
        $res = XDB::iterator("SELECT  id, text
                                FROM  payments
                               WHERE  asso_id = {?} AND NOT FIND_IN_SET('old', flags)",
                             $globals->asso('id'));
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

    function handler_admin($page, $eid = null, $item_id = null)
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
                               WHERE uid = {?} AND eid = {?} AND nb > 0
                            ORDER BY item_id ASC
                               LIMIT 1",
                             $amount, $member->uid, $evt['eid']);
                subscribe_lists_event($member->uid, $evt['short_name'], 1, $amount);
            }

            // change the number of personns coming with a participant
            if (Env::v('adm') == 'nbs' && $member) {
                $res = XDB::query("SELECT SUM(paid)
                                     FROM group_event_participants
                                    WHERE uid = {?} AND eid = {?}",
                                  $member->uid, $evt['eid']);

                $paid = $res->fetchOneCell();

                // Ensure we have an integer
                if ($paid == null) {
                    $paid = 0;
                }

                $nbs  = Post::v('nb', array());

                $paid_inserted = false;
                foreach ($nbs as $id => $nb) {
                    $nb = max(intval($nb), 0);
                    if (!$paid_inserted && $nb > 0) {
                        $item_paid = $paid;
                        $paid_inserted = true;
                    } else {
                        $item_paid = 0;
                    }
                    XDB::execute('INSERT INTO  group_event_participants (eid, uid, item_id, nb, flags, paid)
                                       VALUES  ({?}, {?}, {?}, {?}, {?}, {?})
                      ON DUPLICATE KEY UPDATE  nb = VALUES(nb), flags = VALUES(flags), paid = VALUES(paid)',
                                 $evt['eid'], $member->uid, $id, $nb, '', $item_paid);
                }

                $res = XDB::query('SELECT  COUNT(uid) AS cnt, SUM(nb) AS nb
                                     FROM  group_event_participants
                                    WHERE  uid = {?} AND eid = {?}
                                 GROUP BY  uid',
                                  $member->uid, $evt['eid']);
                $u = $res->fetchOneAssoc();
                if ($paid == 0 && Post::v('cancel')) {
                    XDB::execute("DELETE FROM group_event_participants
                                        WHERE uid = {?} AND eid = {?}",
                                    $member->uid, $evt['eid']);
                    $u = 0;
                    subscribe_lists_event($member->uid, $evt['short_name'], -1, $paid);
                } else {
                    $u = $u['cnt'] ? $u['nb'] : null;
                    subscribe_lists_event($member->uid, $evt['short_name'], ($u > 0 ? 1 : 0), $paid);
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
                                                 WHERE  t.status = "confirmed" AND t.ref = {?} AND ep.uid IS NULL',
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
        $part = get_event_participants($evt, $item_id, UserFilter::sortByName(),
                                       NB_PER_PAGE, $ofs * NB_PER_PAGE);

        $nbp = ceil($evt['user_count'] / NB_PER_PAGE);
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
        $page->assign('participants', $part);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
