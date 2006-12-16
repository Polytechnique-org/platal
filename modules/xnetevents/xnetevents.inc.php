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

// {{{ function get_event_detail()

function get_event_detail($eid, $item_id = false)
{
    global $globals;
    $res = XDB::query(
        "SELECT	SUM(nb) AS nb_tot, e.*,
                IF(e.deadline_inscription, e.deadline_inscription >= LEFT(NOW(), 10),
                   1) AS inscr_open,
                LEFT(10, e.debut) AS debut_day, LEFT(10, e.fin) AS fin_day,
                LEFT(NOW(), 10) AS now,
                ei.titre,
                al.vid AS absent_list, pl.vid AS participant_list,
                a.nom, a.prenom, a.promo, aa.alias
           FROM	groupex.evenements              AS e
     INNER JOIN x4dat.auth_user_md5             AS a  ON a.user_id = e.organisateur_uid
     INNER JOIN x4dat.aliases                   AS aa ON (aa.type = 'a_vie' AND aa.id = a.user_id)
     INNER JOIN	groupex.evenements_items        AS ei ON (e.eid = ei.eid)
      LEFT JOIN	groupex.evenements_participants AS ep ON(e.eid = ep.eid AND ei.item_id = ep.item_id)
      LEFT JOIN virtual AS al ON(al.type = 'evt' AND al.alias = CONCAT(short_name, {?}))
      LEFT JOIN virtual AS pl ON(pl.type = 'evt' AND pl.alias = CONCAT(short_name, {?}))
          WHERE	(e.eid = {?} OR e.short_name = {?}) AND ei.item_id = {?} AND e.asso_id = {?} 
       GROUP BY ei.item_id",
       '-absents@'.$globals->xnet->evts_domain,
       '-participants@'.$globals->xnet->evts_domain,
       $eid, $eid, $item_id ? $item_id : 1, $globals->asso('id'));

    $evt = $res->fetchOneAssoc();

    if (!$evt || ($evt['accept_nonmembre'] == 0 && !is_member() && !may_update())) {
        return null;
    }

    // smart calculation of the total number
    if (!$item_id) {
        $res = XDB::query(
               "SELECT MAX(nb)
                  FROM groupex.evenements              AS e
            INNER JOIN groupex.evenements_items        AS ei ON (e.eid = ei.eid)
             LEFT JOIN groupex.evenements_participants AS ep
                       ON (e.eid = ep.eid AND ei.item_id = ep.item_id)
                 WHERE e.eid = {?}
              GROUP BY ep.uid", $evt['eid']);
        $evt['nb_tot'] = array_sum($res->fetchColumn());
        $evt['titre'] = '';
        $evt['item_id'] = 0;
    }

    $res = XDB::query(
        "SELECT titre, details, montant, ei.item_id, nb, ep.paid
           FROM groupex.evenements_items        AS ei
      LEFT JOIN groupex.evenements_participants AS ep
                ON (ep.eid = ei.eid AND ep.item_id = ei.item_id AND uid = {?})
          WHERE ei.eid = {?}",
            S::v('uid'), $evt['eid']);
    $evt['moments'] = $res->fetchAllAssoc();

    $evt['topay'] = 0;
    $evt['paid'] = 0;
    foreach ($evt['moments'] as $m) {
        $evt['topay'] += $m['nb'] * $m['montant'];
        if ($m['montant']) {
            $evt['money'] = true;
        }
        $evt['paid']  = $m['paid'];
    }

    $req = XDB::query(
        "SELECT montant
           FROM {$globals->money->mpay_tprefix}transactions AS t
         WHERE ref = {?} AND uid = {?}", $evt['paiement_id'], S::v('uid'));
    $montants = $req->fetchColumn();

    foreach ($montants as $m) {
        $p = strtr(substr($m, 0, strpos($m, 'EUR')), ',', '.');
        $evt['paid'] += trim($p);
    }

    return $evt;
}

// }}}

// {{{ function get_event_participants()
function get_event_participants($evt, $item_id, $tri, $limit = '') {
    global $globals;

    if (Env::has('initiale')) {
        $where = 'AND IF(u.nom IS NULL, m.nom,
                         IF(u.nom_usage<>"", u.nom_usage, u.nom))
                  LIKE "'.addslashes(Env::v('initiale')).'%"';
    } else {
        $where = '';
    }

    $eid    = $evt['eid'];
    $money  = $evt['money'] && may_update();
    $pay_id = $evt['paiement_id'];

    $query =
          "SELECT  IF(m.origine != 'X',m.nom,IF(u.nom_usage<>'', u.nom_usage, u.nom)) AS nom,
                   IF(m.origine != 'X',m.prenom,u.prenom) AS prenom,
                   IF(m.origine != 'X','extérieur',u.promo) AS promo,
                   IF(m.origine != 'X' OR u.perms = 'pending',m.email,a.alias) AS email,
                   IF(m.origine != 'X',m.sexe,FIND_IN_SET('femme', u.flags)) AS femme,
                   m.perms='admin' AS admin,
                   (m.origine = 'X') AS x,
		           ep.uid, ep.paid, SUM(nb) AS nb 
             FROM  groupex.evenements_participants AS ep
       INNER JOIN  groupex.evenements AS e ON (ep.eid = e.eid)
	    LEFT JOIN  groupex.membres AS m ON ( ep.uid = m.uid AND e.asso_id = m.asso_id)
        LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = ep.uid )
        LEFT JOIN  aliases         AS a ON ( a.id = ep.uid AND a.type='a_vie' )
            WHERE  ep.eid = {?}
                    ".(($item_id)?" AND item_id = $item_id":"")."
                    $where
         GROUP BY  ep.uid
         ORDER BY  $tri $limit";

    if ($item_id) {
        $res = XDB::query($query, $eid);
        return $res->fetchAllAssoc();
    }

    $res = XDB::iterator($query, $eid);
    $tab = array();
    $user = 0;

    while ($u = $res->next()) {
        $u['montant'] = 0;
	if ($money && $pay_id) {
            $res_ = XDB::query(
                "SELECT montant
                   FROM {$globals->money->mpay_tprefix}transactions AS t
                  WHERE ref = {?} AND uid = {?}",
                $pay_id, $u['uid']);
            $montants = $res_->fetchColumn();
            foreach ($montants as $m) {
                    $p = strtr(substr($m, 0, strpos($m, "EUR")), ",", ".");
                    $u['paid'] += trim($p);
            }
	}
        $res_ = XDB::iterator(
            "SELECT ep.nb, ep.item_id, ei.montant
               FROM groupex.evenements_participants AS ep
         INNER JOIN groupex.evenements_items AS ei ON (ei.eid = ep.eid AND ei.item_id = ep.item_id)
              WHERE ep.eid = {?} AND ep.uid = {?}",
            $eid, $u['uid']);
        while ($i = $res_->next()) {
            $u[$i['item_id']] = $i['nb'];
            $u['montant'] += $i['montant']*$i['nb'];
        }
	$tab[] = $u;
    }
    return $tab;
}
// }}}

