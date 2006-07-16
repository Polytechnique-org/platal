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

define('NB_PER_PAGE', 25);

class XnetEventsModule extends PLModule
{
    function handlers()
    {
        return array(
            'grp/events'       => $this->make_hook('events',  AUTH_MDP),
            'grp/events/sub'   => $this->make_hook('sub',     AUTH_MDP),
            'grp/events/csv'   => $this->make_hook('csv',     AUTH_MDP),
            'grp/events/edit'  => $this->make_hook('edit',    AUTH_MDP),
            'grp/events/admin' => $this->make_hook('admin',   AUTH_MDP),
        );
    }

    function handler_events(&$page)
    {
        global $globals;

        new_group_page('xnetevents/index.tpl');

        if (Post::has('del')) {
            if (!may_update()) {
                return PL_NOT_ALLOWED;
            }

            $eid = Post::get('del');

            $res = $globals->xdb->query("SELECT asso_id, short_name FROM groupex.evenements
                                        WHERE eid = {?} AND asso_id = {?}",
                                        $eid, $globals->asso('id'));

            $tmp = $res->fetchOneRow();
            if (!$tmp) {
                return PL_NOT_ALLOWED;
            }

            // deletes the event mailing aliases
            if ($tmp[1]) {
                $globals->xdb->execute(
                    "DELETE FROM virtual WHERE type = 'evt' AND alias = {?}",
                    $tmp[1].'-absents');
                $globals->xdb->execute(
                    "DELETE FROM virtual WHERE type = 'evt' AND alias = {?}",
                    $tmp[1].'-participants');
            }

            // deletes the event items
            $globals->xdb->execute("DELETE FROM groupex.evenements_items WHERE eid = {?}", $eid);

            // deletes the event participants
            $globals->xdb->execute("DELETE FROM groupex.evenements_participants
                                    WHERE eid = {?}", $eid);

            // deletes the event
            $globals->xdb->execute("DELETE FROM groupex.evenements
                                    WHERE eid = {?} AND asso_id = {?}",
                                   $eid, $globals->asso('id'));

            // delete the requests for payments
            require_once 'validations.inc.php';
            $globals->xdb->execute("DELETE FROM requests
                                    WHERE type = 'paiements' AND data LIKE {?}",
                                   PayReq::same_event($eid, $globals->asso('id')));
        }

        $page->assign('admin', may_update());

        $evenements = $globals->xdb->iterator(
                "SELECT  e.*, LEFT(10, e.debut) AS debut_day, LEFT(10, e.fin) AS fin_day,
                         IF(e.deadline_inscription, e.deadline_inscription >= LEFT(NOW(), 10),
                            1) AS inscr_open, e.deadline_inscription,
                         u.nom, u.prenom, u.promo, a.alias,
                         MAX(ep.nb) AS inscrit, MAX(ep.paid) AS paid
                  FROM  groupex.evenements  AS e
            INNER JOIN  x4dat.auth_user_md5 AS u ON u.user_id = e.organisateur_uid
            INNER JOIN  x4dat.aliases       AS a ON (a.type = 'a_vie' AND a.id = u.user_id)
             LEFT JOIN  groupex.evenements_participants AS ep ON (ep.eid = e.eid AND ep.uid = {?})
                 WHERE  asso_id = {?}
              GROUP BY  e.eid
              ORDER BY  debut", Session::get('uid'), $globals->asso('id'));

        $evts = array();

        while ($e = $evenements->next()) {
            $res = $globals->xdb->query(
                "SELECT titre, details, montant, ei.item_id, nb
                   FROM groupex.evenements_items AS ei
              LEFT JOIN groupex.evenements_participants AS ep
                        ON (ep.eid = ei.eid AND ep.item_id = ei.item_id AND uid = {?})
                  WHERE ei.eid = {?}",
                    Session::get('uid'), $e['eid']);
            $e['moments'] = $res->fetchAllAssoc();

            $e['topay'] = 0;
            foreach ($e['moments'] as $m) {
                $e['topay'] += $m['nb'] * $m['montant'];
            }

            $query = $globals->xdb->query(
                "SELECT montant
                   FROM {$globals->money->mpay_tprefix}transactions AS t
                 WHERE ref = {?} AND uid = {?}", $e['paiement_id'], Session::get('uid'));
            $montants = $query->fetchColumn();

            foreach ($montants as $m) {
                $p = strtr(substr($m, 0, strpos($m, 'EUR')), ',', '.');
                $e['paid'] += trim($p);
            }

            $evts[] = $e;
        }

        $page->assign('evenements', $evts);
        $page->assign('is_member', is_member());
    }

    function handler_sub(&$page, $eid = null)
    {
        global $globals;

        require_once dirname(__FILE__).'/xnetevents/xnetevents.inc.php';

        new_group_page('xnetevents/subscribe.tpl');

        $evt = get_event_detail($eid);
        if (!$evt) {
            return PL_NOT_FOUND;
        }

        if (!$evt['inscr_open']) {
            $page->kill('Les inscriptions pour cet événement sont closes');
        }

        $page->assign('event', $evt);

        if (!Post::has('submit')) {
            return;
        }

        $moments = Post::getMixed('moment',    array());
        $pers    = Post::getMixed('personnes', array());
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
        if (array_sum($subs) && $evt['paid'] != 0) {
            $page->trig("Impossible de te désinscrire complètement ".
                        "parce que tu as fait un paiement par ".
                        "chèque ou par liquide. Contacte un ".
                        "administrateur du groupe si tu es sûr de ".
                        "ne pas venir");
            return;
        }

        // update actual inscriptions
        foreach ($subs as $j => $nb) {
            if ($nb > 0) {
                $globals->xdb->execute(
                    "REPLACE INTO  groupex.evenements_participants
                           VALUES  ({?}, {?}, {?}, {?}, {?})",
                    $eid, Session::getInt('uid'), $j, $nb, $evt['paid']);
            } else {
                $globals->xdb->execute(
                    "DELETE FROM  groupex.evenements_participants
                           WHERE  eid = {?} AND uid = {?} AND item_id = {?}",
                    $eid, Session::getInt("uid"), $j);		
            }
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

        header('Content-type: text/x-csv; encoding=iso-8859-1');
        header('Pragma: ');
        header('Cache-Control: ');

        new_nonhtml_page('xnetevents/csv.tpl');

        $admin = may_update();

        $tri = (Env::get('order') == 'alpha' ? 'promo, nom, prenom' : 'nom, prenom, promo');

        $page->assign('participants',
                      get_event_participants($evt, $item_id, $tri));

        $page->assign('admin', $admin);
        $page->assign('moments', $evt['moments']);
        $page->assign('money', $evt['money']);
        $page->assign('tout', !Env::get('item_id', false));
    }

    function handler_edit(&$page, $eid = null)
    {
        global $globals;

        new_groupadmin_page('xnetevents/edit.tpl');

        $page->assign('logged', logged());
        $page->assign('admin', may_update());

        $moments = range(1, 4);
        $page->assign('moments', $moments);

        if (!is_null($eid)) {
            $res = $globals->xdb->query("SELECT short_name, asso_id
                                           FROM groupex.evenements
                                          WHERE eid = {?}", $eid);
            $infos = $res->fetchOneAssoc();
            if ($infos['asso_id'] != $globals->asso('id')) {
                return PL_NOT_ALLOWED;
            }
        }

        $get_form = true;

        if (Post::get('intitule')) {
            $get_form = false;
            $short_name = Env::get('short_name');

            // Quelques vérifications sur l'alias (caractères spéciaux)
            if ($short_name && !preg_match( "/^[a-zA-Z0-9\-.]{3,20}$/", $short_name)) {
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
                $globals->xdb->execute("UPDATE virtual
                                           SET alias = REPLACE(alias, {?}, {?})
                                         WHERE type = 'evt' AND alias LIKE {?}",
                                         $infos['short_name'], $short_name,
                                         $infos['short_name']."-%");
            }
            elseif ($short_name && !$infos['short_name']) {
                // if we have a first new short_name create the lists
                //
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
            elseif (!$short_name && $infos['short_name']) {
                // if we delete the old short name, delete the lists
                $globals->xdb->execute("DELETE virtual, virtual_redirect FROM virtual
                                     LEFT JOIN virtual_redirect USING(vid)
                                         WHERE virtual.alias LIKE {?}",
                                       $infos['short_name']."-%");
            }

            $evt = array();
            $evt['eid']          = $eid;
            $evt['asso_id']      = $globals->asso('id');
            $evt['organisateur_uid'] = Session::get('uid');
            $evt['intitule']     = Post::get('intitule');
            $evt['paiement_id']  = (Post::get('paiement_id')>0) ? Post::get('paiement_id') : null;
            $evt['descriptif']   = Post::get('descriptif');
            $evt['debut']        = Post::get('deb_Year')."-".Post::get('deb_Month')
                                 . "-".Post::get('deb_Day')." ".Post::get('deb_Hour')
                                 . ":".Post::get('deb_Minute').":00";
            $evt['fin']          = Post::get('fin_Year')."-".Post::get('fin_Month')
                                 . "-".Post::get('fin_Day')." ".Post::get('fin_Hour')
                                 . ":".Post::get('fin_Minute').":00";
            $evt['show_participants'] = Post::get('show_participants');
            $evt['noinvite']     = Post::get('noinvite');
            if (!$short_name) {
                $short_name = '';
            }
            $evt['short_name']   = $short_name;
            $evt['deadline_inscription'] = null;
            if (Post::get('deadline')) {
                $evt['deadline_inscription'] = Post::get('inscr_Year')."-"
                                             . Post::get('inscr_Month')."-"
                                             . Post::get('inscr_Day');
            }

            // Store the modifications in the database
            $globals->xdb->execute("REPLACE INTO groupex.evenements
                SET eid={?}, asso_id={?}, organisateur_uid={?}, intitule={?},
                    paiement_id = {?}, descriptif = {?},
                    debut = {?}, fin = {?}, show_participants = {?},
                    short_name = {?}, deadline_inscription = {?}, noinvite = {?}",
                    $evt['eid'], $evt['asso_id'], $evt['organisateur_uid'], $evt['intitule']
                    , $evt['paiement_id'], $evt['descriptif'],
                    $evt['debut'], $evt['fin'],
                    $evt['show_participants'],
                    $evt['short_name'], $evt['deadline_inscription'], $evt['noinvite']);

            // if new event, get its id
            if (!$eid) {
                $res = $globals->xdb->query("SELECT LAST_INSERT_ID()");
                $eid = $res->fetchOneCell();
                $evt['eid'] = $eid;
            }

            $nb_moments = 0;
            $money_defaut = 0;

            foreach ($moments as $i) {
                if (Post::get('titre'.$i)) {
                    $nb_moments++;
                    if (!($money_defaut > 0))
                        $money_defaut = strtr(Post::get('montant'.$i), ',', '.');
                    $globals->xdb->execute("
                        REPLACE INTO groupex.evenements_items
                        VALUES ({?}, {?}, {?}, {?}, {?})",
                        $eid, $i, Post::get('titre'.$i),
                        Post::get('details'.$i),
                        strtr(Post::get('montant'.$i), ',', '.'));
                } else {
                    $globals->xdb->execute("DELETE FROM groupex.evenements_items 
                                            WHERE eid = {?} AND item_id = {?}", $eid, $i);
                }
            }

            // request for a new payment
            if (Post::get('paiement_id') == -1 && $money_defaut >= 0) {
                require_once 'validations.inc.php';
                $p = new PayReq(Session::get('uid'),
                                Post::get('intitule')." - ".$globals->asso('nom'),
                                Post::get('site'), $money_defaut,
                                Post::get('confirmation'), 0, 999,
                                $globals->asso('id'), $eid);
                $p->submit();
            }

            // events with no sub-event: add a sub-event with no name
            if ($nb_moments == 0) {
                $globals->xdb->execute("INSERT INTO groupex.evenements_items
                                        VALUES ({?}, {?}, '', '', 0)", $eid, 1);
            }
        }

        if (!$get_form) {
            redirect("evenements.php");
        }

        // get a list of all the payment for this asso
        $res = $globals->xdb->iterator("SELECT id, text
                                        FROM {$globals->money->mpay_tprefix}paiements
                                        WHERE asso_id = {?}", $globals->asso('id'));
        $paiements = array();
        while ($a = $res->next()) $paiements[$a['id']] = $a['text']; {
            $page->assign('paiements', $paiements);
        }

        // when modifying an old event retreive the old datas
        if ($eid) {
            $res = $globals->xdb->query(
                    "SELECT	eid, intitule, descriptif, debut, fin,
                                show_participants, paiement_id, short_name,
                                deadline_inscription, noinvite
                       FROM	groupex.evenements
                      WHERE eid = {?}", $eid);
            $evt = $res->fetchOneAssoc();
            // find out if there is already a request for a payment for this event
            require_once 'validations.inc.php';
            $res = $globals->xdb->query("SELECT stamp FROM requests
                                         WHERE type = 'paiements' AND data LIKE {?}",
                                        PayReq::same_event($eid, $globals->asso('id')));
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
            while ($item = $res->next()) {
                $items[$item['item_id']] = $item;
            }
            $page->assign('items', $items);
        }
    }

    function handler_admin(&$page, $eid = null, $item_id = null)
    {
        global $globals;

        require_once dirname(__FILE__).'/xnetevents/xnetevents.inc.php';

        $evt = get_event_detail($eid, $item_id);
        if (!$evt) {
            return PL_NOT_FOUND;
        }

        if ($evt['show_participants']) {
            new_group_page('xnetevents/admin.tpl');
        } else {
            new_groupadmin_page('xnetevents/admin.tpl');
        }

        if (may_update() && Post::get('adm')) {
            $member = get_infos(Post::get('mail'));
            if (!$member) {
                $page->trig("Membre introuvable");
            }

            // change the price paid by a participant
            if (Env::get('adm') == 'prix' && $member) {
                $globals->xdb->execute("UPDATE groupex.evenements_participants
                                           SET paid = IF(paid + {?} > 0, paid + {?}, 0)
                                         WHERE uid = {?} AND eid = {?}",
                        strtr(Env::get('montant'), ',', '.'),
                        strtr(Env::get('montant'), ',', '.'),
                        $member['uid'], $eid);
            }

            // change the number of personns coming with a participant
            if (Env::get('adm') == 'nbs' && $member) {
                $res = $globals->xdb->query("SELECT paid
                                               FROM groupex.evenements_participants
                                              WHERE uid = {?} AND eid = {?}",
                                            $member['uid'], $eid);

                $paid = intval($res->fetchOneCell());
                $nbs  = Post::getMixed('nb', array());

                foreach ($nbs as $id => $nb) {
                    $nb = intval($nb);

                    if ($nb < 0) {
                        $nb = 0;
                    }

                    if ($nb) {
                        $globals->xdb->execute("REPLACE INTO groupex.evenements_participants
                                               VALUES ({?}, {?}, {?}, {?}, {?})",
                                               $eid, $member['uid'], $id, $nb, $paid);
                    } else {
                        $globals->xdb->execute("DELETE FROM groupex.evenements_participants
                                               WHERE uid = {?} AND eid = {?} AND item_id = {?}",
                                               $member['uid'], $eid, $id);
                    }
                }

                $res = $globals->xdb->query("SELECT uid FROM groupex.evenements_participants
                                            WHERE uid = {?} AND eid = {?}",
                                            $member['uid'], $eid);
                $u = $res->fetchOneCell();
                subscribe_lists_event($u, $member['uid'], $evt);
            }

            $evt = get_event_detail($eid, $item_id);
        }

        $page->assign('admin', may_update());
        $page->assign('evt', $evt);
        $page->assign('tout', is_null($item_id));

        if (count($evt['moments'])) {
            $page->assign('moments', $evt['moments']);
        }

        $tri = (Env::get('order') == 'alpha' ? 'promo, nom, prenom' : 'nom, prenom, promo');
        $whereitemid = is_null($item_id) ? '' : "AND ep.item_id = $item_id";
        $res = $globals->xdb->iterRow(
                    'SELECT  UPPER(SUBSTRING(IF(u.nom IS NULL, m.nom,
                                                IF(u.nom_usage<>"", u.nom_usage, u.nom)), 1, 1)),
                             COUNT(DISTINCT ep.uid)
                       FROM  groupex.evenements_participants AS ep
                 INNER JOIN  groupex.evenements AS e ON (ep.eid = e.eid)
                  LEFT JOIN  groupex.membres AS m ON ( ep.uid = m.uid AND e.asso_id = m.asso_id)
                  LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = ep.uid )
                      WHERE  ep.eid = {?} '.$whereitemid.'
                   GROUP BY  UPPER(SUBSTRING(IF(u.nom IS NULL,m.nom,u.nom), 1, 1))', $eid);

        $alphabet = array();
        $nb_tot = 0;
        while (list($char, $nb) = $res->next()) {
            $alphabet[ord($char)] = $char;
            $nb_tot += $nb;
            if (Env::has('initiale') && $char == strtoupper(Env::get('initiale'))) {
                $tot = $nb;
            }
        }
        ksort($alphabet);
        $page->assign('alphabet', $alphabet);

        $ofs   = Env::getInt('offset');
        $tot   = Env::get('initiale') ? $tot : $nb_tot;
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

        if ($evt['paiement_id']) {
            $res = $globals->xdb->iterator(
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

        $page->assign('participants', 
                      get_event_participants($evt, $item_id, $tri,
                                             "LIMIT ".($ofs*NB_PER_PAGE).", ".NB_PER_PAGE));
    }
}

?>
