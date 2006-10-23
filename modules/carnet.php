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

class CarnetModule extends PLModule
{
    function handlers()
    {
        return array(
            'carnet'                => $this->make_hook('index',    AUTH_COOKIE),
            'carnet/panel'          => $this->make_hook('panel',    AUTH_COOKIE),
            'carnet/notifs'         => $this->make_hook('notifs',   AUTH_COOKIE),

            'carnet/contacts'       => $this->make_hook('contacts', AUTH_COOKIE),
            'carnet/contacts/pdf'   => $this->make_hook('pdf',      AUTH_COOKIE),
            'carnet/contacts/ical'  => $this->make_hook('ical',     AUTH_COOKIE),
            'carnet/contacts/vcard' => $this->make_hook('vcard',    AUTH_COOKIE),

            'carnet/rss'            => $this->make_hook('rss',      AUTH_PUBLIC),
        );
    }

    function on_subscribe($forlife, $uid, $promo, $password)
    {
        require_once 'notifs.inc.php';
        register_watch_op($uid, WATCH_INSCR);
        inscription_notifs_base($uid);
    }

    function _add_rss_link(&$page)
    {
        if (!S::has('core_rss_hash')) {
            return;
        }
        $page->assign('xorg_rss',
                      array('title' => 'Polytechnique.org :: Carnet',
                            'href'  => '/carnet/rss/'.S::v('forlife')
                                      .'/'.S::v('core_rss_hash').'/rss.xml')
                      );
    }

    function handler_index(&$page)
    {
        $page->changeTpl('carnet/index.tpl');
        $page->assign('xorg_title','Polytechnique.org - Mon carnet');
        $this->_add_rss_link($page);
    }

    function handler_panel(&$page)
    {
        $page->changeTpl('carnet/panel.tpl');

        if (Get::has('read')) {
            $_SESSION['watch_last'] = Get::v('read');
            pl_redirect('carnet/panel');
        }

        require_once 'notifs.inc.php';

        $page->assign('now',date('YmdHis'));
        $notifs = new Notifs(S::v('uid'), true);

        $page->assign('notifs', $notifs);
        $page->assign('today', date('Y-m-d'));
        $this->_add_rss_link($page);
    }

    function _handler_notifs_promos(&$page, &$watch, $action, $arg)
    {
        if(preg_match('!^ *(\d{4}) *$!', $arg, $matches)) {
            $p = intval($matches[1]);
            if($p<1900 || $p>2100) {
                $page->trig("la promo entrée est invalide");
            } else {
                if ($action == 'add_promo') {
                    $watch->_promos->add($p);
                } else {
                    $watch->_promos->del($p);
                }
            }
        } elseif (preg_match('!^ *(\d{4}) *- *(\d{4}) *$!', $arg, $matches)) {
            $p1 = intval($matches[1]);
            $p2 = intval($matches[2]);
            if($p1<1900 || $p1>2100) {
                $page->trig('la première promo de la plage entrée est invalide');
            } elseif($p2<1900 || $p2>2100) {
                $page->trig('la seconde promo de la plage entrée est invalide');
            } else {
                if ($action == 'add_promo') {
                    $watch->_promos->addRange($p1, $p2);
                } else {
                    $watch->_promos->delRange($p1, $p2);
                }
            }
        } else {
            $page->trig("La promo (ou la plage de promo) entrée est dans un format incorrect.");
        }
    }

    function handler_notifs(&$page, $action = null, $arg = null)
    {
        $page->changeTpl('carnet/notifs.tpl');

        require_once 'notifs.inc.php';

        $watch = new Watch(S::v('uid'));

        $res = XDB::query("SELECT promo_sortie
                                       FROM auth_user_md5
                                      WHERE user_id = {?}",
                                    S::v('uid', -1));
        $promo_sortie = $res->fetchOneCell();
        $page->assign('promo_sortie', $promo_sortie);

        switch ($action) {
          case 'add_promo':
          case 'del_promo':
            $this->_handler_notifs_promos($page, $watch, $action, $arg);
            break;

          case 'del_nonins':
            $watch->_nonins->del($arg);
            break;

          case 'add_nonins':
            $watch->_nonins->add($arg);
            break;
        }

        if (Env::has('subs'))       $watch->_subs->update('sub');
        if (Env::has('flags_contacts')) {
            $watch->watch_contacts = Env::b('contacts');
            $watch->saveFlags();
        }
        if (Env::has('flags_mail')) {
            $watch->watch_mail     = Env::b('mail');
            $watch->saveFlags();
        }

        $page->assign_by_ref('watch', $watch);
    }

