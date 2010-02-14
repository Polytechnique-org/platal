<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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
            'carnet/contacts/vcard' => $this->make_hook('vcard',    AUTH_COOKIE),
            'carnet/contacts/ical'  => $this->make_hook('ical',     AUTH_PUBLIC, 'user', NO_HTTPS),

            'carnet/rss'            => $this->make_hook('rss',      AUTH_PUBLIC, 'user', NO_HTTPS),
        );
    }

    function _add_rss_link(&$page)
    {
        if (!S::hasAuthToken()) {
            return;
        }
        $page->setRssLink('Polytechnique.org :: Carnet',
                          '/carnet/rss/'.S::v('hruid').'/'.S::v('token').'/rss.xml');
    }

    function handler_index(&$page)
    {
        $page->changeTpl('carnet/index.tpl');
        $page->setTitle('Mon carnet');
        $this->_add_rss_link($page);
    }

    function handler_panel(&$page)
    {
        $page->changeTpl('carnet/panel.tpl');

        if (Get::has('read')) {
            XDB::execute('UPDATE  watch
                             SET  last = FROM_UNIXTIME({?})
                           WHERE  uid = {?}',
                         Get::i('read'), S::i('uid'));
            S::set('watch_last', Get::i('read'));
            Platal::session()->updateNbNotifs();
            pl_redirect('carnet/panel');
        }

        require_once 'notifs.inc.php';
        $page->assign('now', time());

        $user = S::user();
        $notifs = Watch::getEvents($user, time() - (7 * 86400));
        $page->assign('notifs', $notifs);
        $page->assign('today', date('Y-m-d'));
        $this->_add_rss_link($page);
    }

    private function getSinglePromotion(PlPage &$page, $promo)
    {
        if (!ctype_digit($promo) || $promo < 1920 || $promo > date('Y')) {
            $page->trigError('Promotion invalide&nbsp;: ' . $promo . '.');
            return null;
        }
        return (int)$promo;
    }

    private function getPromo(PlPage &$page, $promo)
    {
        if (strpos($promo, '-') === false) {
            $promo = $this->getSinglePromotion($page, $promo);
            if (!$promo) {
                return null;
            } else {
                return array($promo);
            }
        }

        list($promo1, $promo2) = explode('-', $promo);
        $promo1 = $this->getSinglePromotion($page, $promo1);
        if (!$promo1) {
            return null;
        }
        $promo2 = $this->getSinglePromotion($page, $promo2);
        if (!$promo2) {
            return null;
        }
        if ($promo1 > $promo2) {
            $page->trigError('Intervalle non valide :&nbsp;' . $promo . '.');
            return null;
        }
        $array = array();
        for ($i = $promo1 ; $i <= $promo2 ; ++$i) {
            $array[] = $i;
        }
        return $array;
    }

    private function addPromo(PlPage &$page, $promo)
    {
        $promos = $this->getPromo($page, $promo);
        if (!$promos || count($promos) == 0) {
            return;
        }
        $to_add = array();
        foreach ($promos as $promo) {
            $to_add[] = XDB::format('({?}, {?})', S::i('uid'), $promo);
        }
        XDB::execute('INSERT IGNORE INTO  watch_promo (uid, promo)
                                  VALUES  ' . implode(', ', $to_add));
    }

    private function delPromo(PlPage &$page, $promo)
    {
        $promos = $this->getPromo($page, $promo);
        if (!$promos || count($promos) == 0) {
            return;
        }
        $to_delete = array();
        foreach ($promos as $promo) {
            $to_delete[] = XDB::format('{?}', $promo);
        }
        XDB::execute('DELETE FROM  watch_promo
                            WHERE  ' . XDB::format('uid = {?}', S::i('uid')) . '
                                   AND promo IN (' . implode(', ', $to_delete) . ')');
    }

    public function addNonRegistered(PlPage &$page, PlUser &$user)
    {
        XDB::execute('INSERT IGNORE INTO  watch_nonins (uid, ni_id)
                                  VALUES  ({?}, {?})', S::i('uid'), $user->id());
    }

    public function delNonRegistered(PlPage &$page, PlUser &$user)
    {
        XDB::execute('DELETE FROM  watch_nonins
                            WHERE  uid = {?} AND ni_id = {?}',
                    S::i('uid'), $user->id());
    }

    public function handler_notifs(&$page, $action = null, $arg = null)
    {
        $page->changeTpl('carnet/notifs.tpl');

        if ($action) {
            S::assert_xsrf_token();
            switch ($action) {
              case 'add_promo':
                $this->addPromo($page, $arg);
                break;

              case 'del_promo':
                $this->delPromo($page, $arg);
                break;

              case 'del_nonins':
                $user = User::get($arg);
                if ($user) {
                    $this->delNonRegistered($page, $user);
                }
                break;

              case 'add_nonins':
                $user = User::get($arg);
                if ($user) {
                    $this->addNonRegistered($page, $user);
                }
                break;
            }
        }

        if (Env::has('subs')) {
            S::assert_xsrf_token();
            $flags = new PlFlagSet();
            foreach (Env::v('sub') as $key=>$value) {
                $flags->addFlag($key, $value);
            }
            XDB::execute('UPDATE  watch
                             SET  actions = {?}
                           WHERE  uid = {?}', $flags, S::i('uid'));
        }

        if (Env::has('flags_contacts')) {
            S::assert_xsrf_token();
            XDB::execute('UPDATE  watch
                             SET  ' . XDB::changeFlag('flags', 'contacts', Env::b('contacts')) . '
                           WHERE  uid = {?}', S::i('uid'));
        }

        if (Env::has('flags_mail')) {
            S::assert_xsrf_token();
            XDB::execute('UPDATE  watch
                             SET  ' . XDB::changeFlag('flags', 'mail', Env::b('mail')) . '
                           WHERE  uid = {?}', S::i('uid'));
        }

        $user = S::user();
        $nonins = new UserFilter(new UFC_WatchRegistration($user));

        $promo = XDB::fetchColumn('SELECT  promo
                                     FROM  watch_promo
                                    WHERE  uid = {?}
                                 ORDER BY  promo', S::i('uid'));
        $page->assign('promo_count', count($promo));
        $ranges = array();
        $range_start  = null;
        $range_end    = null;
        foreach ($promo as $p) {
            if (is_null($range_start)) {
                $range_start = $range_end = $p;
            } else if ($p != $range_end + 1) {
                $ranges[] = array($range_start, $range_end);
                $range_start = $range_end = $p;
            } else {
                $range_end = $p;
            }
        }
        $ranges[] = array($range_start, $range_end);
        $page->assign('promo_ranges', $ranges);
        $page->assign('nonins', $nonins->getUsers());

        list($flags, $actions) = XDB::fetchOneRow('SELECT  flags, actions
                                                     FROM  watch
                                                    WHERE  uid = {?}', S::i('uid'));
        $flags = new PlFlagSet($flags);
        $actions = new PlFlagSet($actions);
        $page->assign('flags', $flags);
        $page->assign('actions', $actions);
    }

    function handler_contacts(&$page, $action = null, $subaction = null, $ssaction = null)
    {
        $page->setTitle('Mes contacts');
        $this->_add_rss_link($page);

        $uid  = S::i('uid');
        $user = Env::v('user');

        // For XSRF protection, checks both the normal xsrf token, and the special RSS token.
        // It allows direct linking to contact adding in the RSS feed.
        if (Env::v('action') && Env::v('token') !== S::v('token')) {
            S::assert_xsrf_token();
        }
        switch (Env::v('action')) {
            case 'retirer':
                if (($user = User::get(Env::v('user')))) {
                    if (XDB::execute("DELETE FROM  contacts
                                            WHERE  uid = {?} AND contact = {?}", $uid, $user->id())) {
                        $page->trigSuccess("Contact retiré&nbsp;!");
                    }
                }
                break;

            case 'ajouter':
                if (($user = User::get(Env::v('user')))) {
                    if (XDB::execute("REPLACE INTO  contacts (uid, contact)
                                            VALUES  ({?}, {?})", $uid, $user->id())) {
                        $page->trigSuccess('Contact ajouté&nbsp;!');
                    } else {
                        $page->trigWarning('Contact déjà dans la liste&nbsp;!');
                    }
                }
                break;
        }

        $search = false;
        $user = S::user();

        require_once 'userset.inc.php';

        if ($action == 'search') {
            $action = $subaction;
            $subaction = $ssaction;
            $search = true;
        }
        if ($search && trim(Env::v('quick'))) {
            $base = 'carnet/contacts/search';

            Platal::load('search', 'classes.inc.php');
            $view = new SearchSet(true, false, new UFC_Contact($user));
        } else {
            $base = 'carnet/contacts';
            $view = new ProfileSet(new UFC_Contact($user));
        }

        $view->addMod('minifiche', 'Mini-fiches', true);
        $view->addMod('trombi', 'Trombinoscope', false, array('with_admin' => false, 'with_promo' => true));
        // TODO: Reactivate when the new map is completed.
        // $view->addMod('geoloc', 'Planisphère', false, array('with_annu' => 'carnet/contacts/search'));
        $view->apply('carnet/contacts', $page, $action, $subaction);
        //if ($action != 'geoloc' || ($search && !$ssaction) || (!$search && !$subaction)) {
        $page->changeTpl('carnet/mescontacts.tpl');
        //}
    }

    function handler_pdf(&$page, $arg0 = null, $arg1 = null)
    {
        $this->load('contacts.pdf.inc.php');
        $user = S::user();

        Platal::session()->close();

        $order = array(new UFO_Name(UserFilter::LASTNAME), new UFO_Name(UserFilter::FIRSTNAME));
        if ($arg0 == 'promo') {
            $order = array_unshift($order, new UFO_Promo());
        } else {
            $order[] = new UFO_Promo();
        }
        $filter = new UserFilter(new UFC_Contact($user), $order);

        $pdf   = new ContactsPDF();

        $profiles = $filter->getProfiles();
        foreach ($profiles as $p) {
            $pdf = ContactsPDF::addContact($pdf, $p, $arg0 == 'photos' || $arg1 == 'photos');
        }
        $pdf->Output();

        exit;
    }

    function handler_rss(&$page, $user = null, $hash = null)
    {
        $this->load('feed.inc.php');
        $feed = new CarnetFeed();
        return $feed->run($page, $user, $hash);
    }

    function handler_ical(&$page, $alias = null, $hash = null)
    {
        $user = Platal::session()->tokenAuth($alias, $hash);
        if (is_null($user)) {
            if (S::logged()) {
                $user == S::user();
            } else {
                return PL_FORBIDDEN;
            }
        }

        require_once 'ical.inc.php';
        $page->changeTpl('carnet/calendar.tpl', NO_SKIN);
        $page->register_function('display_ical', 'display_ical');

        $filter = new UserFilter(new UFC_Contact($user));
        $annivs = Array();
        foreach ($filter->getUsers() as $u) {
            $profile = $u->profile();
            $date = strtotime($profile->birthdate);
            $tomorrow = $date + 86400;
            $annivs[] = array(
                'timestamp' => strtotime($user->registration_date),
                'date' => date('Ymd', $date),
                'tomorrow' => date('Ymd', $tomorrow),
                'hruid' => $profile->hrid(),
                'summary' => 'Anniversaire de ' . $profile->fullName(true)
            );
        }
        $page->assign('events', $annivs);

        pl_content_headers("text/calendar");
    }

    function handler_vcard(&$page, $photos = null)
    {
        $res = XDB::query('SELECT contact
                             FROM contacts
                            WHERE uid = {?}', S::v('uid'));
        $vcard = new VCard($photos == 'photos');
        $vcard->addUsers($res->fetchColumn());
        $vcard->show();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
