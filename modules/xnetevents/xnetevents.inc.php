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

// {{{ function get_event_detail()

function get_event_detail($eid, $item_id = false, $asso_id = null)
{
    global $globals;
    if (is_null($asso_id)) {
        $asso_id = $globals->asso('id');
    }
    $evt = XDB::fetchOneAssoc('SELECT  SUM(nb) AS nb_tot, COUNT(DISTINCT ep.uid) AS nb, e.*,
                                       IF(e.deadline_inscription,
                                          e.deadline_inscription >= LEFT(NOW(), 10),
                                          1) AS inscr_open,
                                       LEFT(10, e.debut) AS start_day, LEFT(10, e.fin) AS last_day,
                                       LEFT(NOW(), 10) AS now,
                                       ei.titre, e.subscription_notification
                                 FROM  group_events             AS e
                           INNER JOIN  group_event_items        AS ei ON (e.eid = ei.eid)
                            LEFT JOIN  group_event_participants AS ep ON(e.eid = ep.eid AND ei.item_id = ep.item_id)
                                WHERE  (e.eid = {?} OR e.short_name = {?}) AND ei.item_id = {?} AND e.asso_id = {?}
                             GROUP BY  ei.item_id',
                           $eid, $eid, $item_id ? $item_id : 1, $asso_id);

    if (!$evt) {
        return null;
    }
    if ($GLOBALS['IS_XNET_SITE'] && $evt['accept_nonmembre'] == 0 && !is_member() && !may_update()) {
        return false;
    }

    // smart calculation of the total number
    if (!$item_id) {
        $res = XDB::query('SELECT  MAX(nb)
                             FROM  group_events              AS e
                       INNER JOIN  group_event_items        AS ei ON (e.eid = ei.eid)
                        LEFT JOIN  group_event_participants AS ep ON (e.eid = ep.eid AND ei.item_id = ep.item_id)
                            WHERE  e.eid = {?}
                         GROUP BY  ep.uid', $evt['eid']);
        $evt['nb_tot'] = array_sum($res->fetchColumn());
        $evt['titre'] = '';
        $evt['item_id'] = 0;
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
        $evt['paid']  = $m['paid'];
        $evt['notify_payment'] = $evt['notify_payment'] || $m['notify_payment'];
    }

    $montants = XDB::fetchColumn('SELECT  amount
                                    FROM  payment_transactions AS t
                                   WHERE  ref = {?} AND uid = {?}',
                                   $evt['paiement_id'], S::v('uid'));
    $evt['telepaid'] = 0;
    foreach ($montants as $m) {
        $p = strtr(substr($m, 0, strpos($m, 'EUR')), ',', '.');
        $evt['paid'] += trim($p);
        $evt['telepaid'] += trim($p);
    }
    $evt['organizer'] = User::getSilent($evt['uid']);

    make_event_date($evt);

    return $evt;
}

// }}}

// {{{ function get_event_participants()
function get_event_participants(&$evt, $item_id, array $tri = array(), $limit = null)
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
    $users = User::getBulkUsersWithUIDs($uf->filter(array_keys($query), new PlLimit($count, $offset)));
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
        $u['adminpaid'] = $u['paid'];
        $u['montant'] = 0;
        if ($money && $pay_id) {
            $montants = XDB::fetchColumn('SELECT  amount
                                            FROM  payment_transactions AS t
                                           WHERE  ref = {?} AND uid = {?}',
                                         $pay_id, $uid);
            foreach ($montants as $m) {
                $p = strtr(substr($m, 0, strpos($m, "EUR")), ",", ".");
                $u['paid'] += trim($p);
            }
        }
        $u['telepayment'] = $u['paid'] - $u['adminpaid'];
        $res_ = XDB::iterator('SELECT  ep.nb, ep.item_id, ei.montant
                                 FROM  group_event_participants AS ep
                           INNER JOIN  group_event_items AS ei ON (ei.eid = ep.eid AND ei.item_id = ep.item_id)
                                WHERE  ep.eid = {?} AND ep.uid = {?}',
                            $eid, $uid);
        while ($i = $res_->next()) {
            $u[$i['item_id']] = $i['nb'];
            $u['montant'] += $i['montant']*$i['nb'];
        }
        $evt['telepaid']  += $u['telepayment'];
        $evt['adminpaid'] += $u['adminpaid'];
        $evt['paid']      += $u['paid'];
        $evt['topay']     += $u['montant'];
    }
    return $tab;
}
// }}}

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
            $globals->xnet->participant_list => 'nb > 0',
            $globals->xnet->payed_list       => 'paid > 0',
            $globals->xnet->unpayed_list     => 'nb > 0 AND paid = 0'
        );

        foreach (array($globals->xnet->participant_list, $globals->xnet->payed_list, $globals->xnet->unpayed_list) as $suffix) {
            $uids = XDB::fetchColumn('SELECT  uid
                                        FROM  group_event_participants
                                       WHERE  eid = {?} AND ' . $where[$suffix],
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
function make_event_date(&$e)
{
    $start     = strtotime($e['debut']);
    $end       = strtotime($e['fin']);
    $first_day = @strtotime($e['first_day']);
    $last_day  = strtotime($e['last_day']);
    unset($e['debut'], $e['fin'], $e['first_day'], $e['last_day']);

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
    $e['date'] = $date;
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
