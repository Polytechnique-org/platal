<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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
function get_event_detail($eid, $item_id = false) {
    global $globals;
    $res = $globals->xdb->query(
        "SELECT	SUM(nb) AS nb_tot, e.intitule, ei.titre,
                debut AS deb, fin, membres_only, descriptif, e.eid,
                e.show_participants, e.paiement_id, e.short_name,
                al.vid AS absent_list, pl.vid AS participant_list,
                a.nom, a.prenom, a.promo
           FROM	groupex.evenements AS e
     INNER JOIN	groupex.evenements_items AS ei ON (e.eid = ei.eid)
      LEFT JOIN	groupex.evenements_participants AS ep ON(e.eid = ep.eid AND ei.item_id = ep.item_id)
      LEFT JOIN virtual AS al ON(al.type = 'evt' AND al.alias = CONCAT(short_name, {?}))
      LEFT JOIN virtual AS pl ON(pl.type = 'evt' AND pl.alias = CONCAT(short_name, {?}))
      LEFT JOIN auth_user_md5 AS a ON (a.user_id = e.organisateur_uid)
          WHERE	e.eid = {?} AND ei.item_id = {?} AND e.asso_id = {?} 
       GROUP BY ei.item_id",
       '-absents@'.$globals->mail->domain,
       '-participants@'.$globals->mail->domain,
       $eid, $item_id?$item_id:1, $globals->asso('id'));
    $evt = $res->fetchOneAssoc();
    if (!$evt) return false;

    // smart calculation of the total number
    if (!$item_id) {
        $res = $globals->xdb->query(
               "SELECT MAX(nb)
                  FROM groupex.evenements AS e
            INNER JOIN groupex.evenements_items AS ei ON (e.eid = ei.eid)
             LEFT JOIN groupex.evenements_participants AS ep ON(e.eid = ep.eid AND ei.item_id = ep.item_id)
                 WHERE e.eid = {?}
              GROUP BY ep.uid", $eid);
        $evt['nb_tot'] = array_sum($res->fetchColumn());
        $evt['titre'] = '';
        $evt['item_id'] = 0;
    }
    
    $res = $globals->xdb->iterator(
	"SELECT eid, item_id, titre, montant
	   FROM groupex.evenements_items
	  WHERE eid = {?}",
	$eid);
    $moments = array(); $evt['money'] = false;
    while ($m = $res->next()) {
        $moments[$m['item_id']] = $m;
        if ($m['montant']  > 0) $evt['money'] = true;
    }
    $evt['moments'] = $moments;
    return $evt;
}
// }}}

// {{{ function get_event_participants()
function get_event_participants($eid, $item_id, $where, $tri, $limit, $money, $pay_id) {
    global $globals;
    $query =
        "SELECT  IF(u.nom IS NULL,m.nom,IF(u.nom_usage<>'', u.nom_usage, u.nom)) AS nom,
                   IF(u.nom IS NULL,m.prenom,u.prenom) AS prenom,
                   IF(u.nom IS NULL,'extérieur',u.promo) AS promo,
                   IF(u.nom IS NULL,m.email,a.alias) AS email,
                   IF(u.nom IS NULL,0,FIND_IN_SET('femme', u.flags)) AS femme,
                   m.perms='admin' AS admin,
                   NOT(u.nom IS NULL) AS x,
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
	   ORDER BY  $tri
	      $limit";
    if ($item_id) {
        $res = $globals->xdb->query($query, $eid);
        return $res->fetchAllAssoc();
    }
    $res = $globals->xdb->iterator($query, $eid);
    $tab = array();
    $user = 0;
    while ($u = $res->next()) {
        $u['montant'] = 0;
	if ($money && $pay_id) {
            $res_ = $globals->xdb->query(
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
        $res_ = $globals->xdb->iterator(
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
function subscribe_lists_event($participate, $uid, $participant_list, $absent_list) {
    global $globals,$page;
    
    $email = Session::get('forlife');

    if ($email) {
        $email .= '@'.$globals->mail->domain;
    } else {
        $res = $globals->xdb->query("SELECT email FROM groupex.membres WHERE uid = {?} AND asso_id = {?}", Session::get('uid'), $globals->asso('id'));
        $email = $res->fetchOneCell();
    }

    $subscribe = $participate?$participant_list:(is_member()?$absent_list:0);
    $unsubscri = $participate?$absent_list:$participant_list;

    if ($subscribe) {
        $globals->xdb->execute(
            "REPLACE INTO virtual_redirect VALUES({?},{?})",
		 $subscribe, $email);
    }

    if ($unsubscri) {
        $globals->xdb->execute(
            "DELETE FROM virtual_redirect WHERE vid = {?} AND redirect = {?}",
                $unsubscri, $email);
    }

    if ($participate) {
        $page->trig("tu es maintenant inscrit à l'évenement, suis le lien en bas si tu souhaites procéder à un paiment par le web");
    } else {
        $page->trig("tu es maintenant désinscrit de cet évenement");
    }
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
