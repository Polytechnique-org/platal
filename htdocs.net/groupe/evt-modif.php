<?php
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
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

require 'xnet.inc.php';

new_group_page('xnet/groupe/evt-modif.tpl');

$page->assign('logged', logged());
$page->assign('admin', may_update());

$moments = range(1, 4);
$page->assign('moments', $moments);

$page->assign('eid', Env::get('eid'));

if (!may_update())
    redirect("evenements.php");

if ($eid = Env::get('eid')) {
	$res = $globals->xdb->query("SELECT asso_id, short_name FROM groupex.evenements WHERE eid = {?}", $eid);
	$infos = $res->fetchOneAssoc();
	if ($infos['asso_id'] != $globals->asso('id')) {
		unset($eid);
		unset($infos);
	}
}

$get_form = true;

if (Post::get('intitule')) {
    $get_form = false;
    $short_name = Env::get('short_name');
    //Quelques vérifications sur l'alias (caractères spéciaux)
    if ($short_name && !preg_match( "/^[a-zA-Z0-9\-.]{3,20}$/", $short_name))
    {
        $page->trig("Le raccourci demandé n'est pas valide.
                    Vérifie qu'il comporte entre 3 et 20 caractères
                    et qu'il ne contient que des lettres non accentuées,
                    des chiffres ou les caractères - et .");
        $short_name = $infos['short_name'];
        $get_form = true;
    }
    //vérifier que l'alias n'est pas déja pris
    if ($short_name && $short_name != $infos['short_name']) {
        $res = $globals->xdb->query('SELECT COUNT(*) FROM virtual WHERE alias LIKE {?}', $short_name."-%");
        if ($res->fetchOneCell() > 0) {
            $page->trig("Le raccourci demandé est déjà utilisé. Choisis en un autre.");
            $short_name = $infos['short_name'];
            $get_form = true;
        }
    }

    // if had a previous shortname change the old lists
    if ($short_name && $infos['short_name'] && $short_name != $infos['short_name']) {
        $globals->xdb->execute("UPDATE virtual SET alias = REPLACE(alias, {?}, {?}) WHERE type = 'evt' AND alias LIKE {?}",
                $infos['short_name'], $short_name, $infos['short_name']."-%");
    } 
    // if we have a first new short_name create the lists
    elseif ($short_name && !$infos['short_name'])
    {
        $globals->xdb->execute("INSERT INTO virtual SET type = 'evt', alias = {?}",
                $short_name."-participants@".$globals->xnet->evts_domain);
            
        $res = $globals->xdb->query("SELECT LAST_INSERT_ID()");
        $globals->xdb->execute("INSERT INTO virtual_redirect (
            SELECT {?} AS vid, IF(u.nom IS NULL, m.email, CONCAT(a.alias, {?})) AS redirect
              FROM groupex.evenements_participants AS ep
         LEFT JOIN groupex.membres AS m ON (ep.uid = m.uid)
         LEFT JOIN auth_user_md5 AS u ON (u.user_id = ep.uid)
         LEFT JOIN aliases AS a ON (a.id = ep.uid AND a.type = 'a_vie')
             WHERE ep.eid = {?}
          GROUP BY ep.uid)",
                 $res->fetchOneCell(), "@".$globals->mail->domain, $eid);

        $globals->xdb->execute("INSERT INTO virtual SET type = 'evt', alias = {?}",
                $short_name."-absents@".$globals->xnet->evts_domain);
                
        $res = $globals->xdb->query("SELECT LAST_INSERT_ID()");
        $globals->xdb->execute("INSERT INTO virtual_redirect (
            SELECT {?} AS vid, IF(u.nom IS NULL, m.email, CONCAT(a.alias, {?})) AS redirect
                  FROM groupex.membres AS m
             LEFT JOIN groupex.evenements_participants AS ep ON (ep.uid = m.uid)
             LEFT JOIN auth_user_md5 AS u ON (u.user_id = m.uid)
             LEFT JOIN aliases AS a ON (a.id = m.uid AND a.type = 'a_vie')
                 WHERE m.asso_id = {?} AND ep.uid IS NULL
              GROUP BY m.uid)",
                 $res->fetchOneCell(), "@".$globals->mail->domain, $globals->asso('id'));
    }
    // if we delete the old short name, delete the lists
    elseif (!$short_name && $infos['short_name']) {
        $globals->xdb->execute("DELETE virtual, virtual_redirect FROM virtual LEFT JOIN virtual_redirect USING(vid) WHERE virtual.alias LIKE {?}",
                $infos['short_name']."-%");
    }

    $evt = array();
    $evt['eid'] = $eid;
    $evt['asso_id'] = $globals->asso('id');
    $evt['organisateur_uid'] = Session::get('uid');
    $evt['intitule'] = Post::get('intitule');
    $evt['paiement_id'] =(Post::get('paiement_id')>0)?Post::get('paiement_id'):null;
    $evt['descriptif'] =Post::get('descriptif');
    $evt['debut'] = Post::get('deb_Year')."-".Post::get('deb_Month')."-".Post::get('deb_Day')." ".Post::get('deb_Hour').":".Post::get('deb_Minute').":00";
    $evt['fin'] = Post::get('fin_Year')."-".Post::get('fin_Month')."-".Post::get('fin_Day')." ".Post::get('fin_Hour').":".Post::get('fin_Minute').":00";
    $evt['membres_only'] = Post::get('membres_only');
    $evt['advertise'] = Post::get('advertise');
    $evt['show_participants'] = Post::get('show_participants');
    if (!$short_name) $short_name = '';
    $evt['short_name'] = $short_name;
    $evt['deadline_inscription'] = (Post::get('deadline', 'off')=='on')?null:(Post::get('inscr_Year')."-".Post::get('inscr_Month')."-".Post::get('inscr_Day'));

    // Store the modifications in the database
    $globals->xdb->execute("REPLACE INTO groupex.evenements 
        SET eid={?}, asso_id={?}, organisateur_uid={?}, intitule={?},
            paiement_id = {?}, descriptif = {?},
            debut = {?}, fin = {?},
            membres_only = {?}, advertise = {?}, show_participants = {?}, 
            short_name = {?}, deadline_inscription = {?}",
            $evt['eid'], $evt['asso_id'], $evt['organisateur_uid'], $evt['intitule']
            , $evt['paiement_id'], $evt['descriptif'],
            $evt['debut'], $evt['fin'],
            $evt['membres_only'], $evt['advertise'], $evt['show_participants'],
            $evt['short_name'], $evt['deadline_inscription']);

    // if new event, get its id
    if (!$eid) {
        $res = $globals->xdb->query("SELECT LAST_INSERT_ID()");
        $eid = $res->fetchOneCell();
        $evt['eid'] = $eid;
    }

    $nb_moments = 0;
    $money_defaut = 0;
    foreach ($moments as $i) 
    {
        if (Post::get('titre'.$i)) {
            $nb_moments++;
            if (!($money_defaut > 0))
                $money_defaut = strtr(Post::get('montant'.$i), ',', '.');
            $globals->xdb->execute("
                REPLACE INTO groupex.evenements_items VALUES (
                    {?}, {?},
                    {?}, {?}, {?})",
                    $eid, $i,
                    Post::get('titre'.$i), Post::get('details'.$i), strtr(Post::get('montant'.$i), ',', '.'));
        }
        else
        {
            $globals->xdb->execute("DELETE FROM groupex.evenements_items WHERE eid = {?} AND item_id = {?}", $eid, $i);
        }
    }

    // request for a new payment
    if (Post::get('paiement_id') == -1 && $money_defaut >= 0) {
        require_once ('validations.inc.php');
        $p = new PayReq(
        Session::get('uid'),
        Post::get('intitule')." - ".$globals->asso('nom'),
        Post::get('site'),
        $money_defaut,
        Post::get('confirmation'),
        0,
        999,
        $globals->asso('id'),
        $eid);
        $p->submit();
    }

    // events with no sub-event: add a sub-event with no name
    if ($nb_moments == 0)
        $globals->xdb->execute("INSERT INTO groupex.evenements_items VALUES ({?}, {?}, '', '', 0)", $eid, 1);
}