    function _get_list($offset, $limit) {
        $uid   = S::v('uid');
        $res   = XDB::query("SELECT COUNT(*) FROM contacts WHERE uid = {?}", $uid);
        $total = $res->fetchOneCell();

        $order = Get::v('order');
        $orders = Array(
            'nom'     => 'nom DESC, u.prenom, u.promo',
            'promo'   => 'promo DESC, nom, u.prenom',
            'last'    => 'u.date DESC, nom, u.prenom, promo');
        if ($order != 'promo' && $order != 'last')
            $order = 'nom';
        $order = $orders[$order];
        if (Get::v('inv') == '')
            $order = str_replace(" DESC,", ",", $order);

        $res   = XDB::query("
                SELECT  u.prenom, IF(u.nom_usage='',u.nom,u.nom_usage) AS nom, a.alias AS forlife, u.promo
                  FROM  contacts       AS c
            INNER JOIN  auth_user_md5  AS u   ON (u.user_id = c.contact)
            INNER JOIN  aliases        AS a   ON (u.user_id = a.id AND a.type='a_vie')
                 WHERE  c.uid = {?}
              ORDER BY  $order
                 LIMIT  {?}, {?}", $uid, $offset*$limit, $limit);
        $list  = $res->fetchAllAssoc();

        return Array($total, $list);
    }

    function handler_contacts(&$page, $action = null)
    {
        $page->changeTpl('carnet/mescontacts.tpl');
        require_once("applis.func.inc.php");
        $page->assign('xorg_title','Polytechnique.org - Mes contacts');

        $uid  = S::v('uid');
        $user = Env::v('user');

        switch (Env::v('action')) {
            case 'retirer':
                if (is_numeric($user)) {
                    if (XDB::execute('DELETE FROM contacts
                                                      WHERE uid = {?} AND contact = {?}',
                                               $uid, $user))
                    {
                        $page->trig("Contact retiré !");
                    }
                } else {
                    if (XDB::execute(
                                'DELETE FROM  contacts
                                       USING  contacts AS c
                                  INNER JOIN  aliases  AS a ON (c.contact=a.id and a.type!="homonyme")
                                       WHERE  c.uid = {?} AND a.alias={?}', $uid, $user))
                    {
                        $page->trig("Contact retiré !");
                    }
                }
                break;

            case 'ajouter':
                require_once('user.func.inc.php');
                if (($login = get_user_login($user)) !== false) {
                    if (XDB::execute(
                                'INSERT INTO  contacts (uid, contact)
                                      SELECT  {?}, id
                                        FROM  aliases
                                       WHERE  alias = {?}', $uid, $login))
                    {
                        $page->trig('Contact ajouté !');
                    } else {
                        $page->trig('Contact déjà dans la liste !');
                    }
                }
        }

        if ($action == 'trombi') {
            $trombi = new Trombi(array($this, '_get_list'));
            $trombi->setNbRows(4);
            $page->assign_by_ref('trombi',$trombi);

            $order = Get::v('order');
            if ($order != 'promo' && $order != 'last')
                $order = 'nom';
            $page->assign('order', $order);
            $page->assign('inv', Get::v('inv'));

        } else {

            $order = Get::v('order');
            $orders = Array(
                'nom'     => 'sortkey DESC, a.prenom, a.promo',
                'promo'   => 'promo DESC, sortkey, a.prenom',
                'last'    => 'a.date DESC, sortkey, a.prenom, promo');
            if ($order != 'promo' && $order != 'last')
                $order = 'nom';
            $page->assign('order', $order);
            $page->assign('inv', Get::v('inv'));
            $order = $orders[$order];
            if (Get::v('inv') == '')
                $order = str_replace(" DESC,", ",", $order);

            $sql = "SELECT  contact AS id,
                            a.*, l.alias AS forlife,
                            1 AS inscrit,
                            a.perms != 'pending' AS wasinscrit,
                            a.deces != 0 AS dcd, a.deces, a.matricule_ax,
                            FIND_IN_SET('femme', a.flags) AS sexe,
                            e.entreprise, es.label AS secteur, ef.fonction_fr AS fonction,
                            IF(n.nat='',n.pays,n.nat) AS nat, n.a2 AS iso3166,
                            ad0.text AS app0text, ad0.url AS app0url, ai0.type AS app0type,
                            ad1.text AS app1text, ad1.url AS app1url, ai1.type AS app1type,
                            adr.city, gp.a2, gp.pays AS countrytxt, gr.name AS region,
                            IF(a.nom_usage<>'',a.nom_usage,a.nom) AS sortkey,
                            COUNT(em.email) > 0 AS actif
                      FROM  contacts       AS c
                INNER JOIN  auth_user_md5  AS a   ON (a.user_id = c.contact)
                INNER JOIN  aliases        AS l   ON (a.user_id = l.id AND l.type='a_vie')
                 LEFT JOIN  entreprises    AS e   ON (e.entrid = 0 AND e.uid = a.user_id)
                 LEFT JOIN  emploi_secteur AS es  ON (e.secteur = es.id)
                 LEFT JOIN  fonctions_def  AS ef  ON (e.fonction = ef.id)
                 LEFT JOIN  geoloc_pays    AS n   ON (a.nationalite = n.a2)
                 LEFT JOIN  applis_ins     AS ai0 ON (a.user_id = ai0.uid AND ai0.ordre = 0)
                 LEFT JOIN  applis_def     AS ad0 ON (ad0.id = ai0.aid)
                 LEFT JOIN  applis_ins     AS ai1 ON (a.user_id = ai1.uid AND ai1.ordre = 1)
                 LEFT JOIN  applis_def     AS ad1 ON (ad1.id = ai1.aid)
                 LEFT JOIN  adresses       AS adr ON (a.user_id = adr.uid
                                                      AND FIND_IN_SET('active', adr.statut))
                 LEFT JOIN  geoloc_pays    AS gp  ON (adr.country = gp.a2)
                 LEFT JOIN  geoloc_region  AS gr  ON (adr.country = gr.a2 AND adr.region = gr.region)
                 LEFT JOIN  emails         AS em  ON (em.uid = a.user_id AND em.flags = 'active')
                     WHERE  c.uid = $uid
                  GROUP BY  a.user_id
                  ORDER BY  ".$order;

            $page->assign('citer', XDB::iterator($sql));
        }
    }

    function handler_pdf(&$page, $arg0 = null, $arg1 = null)
    {
        require_once dirname(__FILE__).'/carnet/contacts.pdf.inc.php';
        require_once 'user.func.inc.php';

        session_write_close();

        $sql = "SELECT  a.alias
                  FROM  aliases       AS a
            INNER JOIN  auth_user_md5 AS u ON ( a.id = u.user_id )
            INNER JOIN  contacts      AS c ON ( a.id = c.contact )
                 WHERE  c.uid = {?} AND a.type='a_vie'";
        if ($arg0 == 'promo') {
            $sql .= ' ORDER BY  u.promo, u.nom, u.prenom';
        } else {
            $sql .= ' ORDER BY  u.nom, u.prenom, u.promo';
        }

        $citer = XDB::iterRow($sql, S::v('uid'));
        $pdf   = new ContactsPDF();

        while (list($alias) = $citer->next()) {
            $user = get_user_details($alias);
            $pdf = ContactsPDF::addContact($pdf, $user, $arg0 == 'photos' || $arg1 == 'photos');
        }
        $pdf->Output();

        exit;
    }

    function handler_rss(&$page, $user = null, $hash = null)
    {
        require_once 'rss.inc.php';
        require_once 'notifs.inc.php';

        $uid    = init_rss('carnet/rss.tpl', $user, $hash);
        $notifs = new Notifs($uid, false);
        $page->assign('notifs', $notifs);
    }

    function handler_ical(&$page)
    {
        require_once dirname(__FILE__).'/carnet/smarty.php';
        $page->changeTpl('carnet/calendar.tpl', NO_SKIN);
        $page->register_function('display_ical', 'display_ical');

        $res = XDB::iterRow(
                'SELECT u.prenom,
                        IF(u.nom_usage = \'\',u.nom,u.nom_usage) AS nom,
                        u.promo,
                        u.naissance,
                        DATE_ADD(u.naissance, INTERVAL 1 DAY) AS end,
                        u.date_ins,
                        a.alias AS forlife
                   FROM contacts      AS c
             INNER JOIN auth_user_md5 AS u ON (u.user_id = c.contact)
             INNER JOIN aliases       AS a ON (u.user_id = a.id AND a.type = \'a_vie\')
                  WHERE c.uid = {?}', S::v('uid'));

        $annivs = Array();
        while (list($prenom, $nom, $promo, $naissance, $end, $ts, $forlife) = $res->next()) {
            $naissance = str_replace('-', '', $naissance);
            $end       = str_replace('-', '', $end);
            $annivs[] = array(
                'timestamp' => strtotime($ts),
                'date'      => $naissance,
                'tomorrow'  => $end,
                'forlife'   => $forlife,
                'summary'   => 'Anniversaire de '.$prenom
                                .' '.$nom.' - x '.$promo,
            );
        }
        $page->assign('events', $annivs);

        header('Content-Type: text/calendar; charset=utf-8');
    }

    function handler_vcard(&$page, $photos = null)
    {
        $res = XDB::query('SELECT contact
                             FROM contacts
                            WHERE uid = {?}', S::v('uid'));

        require_once('vcard.inc.php');
        $vcard = new VCard($res->fetchColumn(), $photos == 'photos');
        $vcard->do_page(&$page);
    }
}

?>
