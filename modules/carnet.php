<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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
            'carnet/contacts/pdf'   => $this->make_hook('pdf',      AUTH_COOKIE, 'user', NO_HTTPS),
            'carnet/contacts/ical'  => $this->make_hook('ical',     AUTH_PUBLIC, 'user', NO_HTTPS),
            'carnet/contacts/vcard' => $this->make_hook('vcard',    AUTH_COOKIE, 'user', NO_HTTPS),

            'carnet/rss'            => $this->make_hook('rss',      AUTH_PUBLIC, 'user', NO_HTTPS),
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
        $page->setRssLink('Polytechnique.org :: Carnet',
                          '/carnet/rss/'.S::v('forlife') .'/'.S::v('core_rss_hash').'/rss.xml');
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
            update_NbNotifs();
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
                $page->trigError("la promo entrée est invalide");
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
                $page->trigError('la première promo de la plage entrée est invalide');
            } elseif($p2<1900 || $p2>2100) {
                $page->trigError('la seconde promo de la plage entrée est invalide');
            } else {
                if ($action == 'add_promo') {
                    $watch->_promos->addRange($p1, $p2);
                } else {
                    $watch->_promos->delRange($p1, $p2);
                }
            }
        } else {
            $page->trigError("La promo (ou la plage de promo) entrée est dans un format incorrect.");
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

        if ($action) {
            S::assert_xsrf_token();
        }
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

        if (Env::has('subs')) {
            S::assert_xsrf_token();
            $watch->_subs->update('sub');
        }

        if (Env::has('flags_contacts')) {
            S::assert_xsrf_token();
            $watch->watch_contacts = Env::b('contacts');
            $watch->saveFlags();
        }

        if (Env::has('flags_mail')) {
            S::assert_xsrf_token();
            $watch->watch_mail = Env::b('mail');
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

    function searchErrorHandler($explain) {
        global $page;
        $page->trigError($explain);
        $this->handler_contacts($page);
    }

    function handler_contacts(&$page, $action = null, $subaction = null, $ssaction = null)
    {
        $page->assign('xorg_title','Polytechnique.org - Mes contacts');
        $this->_add_rss_link($page);

        $uid  = S::v('uid');
        $user = Env::v('user');

        // For XSRF protection, checks both the normal xsrf token, and the special RSS token.
        // It allows direct linking to contact adding in the RSS feed.
        if (Env::v('action') && Env::v('token') !== S::v('core_rss_hash')) {
            S::assert_xsrf_token();
        }
        switch (Env::v('action')) {
            case 'retirer':
                if (is_numeric($user)) {
                    if (XDB::execute('DELETE FROM contacts
                                       WHERE uid = {?} AND contact = {?}',
                                     $uid, $user))
                    {
                        $page->trigSuccess("Contact retiré !");
                    }
                } else {
                    if (XDB::execute(
                                'DELETE FROM  c
                                       USING  contacts AS c
                                  INNER JOIN  aliases  AS a ON (c.contact=a.id and a.type!="homonyme")
                                       WHERE  c.uid = {?} AND a.alias={?}', $uid, $user))
                    {
                        $page->trigSuccess("Contact retiré !");
                    }
                }
                break;

            case 'ajouter':
                require_once('user.func.inc.php');
                if (($login = get_user_login($user)) !== false) {
                    if (XDB::execute(
                                'REPLACE INTO  contacts (uid, contact)
                                       SELECT  {?}, id
                                         FROM  aliases
                                        WHERE  alias = {?}', $uid, $login))
                    {
                        $page->trigSuccess('Contact ajouté !');
                    } else {
                        $page->trigWarning('Contact déjà dans la liste !');
                    }
                }
        }

        $search = false;
        if ($action == 'search') {
            $action = $subaction;
            $subaction = $ssaction;
            $search = true;
        }
        if ($search && trim(Env::v('quick'))) {
            require_once 'userset.inc.php';
            $base = 'carnet/contacts/search';

            require_once(dirname(__FILE__) . '/search/classes.inc.php');
            ThrowError::$throwHook = array($this, 'searchErrorHandler');
            $view = new SearchSet(true, false, "INNER JOIN contacts AS c2 ON (u.user_id = c2.contact)", "c2.uid = $uid");
        } else {
            $base = 'carnet/contacts';
            $view = new UserSet("INNER JOIN contacts AS c2 ON (u.user_id = c2.contact)", " c2.uid = $uid ");
        }
        $view->addMod('minifiche', 'Mini-fiches', true);
        $view->addMod('trombi', 'Trombinoscope', false, array('with_admin' => false, 'with_promo' => true));
        $view->addMod('geoloc', 'Planisphère', false, array('with_annu' => 'carnet/contacts/search'));
        $view->apply($base, $page, $action, $subaction);
        if ($action != 'geoloc' || ($search && !$ssaction) || (!$search && !$subaction)) {
            $page->changeTpl('carnet/mescontacts.tpl');
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
            foreach ($user as &$value) {
                if (is_utf8($value)) {
                    $value = utf8_decode($value);
                }
            }
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

    function handler_ical(&$page, $alias = null, $hash = null)
    {
        require_once 'rss.inc.php';
        $uid = init_rss(null, $alias, $hash, false);
        if (S::logged()) {
            if (!$uid) {
                $uid = S::i('uid');
            } else if ($uid != S::i('uid')) {
                require_once 'xorg.misc.inc.php';
                send_warning_email("Récupération d\'un autre utilisateur ($uid)");
            }
        } else if (!$uid) {
            exit;
        }
        require_once 'ical.inc.php';
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
                  WHERE c.uid = {?}', $uid);

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
        $vcard = new VCard($res->fetchColumn(), $photos == 'photos');
        $vcard->do_page(&$page);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
