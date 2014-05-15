<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

// {{{ function get_event_order()
/* get the order to paste the events
 * @param $asso_id: group's id
 */
function get_event_order($asso_id)
{
    $order = XDB::fetchOneCell('SELECT g.event_order
                                  FROM groups as g
                                 WHERE id = {?}',
                                       $asso_id);
    return $order;
}
// }}}

// {{{ function get_events()
/* get the events of the given group ordered by the standard order for the group
 * @param $asso_id: group's id
 * @param $order: order to paste the events (asc or desc)
 * @param $archive: whether to get the archived events (1) or the actuals (0)
 */
function get_events($asso_id, $order, $archive)
{
    if ($order != 'asc' && $order != 'desc') {
        $order = 'desc';
    }
    $evts = XDB::fetchAllAssoc('eid', "SELECT ge.eid, ge.uid, ge.intitule, ge.debut, ge.fin, ge.show_participants, ge.deadline_inscription, ge.accept_nonmembre, ge.paiement_id
                                         FROM group_events as ge
                                        WHERE asso_id = {?} and archive = {?}
                                     ORDER BY ge.debut $order",
                                              $asso_id, $archive);
    return $evts;
}
// }}}

// {{{ function get_event() (detail, for subs page only for now)
/* get event details
 * @param $eid: event's id
 */
function get_event(&$eid)
{
    if (!is_numeric($eid)) {
        $id = XDB::fetchOneCell("SELECT eid
                                   FROM group_events
                                  WHERE short_name = {?}",
                                        $eid);
        $eid = $id;
    }
    $evt = XDB::fetchOneAssoc('SELECT ge.uid, ge.intitule, ge.descriptif, ge.debut, ge.fin, ge.deadline_inscription, ge.accept_nonmembre, ge.paiement_id
                                         FROM group_events as ge
                                        WHERE eid = {?}',
                                        $eid);
    if (!is_null($evt['deadline_inscription']) && strtotime($evt['deadline_inscription']) <= time()) {
        $evt['inscr_open'] = false;
    } else {
        $evt['inscr_open'] = true;
    }
    $evt['organizer'] = User::getSilent($evt['uid']);
    $evt['date'] = make_event_date($evt['debut'], $evt['fin']);

    return $evt;
}
// }}}

// {{{ function get_event_items()
/** get items of the given event
 *
 * @param $eid : event's id
 *
 */
function get_event_items($eid)
{
    $evt = XDB::fetchAllAssoc('item_id', 'SELECT gei.item_id, gei.titre, gei.details, gei.montant
        FROM group_event_items as gei
        WHERE eid = {?}',
        $eid);
    return $evt;
}
// }}}

// {{{ function get_event_subscription()
/* get all participations if uid is not specified, only the user's participation if uid specified
 * @param $eid: event's id
 * @param $uid: user's id
 */
function get_event_subscription($eid, $uid = null)
{
    if (!is_null($uid)) {
        $where = ' and gep.uid = '.$uid;
    }
    else {
        $where = '';
    }
   $sub =  XDB::fetchAllAssoc('item_id','SELECT gep.item_id, gep.nb, gep.paid FROM group_event_participants as gep
                                          WHERE gep.eid = {?}'.$where,
                                                $eid);
   return $sub;
}
// }}}

// {{{ function get_event_telepaid()
/* get the total payments made by a user for an event
 * @param $eid: event's id
 * @param $uid: user's id
 */
function get_event_telepaid($eid, $uid)
{
   $telepaid = XDB::fetchOneCell('SELECT SUM(pt.amount)
                                    FROM payment_transactions AS pt
                               LEFT JOIN group_events as ge ON (ge.paiement_id = pt.ref)
                                   WHERE pt.status = "confirmed" AND ge.eid = {?} AND pt.uid = {?}',
                                         $eid, $uid);
    return $telepaid;
}
// }}}

// {{{ function get_event_detail()

