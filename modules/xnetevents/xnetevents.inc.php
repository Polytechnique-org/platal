<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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
    $res = XDB::query('SELECT  SUM(nb) AS nb_tot, COUNT(DISTINCT ep.uid) AS nb, e.*,
                               IF(e.deadline_inscription,
                                     e.deadline_inscription >= LEFT(NOW(), 10),
                                     1) AS inscr_open,
                               LEFT(10, e.debut) AS start_day, LEFT(10, e.fin) AS last_day,
                               LEFT(NOW(), 10) AS now,
                               ei.titre, al.vid AS absent_list, pl.vid AS participant_list,
                               pyl.vid AS payed_list, bl.vid AS booked_unpayed_list
                         FROM  group_events              AS e
                   INNER JOIN  group_event_items        AS ei ON (e.eid = ei.eid)
                    LEFT JOIN  group_event_participants AS ep ON(e.eid = ep.eid AND ei.item_id = ep.item_id)
                    LEFT JOIN  virtual AS al ON(al.type = \'evt\' AND al.alias = CONCAT(short_name, {?}))
                    LEFT JOIN  virtual AS pl ON(pl.type = \'evt\' AND pl.alias = CONCAT(short_name, {?}))
                    LEFT JOIN  virtual AS pyl ON(pyl.type = \'evt\' AND pyl.alias = CONCAT(short_name, {?}))
                    LEFT JOIN  virtual AS bl ON(bl.type = \'evt\' AND bl.alias = CONCAT(short_name, {?}))
                        WHERE  (e.eid = {?} OR e.short_name = {?}) AND ei.item_id = {?} AND e.asso_id = {?}
                     GROUP BY  ei.item_id',
                   '-absents@'.$globals->xnet->evts_domain,
                   '-participants@'.$globals->xnet->evts_domain,
                   '-paye@' . $globals->xnet->evts_domain,
                   '-participants-non-paye@' . $globals->xnet->evts_domain,
                   $eid, $eid, $item_id ? $item_id : 1, $asso_id);
    $evt = $res->fetchOneAssoc();

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

    $montants = XDB::fetchColumn('SELECT  montant
                                    FROM  ' . $globals->money->mpay_tprefix . 'transactions AS t
                                   WHERE  ref = {?} AND uid = {?}',
                                   $evt['paiement_id'], S::v('uid'));
    $evt['telepaid'] = 0;
    foreach ($montants as $m) {
        $p = strtr(substr($m, 0, strpos($m, 'EUR')), ',', '.');
        $evt['paid'] += trim($p);
        $evt['telepaid'] += trim($p);
    }

    make_event_date($evt);

    return $evt;
}

// }}}

// {{{ function get_event_participants()
function get_event_participants(&$evt, $item_id, array $tri = array(), $count = null, $offset = null)
{
    global $globals;

    $eid    = $evt['eid'];
    $money  = $evt['money'] && (function_exists('may_update')) && may_update();
    $pay_id = $evt['paiement_id'];

    $append = $item_id ? XDB::foramt(' AND ep.item_id = {?}', $item_id) : '';
    $query = XDB::fetchAllAssoc('uid', 'SELECT  ep.uid, SUM(ep.paid) AS paid, SUM(ep.nb) AS nb,
                                                FIND_IN_SET(\'notify_payment\', ep.flags) AS notify_payment
                                          FROM  group_event_participants AS ep
                                         WHERE  ep.eid = {?} AND nb > 0 ' . $append . '
                                      GROUP BY  ep.uid', $eid);
    $uf = new UserFilter(new PFC_True(), $tri);
    $users = User::getBulkUsersWithUIDs($uf->filter(array_keys($query), $count, $offset));
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
            $montants = XDB::fetchColumn('SELECT  montant
                                            FROM  ' . $globals->money->mpay_tprefix . 'transactions AS t
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
function subscribe_lists_event($participate, $uid, $evt, $paid, $payment = null)
{
    global $globals;
    $page =& Platal::page();

    $participant_list  = $evt['participant_list'];
    $absent_list       = $evt['absent_list'];
    $unpayed_list      = $evt['booked_unpayed_list'];
    $payed_list        = $evt['payed_list'];

    $user = User::getSilent($uid);
    if ($user) {
        $email = $user->forlifeEmail();
    } else {
        $res = XDB::query("SELECT  email
                             FROM  group_members
                            WHERE  uid = {?} AND asso_id = {?}",
                          $uid, $globals->asso('id'));
        $email = $res->fetchOneCell();
    }

    function subscribe($list, $email)
    {
        if ($list && $email) {
            XDB::execute("REPLACE INTO  virtual_redirect
                                VALUES  ({?},{?})",
                         $list, $email);
        }
    }

    function unsubscribe($list, $email)
    {
        if ($list && $email) {
            XDB::execute("DELETE FROM  virtual_redirect
                                WHERE  vid = {?} AND redirect = {?}",
                         $list, $email);
        }
    }

    if (is_null($payment)) {
        if (is_null($participate)) {
            unsubscribe($participant_list, $email);
            subscribe($absent_list, $email);
        } elseif ($participate) {
            subscribe($participant_list, $email);
            unsubscribe($absent_list, $email);
        } else {
            unsubscribe($participant_list, $email);
            unsubscribe($absent_list, $email);
        }
    }
    if ($paid > 0) {
        unsubscribe($unpayed_list, $email);
        subscribe($payed_list, $email);
    } else {
        unsubscribe($payed_list, $email);
        if (!is_null($participate)) {
            subscribe($unpayed_list, $email);
        }
    }
}
// }}}

//  {{{ function event_change_shortname()
function event_change_shortname(&$page, $eid, $old, $new)
{
    global $globals;

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
        foreach (array('-absents@', '-participants@', '-paye@', '-participants-non-paye@') as $v) {
            $v .= $globals->xnet->evts_domain;
            XDB::execute("UPDATE  virtual
                             SET  alias = {?}
                           WHERE  type = 'evt' AND alias = {?}",
                         $new . $v, $old . $v);
        }
        return $new;
    }

    if (!$old && $new) {
        // if we have a first new short_name create the lists
        $lastid = array();
        $where = array(
            '-participants@'          => 'ep.nb > 0',
            '-paye@'                  => 'ep.paid > 0',
            '-participants-non-paye@' => 'ep.nb > 0 AND ep.paid = 0'
        );

        foreach (array('-absents@', '-participants@', '-paye@', '-participants-non-paye@') as $v) {
            XDB::execute("INSERT INTO  virtual
                                  SET  type = 'evt', alias = {?}",
                         $new . $v . $globals->xnet->evts_domain);

            $lastid[$v] = XDB::insertId();
        }

        foreach (array('-participants@', '-paye@', '-participants-non-paye@') as $v) {
            XDB::execute("INSERT IGNORE INTO  virtual_redirect (
                                      SELECT  {?} AS vid, IF(al.alias IS NULL, a.email, CONCAT(al.alias, {?})) AS redirect
                                        FROM  group_event_participants AS ep
                                   LEFT JOIN  accounts AS a  ON (ep.uid = a.uid)
                                   LEFT JOIN  aliases  AS al ON (al.uid = a.uid AND al.type = 'a_vie')
                                       WHERE  ep.eid = {?} AND " . $where[$v] . "
                                    GROUP BY  ep.uid)",
                         $lastid[$v], '@' . $globals->mail->domain, $eid);
        }
        XDB::execute("INSERT IGNORE INTO  virtual_redirect (
                                  SELECT  {?} AS vid, IF(al.alias IS NULL, a.email, CONCAT(al.alias, {?})) AS redirect
                                    FROM  group_members AS m
                               LEFT JOIN  accounts  AS a  ON (a.uid = m.uid)
                               LEFT JOIN  aliases   AS al ON (al.uid = a.uid AND al.type = 'a_vie')
                               LEFT JOIN  group_event_participants AS ep ON (ep.uid = m.uid AND ep.eid = {?})
                                   WHERE  m.asso_id = {?} AND ep.uid IS NULL
                                GROUP BY  m.uid)",
                     $lastid['-absents@'], '@' . $globals->mail->domain, $eid, $globals->asso('id'));

        return $new;
    }

    if ($old && !$new) {
        // if we delete the old short name, delete the lists
        foreach (array('-absents@', '-participants@', '-paye@', '-participants-non-paye@') as $v) {
            $v .= $globals->xnet->evts_domain;
            XDB::execute("DELETE  virtual, virtual_redirect
                            FROM  virtual
                       LEFT JOIN  virtual_redirect USING(vid)
                           WHERE  virtual.alias = {?}",
                         $infos['short_name'] . $v);
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
