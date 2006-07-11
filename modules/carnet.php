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
            'carnet/panel'  => $this->make_hook('panel', AUTH_COOKIE),

            'carnet/rss'    => $this->make_hook('rss',   AUTH_PUBLIC),
            'carnet/ical'   => $this->make_hook('ical',  AUTH_PUBLIC),
        );
    }

    function _add_rss_link(&$page)
    {
        if (!Session::has('core_rss_hash'))
            return;
        $page->assign('xorg_rss',
                      array('title' => 'Polytechnique.org :: Carnet',
                            'href'  => '/carnet/rss/'.Session::get('forlife')
                                      .'/'.Session::get('core_rss_hash').'/rss.xml')
                      );
    }

    function handler_panel(&$page)
    {
        $page->changeTpl('carnet/panel.tpl');

        if (Get::has('read')) {
            global $globals;

            $_SESSION['watch_last'] = Get::get('read');
            redirect($globals->baseurl.'/carnet/panel');
        }

        require_once 'notifs.inc.php';

        $page->assign('now',date('YmdHis'));
        $notifs = new Notifs(Session::getInt('uid'), true);

        $page->assign('notifs', $notifs);
        $page->assign('today', date('Y-m-d'));
        $this->_add_rss_link($page);

        return PL_OK;
    }

    function handler_rss(&$page, $user = null, $hash = null)
    {
        require_once 'rss.inc.php';
        require_once 'notifs.inc.php';

        $uid    = init_rss('carnet/rss.tpl', $user, $hash);
        $notifs = new Notifs($uid, false);
        $page->assign('notifs', $notifs);

        return PL_OK;
    }

    function handler_ical(&$page, $user = null, $hash = null, $all = null)
    {
        global $globals;

        new_nonhtml_page('carnet/calendar.tpl', AUTH_PUBLIC);

        if ($alias && $hash) {
            $res = $globals->xdb->query(
                'SELECT  a.id
                   FROM  aliases         AS a
             INNER JOIN  auth_user_quick AS q ON ( a.id = q.user_id AND q.core_rss_hash = {?} )
                  WHERE  a.alias = {?} AND a.type != "homonyme"', $hash, $alias);
            $uid = $res->fetchOneCell();
        }

        require_once 'notifs.inc.php';
        $notifs = new Notifs($uid, true);

        $annivcat = false;
        foreach ($notifs->_cats as $cat) {
            if (preg_match('/anniv/i', $cat['short']))
                $annivcat = $cat['id'];
        }

        if ($annivcat !== false) {
            $annivs = array();
            foreach ($notifs->_data[$annivcat] as $promo) {
                foreach ($promo as $notif) {
                    if ($all == 'all' || $notif['contact']) {
                        $annivs[] = array(
                            'timestamp' => $notif['known'],
                            'date'      => strtotime($notif['date']),
                            'tomorrow'  => strtotime("+1 day", strtotime($notif['date'])),
                            'bestalias' => $notif['bestalias'],
                            'summary'   => 'Anniversaire de '.$notif['prenom']
                                           .' '.$notif['nom'].' - x '.$notif['promo'],
                         );
                    }
                }
            }
            $page->assign('events', $annivs);
        }

        header('Content-Type: text/calendar; charset=utf-8');

        return PL_OK;
    }
}

?>