function get_event_detail($eid, $item_id = false, $asso_id = null)
{
    global $globals;
    if (is_null($asso_id)) {
        $asso_id = $globals->asso('id');
    }
    if (!$item_id) {
        $where = '';
        $group_by = 'e.eid';
    } else {
        $where = XDB::format(' AND ei.item_id = {?}', $item_id);
        $group_by = 'ei.item_id';
    }
    $evt = XDB::fetchOneAssoc('SELECT  SUM(nb) AS nb_tot, COUNT(DISTINCT ep.uid) AS nb, e.*, SUM(IF(nb > 0, 1, 0)) AS user_count,
                                       IF(e.deadline_inscription,
                                          e.deadline_inscription >= LEFT(NOW(), 10),
                                          1) AS inscr_open,
                                       LEFT(e.debut, 10) AS first_day, LEFT(e.fin, 10) AS last_day,
                                       LEFT(NOW(), 10) AS now,
                                       ei.titre, e.subscription_notification
                                 FROM  group_events             AS e
                           INNER JOIN  group_event_items        AS ei ON (e.eid = ei.eid)
                            LEFT JOIN  group_event_participants AS ep ON(e.eid = ep.eid AND ei.item_id = ep.item_id)
                                WHERE  (e.eid = {?} OR e.short_name = {?}) AND e.asso_id = {?}' . $where . '
                             GROUP BY  ' . $group_by,
                              $eid, $eid, $asso_id);

    if (!$evt) {
        return null;
    }
    if ($GLOBALS['IS_XNET_SITE'] && $evt['accept_nonmembre'] == 0 && !is_member() && !may_update()) {
        return false;
    }

    if (!$item_id) {
        /* Don't try to be to smart here, in case we're getting the global summary, we cannot have
         * a general formula to estimate the total number of comers since 'moments' may (or may not be)
         * disjuncted. As a consequence, we can only provides the number of user having fullfiled the
         * registration procedure.
         */
        $evt['user_count'] = $evt['nb_tot'] = $evt['nb'];
        $evt['titre'] = '';
        $evt['item_id'] = 0;
        $evt['csv_name'] = urlencode($evt['intitule']);
    } else {
        $evt['csv_name'] = urlencode($evt['intitule'] . '.' . $evt['titre']);
    }

    $evt['moments'] = XDB::fetchAllAssoc('SELECT  titre, details, montant, ei.item_id, nb,
                                                  ep.paid, FIND_IN_SET(\'notify_payment\', ep.flags) AS notify_payment
                                            FROM  group_event_items        AS ei
                                       LEFT JOIN  group_event_participants AS ep ON (ep.eid = ei.eid AND ep.item_id = ei.item_id
                                                                                                             AND uid = {?})
                                           WHERE  ei.eid = {?}',
                                           S::i('uid'), $evt['eid']);
    $evt['topay'] = 0;
    $evt['paid'] = 0;
    $evt['notify_payment'] = false;
    foreach ($evt['moments'] as $m) {
        $evt['topay'] += $m['nb'] * $m['montant'];
        if ($m['montant']) {
            $evt['money'] = true;
        }
        $evt['paid'] += $m['paid'];
        $evt['notify_payment'] = $evt['notify_payment'] || $m['notify_payment'];
    }

    $montant = XDB::fetchOneCell('SELECT  SUM(amount) AS sum_amount
                                    FROM  payment_transactions AS t
                                   WHERE  status = "confirmed" AND ref = {?} AND uid = {?}',
                                   $evt['paiement_id'], S::v('uid'));
    $evt['telepaid'] = $montant;
    $evt['paid'] += $montant;
    $evt['organizer'] = User::getSilent($evt['uid']);

    $evt['date'] = make_event_date($evt['debut'], $evt['fin']);

    $evt['show_participants'] = ($evt['show_participants'] && $GLOBALS['IS_XNET_SITE'] && (is_member() || may_update()));

    return $evt;
}

// }}}

// {{{ function get_event_participants()
function get_event_participants(&$evt, $item_id, array $tri = array(), $limit = null, $offset = 0)
{
    global $globals;

    $eid    = $evt['eid'];
    $money  = $evt['money'] && (function_exists('may_update')) && may_update();
    $pay_id = $evt['paiement_id'];

    $append = $item_id ? XDB::format(' AND ep.item_id = {?}', $item_id) : '';
    $query = XDB::fetchAllAssoc('uid', 'SELECT  ep.uid, SUM(ep.paid) AS paid, SUM(ep.nb) AS nb,
                                                FIND_IN_SET(\'notify_payment\', ep.flags) AS notify_payment
                                          FROM  group_event_participants AS ep
                                         WHERE  ep.eid = {?} AND nb > 0 ' . $append . '
                                      GROUP BY  ep.uid', $eid);
    $uf = new UserFilter(new PFC_True(), $tri);
    $users = User::getBulkUsersWithUIDs($uf->filter(array_keys($query), new PlLimit($limit, $offset)));
    $tab = array();
    foreach ($users as $user) {
        $uid = $user->id();
        $tab[$uid] = $query[$uid];
        $tab[$uid]['user'] = $user;
    }

    if ($item_id) {
        return $tab;
    }

    $evt['adminpaid'] = 0;
    $evt['telepaid']  = 0;
    $evt['topay']     = 0;
    $evt['paid']      = 0;
    foreach ($tab as $uid=>&$u) {
        $u['adminpaid'] = (float)$u['paid'];
        $u['montant'] = 0;
        if ($money && $pay_id) {
            $montant = XDB::fetchOneCell('SELECT  SUM(amount)
                                            FROM  payment_transactions AS t
                                           WHERE  status = "confirmed" AND ref = {?} AND uid = {?}',
                                         $pay_id, $uid);
            $u['paid'] += $montant;
        }
        $u['telepayment'] = $u['paid'] - $u['adminpaid'];
        $res_ = XDB::iterator('SELECT  ep.nb, ep.item_id, ei.montant
                                 FROM  group_event_participants AS ep
                           INNER JOIN  group_event_items AS ei ON (ei.eid = ep.eid AND ei.item_id = ep.item_id)
                                WHERE  ep.eid = {?} AND ep.uid = {?}',
                            $eid, $uid);
        while ($i = $res_->next()) {
            $u[$i['item_id']] = $i['nb'];
            $u['montant'] += $i['montant'] * $i['nb'];
        }
        $evt['telepaid']  += $u['telepayment'];
        $evt['adminpaid'] += $u['adminpaid'];
        $evt['paid']      += $u['paid'];
        $evt['topay']     += $u['montant'];
    }
    return $tab;
}
// }}}

//  {{{ function subscribe()
/** set or update the user's subscription
 *
 * @param $uid: user's id
 * @param $eid: event's id
 * @param $subs: user's new subscription
 *
 */
function subscribe($uid, $eid, $subs = array())
{
    global $globals;
    // get items
    $items = get_event_items($eid);
    // get previous subscription
    $old_subs = get_event_subscription($eid, $uid);
    $participate = false;
    $updated = false;
    // TODO : change the way to deal with manual payment
    $paid = 0;
    foreach ($old_subs as $item_id => $s) {
        $paid += $s['paid'];
    }
    $paid_updated = false;
    // for each item of the event
    foreach ($items as $item_id => $details) {
        // check if there is an old subscription
        if (array_key_exists($item_id, $old_subs)) {
            // compares new and old subscription
            if ($old_subs[$item_id]['nb'] != $subs[$item_id]) {
                if ($subs[$item_id] != 0) {
                    echo "je m'inscris  ";
                    XDB::execute('INSERT INTO group_event_participants (eid, uid, item_id, nb, flags, paid)
                                       VALUES ({?}, {?}, {?}, {?}, {?}, {?})
                      ON DUPLICATE KEY UPDATE nb = VALUES(nb), flags = VALUES(flags), paid = VALUES(paid)',
                                             $eid, $uid, $item_id, $subs[$item_id],(Env::has('notify_payment') ? 'notify_payment' : 0), (!$paid_updated ? $paid : 0));
                    $participate = true;
                    $paid_updated = true;
                } else { // we do not store non-subscription to event items
                    XDB::execute('DELETE FROM group_event_participants
                                        WHERE eid = {?} AND uid = {?} AND item_id = {?}',
                                              $eid, $uid, $item_id);
                }
                $updated = true;
            }
        } else { // if no old subscription
            if ($subs[$item_id] != 0) {
                XDB::execute('INSERT INTO group_event_participants (eid, uid, item_id, nb, flags, paid)
                                   VALUES ({?}, {?}, {?}, {?}, {?}, {?})',
                                          $eid, $uid, $item_id, $subs[$item_id], '', 0);
                $participate = true;
                $updated = true;
            }
        }
    }
    // item 0 stores whether the user participates globally or not, if he has to be notified when payment is created and his manual payment
    /*
    if (array_key_exists(0, $old_subs)) {
        XDB::execute('UPDATE group_event_participants
                         SET nb = {?}
                       WHERE eid = {?}, uid = {?}, item_id = 0',
                             ($participate ? 1 : 0), $eid, $uid);
    } else {
        XDB::execute('INSERT INTO group_event_participants (eid, uid, item_id, nb, flags, paid)
                           VALUES ({?}, {?}, {?}, {?}, {?}, {?})',
                                  $eid, $uid, 0, ($participate ? 1 : 0), (Env::has('notify_payment') ? 'notify_payment' : ''), 0);
    }
    */
    // if subscription is updated, we have to update the event aliases
    if ($updated) {
        $short_name = get_event_detail($eid)['short_name'];
        subscribe_lists_event($uid, $short_name, ($participate ? 1 : -1), 0);
    }
    return $updated;
}
//  }}}

// TODO : correct this function to be compatible with subscribe() (use $eid, remove useless argument)
// TODO : correct other calls
//  {{{ function subscribe_lists_event()
/** Subscribes user to various event related mailing lists.
 *
 * @param $uid: user's id.
 * @param short_name: event's short_name, which corresponds to the beginning of the emails.
 * @param participate: indicates if the user takes part at the event or not;
 *      -1 means he did not answer, 0 means no, and 1 means yes.
 * @param paid: has the user already payed anything?
 *      0 means no, a positive amount means yes.
 * @param payment: is this function called from a payment page?
 *      If true, only payment related lists should be updated.
 */
function subscribe_lists_event($uid, $short_name, $participate, $paid, $payment = false)
{
    global $globals;
    require_once 'emails.inc.php';

    if (is_null($short_name)) {
        return;
    }

    /** If $payment is not null, we do not retrieve the value of $participate,
     * thus we do not alter participant and absent lists.
     */
    if ($payment === true) {
        if ($paid > 0) {
            delete_from_list_alias($uid, $short_name . $globals->xnet->unpayed_list, $globals->xnet->evts_domain, 'event');
            add_to_list_alias($uid, $short_name . $globals->xnet->payed_list, $globals->xnet->evts_domain, 'event');
        }
    } else {
        switch ($participate) {
          case -1:
            delete_from_list_alias($uid, $short_name . $globals->xnet->participant_list, $globals->xnet->evts_domain, 'event');
            delete_from_list_alias($uid, $short_name . $globals->xnet->unpayed_list, $globals->xnet->evts_domain, 'event');
            delete_from_list_alias($uid, $short_name . $globals->xnet->payed_list, $globals->xnet->evts_domain, 'event');
            add_to_list_alias($uid, $short_name . $globals->xnet->absent_list, $globals->xnet->evts_domain, 'event');
            break;
          case 0:
            delete_from_list_alias($uid, $short_name . $globals->xnet->participant_list, $globals->xnet->evts_domain, 'event');
            delete_from_list_alias($uid, $short_name . $globals->xnet->absent_list, $globals->xnet->evts_domain, 'event');
            delete_from_list_alias($uid, $short_name . $globals->xnet->unpayed_list, $globals->xnet->evts_domain, 'event');
            delete_from_list_alias($uid, $short_name . $globals->xnet->payed_list, $globals->xnet->evts_domain, 'event');
            break;
          case 1:
            add_to_list_alias($uid, $short_name . $globals->xnet->participant_list, $globals->xnet->evts_domain, 'event');
            delete_from_list_alias($uid, $short_name . $globals->xnet->absent_list, $globals->xnet->evts_domain, 'event');
            if ($paid > 0) {
                delete_from_list_alias($uid, $short_name . $globals->xnet->unpayed_list, $globals->xnet->evts_domain, 'event');
                add_to_list_alias($uid, $short_name . $globals->xnet->payed_list, $globals->xnet->evts_domain, 'event');
            } else {
                add_to_list_alias($uid, $short_name . $globals->xnet->unpayed_list, $globals->xnet->evts_domain, 'event');
                delete_from_list_alias($uid, $short_name . $globals->xnet->payed_list, $globals->xnet->evts_domain, 'event');
            }
            break;
        }
    }
}
// }}}

//  {{{ function event_change_shortname()
function event_change_shortname($page, $eid, $old, $new)
{
    global $globals;
    require_once 'emails.inc.php';

    if (is_null($old)) {
        $old = '';
    }
    // Quelques vérifications sur l'alias (caractères spéciaux)
    if ($new && !preg_match( "/^[a-zA-Z0-9\-.]{3,20}$/", $new)) {
        $page->trigError("Le raccourci demandé n'est pas valide.
                    Vérifie qu'il comporte entre 3 et 20 caractères
                    et qu'il ne contient que des lettres non accentuées,
                    des chiffres ou les caractères - et .");
        return $old;
    } elseif ($new && (is_int($new) || ctype_digit($new))) {
        $page->trigError("Le raccourci demandé ne peut être accepté car il
                         ne contient que des chiffres. Rajoute-lui par exemple
                         une lettre.");
        return $old;
    }

    //vérifier que l'alias n'est pas déja pris
    if ($new && $old != $new) {
        $res = XDB::query('SELECT COUNT(*)
                             FROM group_events
                            WHERE short_name = {?}',
                           $new);
        if ($res->fetchOneCell() > 0) {
            $page->trigError("Le raccourci demandé est déjà utilisé. Choisis en un autre.");
            return $old;
        }
    }

    if ($old == $new) {
        return $new;
    }

    if ($old && $new) {
        // if had a previous shortname change the old lists
        foreach (explode(',', $globals->xnet->event_lists) as $suffix) {
            XDB::execute('UPDATE  email_virtual
                             SET  email = {?}
                           WHERE  type = \'event\' AND email = {?}',
                         $new . $suffix, $old . $suffix);
        }

        return $new;
    }

    if (!$old && $new) {
        // if we have a first new short_name create the lists
        $lastid = array();
        $where = array(
            $globals->xnet->participant_list => 'g.nb > 0',
            $globals->xnet->payed_list       => '(g.paid > 0 OR p.amount > 0)',
            $globals->xnet->unpayed_list     => 'g.nb > 0 AND g.paid = 0 AND p.amount IS NULL'
        );

        foreach (array($globals->xnet->participant_list, $globals->xnet->payed_list, $globals->xnet->unpayed_list) as $suffix) {
            $uids = XDB::fetchColumn('SELECT  g.uid
                                        FROM  group_event_participants AS g
                                  INNER JOIN  group_events             AS e ON (g.eid = e.eid)
                                   LEFT JOIN  payment_transactions     AS p ON (e.paiement_id = p.ref AND g.uid = p.uid)
                                       WHERE  g.eid = {?} AND ' . $where[$suffix],
                                     $eid);
            foreach ($uids as $uid) {
                add_to_list_alias($uid, $new . $suffix, $globals->xnet->evts_domain, 'event');
            }
        }

        $uids = XDB::fetchColumn('SELECT  m.uid
                                    FROM  group_members            AS m
                               LEFT JOIN  group_event_participants AS e ON (e.uid = m.uid AND e.eid = {?})
                                   WHERE  m.asso_id = {?} AND e.uid IS NULL',
                                 $eid, $globals->asso('id'));
        foreach ($uids as $uid) {
            add_to_list_alias($uid, $new . $globals->xnet->absent_list, $globals->xnet->evts_domain, 'event');
        }

        return $new;
    }

    if ($old && !$new) {
        // if we delete the old short name, delete the lists
        foreach (explode(',', $globals->xnet->event_lists) as $suffix) {
            delete_list_alias($old . $suffix, $globals->xnet->evts_domain);
        }

        return $new;
    }

    // cannot happen
    return $old;
}
// }}}

//  {{{ function make_event_date()
function make_event_date($debut, $fin)
{
    $start     = strtotime($debut);
    $end       = strtotime($fin);
//    $first_day = $e['first_day'];
//    $last_day  = $e['last_day'];
    $first_day = strftime("%d %B %Y", $start);
    $last_day = strftime("%d %B %Y", $end);
    $date = "";
    if ($start && $end != $start) {
        if ($first_day == $last_day) {
          $date .= "le " . strftime("%d %B %Y", $start) . " de "
                . strftime("%H:%M", $start) . " à " . strftime("%H:%M", $end);
        } else {
          $date .= "du " . strftime("%d %B %Y à %H:%M", $start)
                . "\nau " . strftime("%d %B %Y à %H:%M", $end);
        }
    } else {
        $date .= "le " . strftime("%d %B %Y à %H:%M", $start);
    }
    return $date;
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