//  {{{ function subscribe_lists_event()
function subscribe_lists_event($participate, $uid, $evt)
{
    require_once('user.func.inc.php');
    global $globals,$page;

    $participant_list = $evt['participant_list'];
    $absent_list      = $evt['absent_list'];

    $email = get_user_forlife($uid);

    if ($email) {
        $email .= '@'.$globals->mail->domain;
    } else {
        $res = XDB::query("SELECT email
                             FROM groupex.membres
                            WHERE uid = {?} AND asso_id = {?}",
                            S::v('uid'), $globals->asso('id'));
        $email = $res->fetchOneCell();
    }

    function subscribe($list, $email)
    {
        if ($list && $email) {
            XDB::execute("REPLACE INTO virtual_redirect
                                VALUES ({?},{?})",
                         $list, $email);
        }
    }

    function unsubscribe($list, $email)
    {
        if ($list && $email) {
            XDB::execute("DELETE FROM virtual_redirect
                                WHERE vid = {?} AND redirect = {?}",
                         $list, $email);
        }
    }

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
// }}}

function event_change_shortname(&$page, $old, $new)
{
    global $globals;

    if (is_null($old)) {
        $old = '';
    }
    // Quelques vérifications sur l'alias (caractères spéciaux)
    if ($new && !preg_match( "/^[a-zA-Z0-9\-.]{3,20}$/", $new)) {
        $page->trig("Le raccourci demandé n'est pas valide.
                    Vérifie qu'il comporte entre 3 et 20 caractères
                    et qu'il ne contient que des lettres non accentuées,
                    des chiffres ou les caractères - et .");
        return $old;
    }

    //vérifier que l'alias n'est pas déja pris
    if ($new && $old != $new) {
        $res = XDB::query('SELECT COUNT(*)
                             FROM groupex.evenements
                            WHERE short_name = {?}',
                           $new);
        if ($res->fetchOneCell() > 0) {
            $page->trig("Le raccourci demandé est déjà utilisé. Choisis en un autre.");
            return $old;
        }
    }

    if ($old == $new) {
        return $new;
    }

    if ($old && $new) {
        // if had a previous shortname change the old lists
        foreach (array('-absents@', '-participants@') as $v) {
            $v .= $globals->xnet->evts_domain;
            XDB::execute("UPDATE virtual SET alias = {?}
                           WHERE type = 'evt' AND alias = {?}",
                         $new.$v, $old.$v);
        }
        return $new;
    }

    if (!$old && $new) {
        // if we have a first new short_name create the lists

        XDB::execute("INSERT INTO virtual SET type = 'evt', alias = {?}",
                $new.'-participants@'.$globals->xnet->evts_domain);

        $lastid = XDB::insertId();
        XDB::execute(
          "INSERT INTO virtual_redirect (
                SELECT {?} AS vid, IF(u.nom IS NULL, m.email, CONCAT(a.alias, {?})) AS redirect
                  FROM groupex.evenements_participants AS ep
             LEFT JOIN groupex.membres AS m ON (ep.uid = m.uid)
             LEFT JOIN auth_user_md5   AS u ON (u.user_id = ep.uid)
             LEFT JOIN aliases         AS a ON (a.id = ep.uid AND a.type = 'a_vie')
                 WHERE ep.eid = {?}
              GROUP BY ep.uid)",
              $lastid, '@'.$globals->mail->domain, $eid);

        XDB::execute("INSERT INTO virtual SET type = 'evt', alias = {?}",
                $new.'-absents@'.$globals->xnet->evts_domain);

        $lastid = XDB::insertId();
        XDB::execute("INSERT INTO virtual_redirect (
            SELECT {?} AS vid, IF(u.nom IS NULL, m.email, CONCAT(a.alias, {?})) AS redirect
                  FROM groupex.membres AS m
             LEFT JOIN groupex.evenements_participants AS ep ON (ep.uid = m.uid)
             LEFT JOIN auth_user_md5   AS u ON (u.user_id = m.uid)
             LEFT JOIN aliases         AS a ON (a.id = m.uid AND a.type = 'a_vie')
                 WHERE m.asso_id = {?} AND ep.uid IS NULL
              GROUP BY m.uid)",
             $lastid, "@".$globals->mail->domain, $globals->asso('id'));

        return $new;
    }

    if ($old && !$new) {
        // if we delete the old short name, delete the lists
        foreach (array('-absents@', '-participants@') as $v) {
            $v .= $globals->xnet->evts_domain;
            XDB::execute("DELETE virtual, virtual_redirect FROM virtual
                                 LEFT JOIN virtual_redirect USING(vid)
                                     WHERE virtual.alias = {?}",
                                   $infos['short_name'].$v);
        }
        return $new;
    }

    // cannot happen
    return $old;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