if (Env::has('sup') && $eid) {
    // deletes the event
    $globals->xdb->execute("DELETE FROM groupex.evenements WHERE eid = {?} AND asso_id = {?}", $eid, $globals->asso('id'));
    // deletes the event items
    $globals->xdb->execute("DELETE FROM groupex.evenements_items WHERE eid = {?}", $eid);
    // deletes the event participants
    $globals->xdb->execute("DELETE FROM groupex.evenements_participants WHERE eid = {?}", $eid);
    // deletes the event mailing aliases
    if ($infos['short_name'])
            $globals->xdb->execute("DELETE FROM virtual WHERE type = 'evt' AND alias LIKE {?}", $infos['short_name']."-%");
    // delete the requests for payments
    require_once('validations.inc.php');
    $globals->xdb->execute("DELETE FROM requests WHERE type = 'paiements' AND data  LIKE {?}", PayReq::same_event($eid, $globals->asso('id')));
    redirect("evenements.php");
}

if (!$get_form)
    redirect("evenements.php");

// get a list of all the payment for this asso
$res = $globals->xdb->iterator
        ("SELECT id, text FROM {$globals->money->mpay_tprefix}paiements WHERE asso_id = {?}", $globals->asso('id'));
$paiements = array();
while ($a = $res->next()) $paiements[$a['id']] = $a['text'];
    $page->assign('paiements', $paiements);

// when modifying an old event retreive the old datas
if ($eid) {
    $res = $globals->xdb->query(
            "SELECT	eid, intitule, descriptif, debut, fin, membres_only, advertise, show_participants, paiement_id, short_name, deadline_inscription
               FROM	groupex.evenements
              WHERE eid = {?}", $eid);
    $evt = $res->fetchOneAssoc();
    // find out if there is already a request for a payment for this event
    require_once('validations.inc.php');
    $res = $globals->xdb->query("SELECT stamp FROM requests WHERE type = 'paiements' AND data LIKE {?}", PayReq::same_event($eid, $globals->asso('id')));
    $stamp = $res->fetchOneCell();
    if ($stamp) {
            $evt['paiement_id'] = -2;
            $evt['paiement_req'] = $stamp;
    }
    $page->assign('evt', $evt);
    // get all the different moments infos
    $res = $globals->xdb->iterator(
            "SELECT item_id, titre, details, montant
               FROM groupex.evenements_items AS ei
         INNER JOIN groupex.evenements AS e ON(e.eid = ei.eid)
              WHERE e.eid = {?}
           ORDER BY item_id", $eid);
    $items = array();
    while ($item = $res->next()) $items[$item['item_id']] = $item;
    $page->assign('items', $items);
}

$page->run();

?>
