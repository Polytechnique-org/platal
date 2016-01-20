<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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
            'carnet'                       => $this->make_hook('index',              AUTH_COOKIE, 'directory_private'),
            'carnet/panel'                 => $this->make_hook('panel',              AUTH_COOKIE, 'directory_private'),
            'carnet/notifs'                => $this->make_hook('notifs',             AUTH_COOKIE, 'directory_private'),

            'carnet/contacts'              => $this->make_hook('contacts',           AUTH_COOKIE, 'directory_private'),
            'carnet/contacts/pdf'          => $this->make_hook('pdf',                AUTH_COOKIE, 'directory_private'),
            'carnet/contacts/vcard'        => $this->make_hook('vcard',              AUTH_COOKIE, 'directory_private'),
            'carnet/contacts/ical'         => $this->make_token_hook('ical',         AUTH_COOKIE, 'directory_private'),
            'carnet/contacts/csv'          => $this->make_token_hook('csv',          AUTH_COOKIE, 'directory_private'),
            'carnet/contacts/csv/birthday' => $this->make_token_hook('csv_birthday', AUTH_COOKIE, 'directory_private'),
            'carnet/batch'                 => $this->make_hook('batch',              AUTH_COOKIE, 'directory_private'),

            'carnet/rss'                   => $this->make_token_hook('rss',          AUTH_COOKIE, 'directory_private'),
        );
    }

    function _add_rss_link($page)
    {
        if (!S::hasAuthToken()) {
            return;
        }
        $page->setRssLink('Polytechnique.org :: Carnet',
                          '/carnet/rss/' . S::v('hruid') . '/' . S::user()->token . '/rss.xml');
    }

    function handler_index($page)
    {
        $page->changeTpl('carnet/index.tpl');
        $page->setTitle('Mon carnet');
        $this->_add_rss_link($page);
    }

    function handler_panel($page)
    {
        $page->changeTpl('carnet/panel.tpl');

        if (Get::has('read')) {
            XDB::execute('UPDATE  watch
                             SET  last = FROM_UNIXTIME({?})
                           WHERE  uid = {?}',
                         Get::i('read'), S::i('uid'));
            S::user()->invalidWatchCache();
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

    private function getSinglePromotion(PlPage $page, $promo)
    {
        if (!(is_int($promo) || ctype_digit($promo)) || $promo < 1920 || $promo > date('Y')) {
            $page->trigError('Promotion invalide&nbsp;: ' . $promo . '.');
            return null;
        }
        return (int)$promo;
    }

    private function getPromo(PlPage $page, $promo)
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

    private function addPromo(PlPage $page, $promo)
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
        S::user()->invalidWatchCache();
        Platal::session()->updateNbNotifs();
    }

    private function delPromo(PlPage $page, $promo)
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
        S::user()->invalidWatchCache();
        Platal::session()->updateNbNotifs();
    }

    private function getGroup(PlPage $page, $group)
    {
        $groupid = XDB::fetchOneCell("SELECT  id
                                        FROM  groups
                                       WHERE  (nom = {?} OR diminutif = {?}) AND NOT FIND_IN_SET('private', pub)",
                                     $group, $group);
        if (is_null($groupid)) {
            $search = XDB::formatWildcards(XDB::WILDCARD_CONTAINS, $group);
            $res = XDB::query('SELECT  id
                                 FROM  groups
                                WHERE  (nom ' . $search . ' OR diminutif ' . $search . ") AND NOT FIND_IN_SET('private', pub)",
                              $search, $search);
            if ($res->numRows() == 1) {
                $groupid = $res->fetchOneCell();
            }
        }
        return $groupid;
    }

    private function addGroup(PlPage $page, $group)
    {
        $groupid = $this->getGroup($page, $group);
        if (is_null($groupid)) {
            return;
        }
        XDB::execute('INSERT IGNORE INTO  watch_group (uid, groupid)
                                  VALUES  ({?}, {?})',
                     S::i('uid'), $groupid);
        S::user()->invalidWatchCache();
        Platal::session()->updateNbNotifs();
    }

    private function delGroup(PlPage $page, $group)
    {
        $groupid = $this->getGroup($page, $group);
        if (is_null($groupid)) {
            return;
        }
        XDB::execute('DELETE FROM  watch_group
                            WHERE  uid = {?} AND groupid = {?}',
                     S::i('uid'), $groupid);
        S::user()->invalidWatchCache();
        Platal::session()->updateNbNotifs();
    }

    public function addNonRegistered(PlPage $page, PlUser $user)
    {
        XDB::execute('INSERT IGNORE INTO  watch_nonins (uid, ni_id)
                                  VALUES  ({?}, {?})', S::i('uid'), $user->id());
        if (XDB::affectedRows() > 0) {
            S::user()->invalidWatchCache();
            Platal::session()->updateNbNotifs();
            $page->trigSuccess('Contact ajouté&nbsp;: ' . $user->fullName(true));
        } else {
            $page->trigWarning('Contact déjà dans la liste&nbsp;: ' . $user->fullName(true));
        }
    }

    public function delNonRegistered(PlPage $page, PlUser $user)
    {
        XDB::execute('DELETE FROM  watch_nonins
                            WHERE  uid = {?} AND ni_id = {?}',
                    S::i('uid'), $user->id());
        S::user()->invalidWatchCache();
        Platal::session()->updateNbNotifs();
    }

    public function addRegistered(PlPage $page, Profile $profile)
    {
        XDB::execute('INSERT IGNORE INTO  contacts (uid, contact)
                                  VALUES  ({?}, {?})',
                     S::i('uid'), $profile->id());
        if (XDB::affectedRows() > 0) {
            S::user()->invalidWatchCache();
            Platal::session()->updateNbNotifs();
            $page->trigSuccess('Contact ajouté&nbsp;: ' . $profile->fullName(true));
        } else {
            $page->trigWarning('Contact déjà dans la liste&nbsp;: ' . $profile->fullName(true));
        }
    }

    public function delRegistered(PlPage $page, Profile $profile)
    {
        XDB::execute('DELETE FROM  contacts
                            WHERE  uid = {?} AND contact = {?}',
                     S::i('uid'), $profile->id());
        if (XDB::affectedRows() > 0) {
            S::user()->invalidWatchCache();
            Platal::session()->updateNbNotifs();
            $page->trigSuccess("Contact retiré&nbsp;!");
        }

    }

    public function handler_notifs($page, $action = null, $arg = null)
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

              case 'add_group':
                $this->addGroup($page, $arg);
                break;

              case 'del_group':
                $this->delGroup($page, $arg);
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
            S::user()->invalidWatchCache();
            Platal::session()->updateNbNotifs();
        }

        if (Env::has('flags_contacts')) {
            S::assert_xsrf_token();
            XDB::execute('UPDATE  watch
                             SET  ' . XDB::changeFlag('flags', 'contacts', Env::b('contacts')) . '
                           WHERE  uid = {?}', S::i('uid'));
            S::user()->invalidWatchCache();
            Platal::session()->updateNbNotifs();
        }

        if (Env::has('flags_mail')) {
            S::assert_xsrf_token();
            XDB::execute('UPDATE  watch
                             SET  ' . XDB::changeFlag('flags', 'mail', Env::b('mail')) . '
                           WHERE  uid = {?}', S::i('uid'));
            S::user()->invalidWatchCache();
            Platal::session()->updateNbNotifs();
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

        $groups = XDB::fetchColumn('SELECT  g.nom
                                      FROM  watch_group AS w
                                INNER JOIN  groups      AS g ON (g.id = w.groupid)
                                     WHERE  w.uid = {?}
                                  ORDER BY  g.nom',
                                   S::i('uid'));
        $page->assign('groups', $groups);
        $page->assign('groups_count', count($groups));
        list($flags, $actions) = XDB::fetchOneRow('SELECT  flags, actions
                                                     FROM  watch
                                                    WHERE  uid = {?}', S::i('uid'));
        $flags = new PlFlagSet($flags);
        $actions = new PlFlagSet($actions);
        $page->assign('flags', $flags);
        $page->assign('actions', $actions);
    }

    function handler_contacts($page, $action = null, $subaction = null, $ssaction = null)
    {
        $page->setTitle('Mes contacts');
        $this->_add_rss_link($page);

        // For XSRF protection, checks both the normal xsrf token, and the special RSS token.
        // It allows direct linking to contact adding in the RSS feed.
        if (Env::v('action') && Env::v('token') !== S::user()->token) {
            S::assert_xsrf_token();
        }
        switch (Env::v('action')) {
            case 'retirer':
                if (($contact = Profile::get(Env::v('user')))) {
                    $this->delRegistered($page, $contact);
                }
                break;

            case 'ajouter':
                if (($contact = Profile::get(Env::v('user')))) {
                    $this->addRegistered($page, $contact);
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
            $view = new QuickSearchSet(new UFC_Contact($user));
        } else {
            $base = 'carnet/contacts';
            $view = new ProfileSet(new UFC_Contact($user));
        }

        $view->addMod('minifiche', 'Mini-fiches', true);
        $view->addMod('trombi', 'Trombinoscope', false, array('with_admin' => false, 'with_promo' => true));
        $view->addMod('map', 'Planisphère');
        $view->apply('carnet/contacts', $page, $action, $subaction);
        $page->changeTpl('carnet/mescontacts.tpl');
    }

    function handler_pdf($page, $arg0 = null, $arg1 = null)
    {
        $this->load('contacts.pdf.inc.php');
        $user = S::user();

        Platal::session()->close();

        $order = array(new UFO_Name());
        if ($arg0 == 'promo') {
            $order = array_unshift($order, new UFO_Promo());
        } else {
            $order[] = new UFO_Promo();
        }
        $filter = new UserFilter(new UFC_Contact($user), $order);

        $pdf   = new ContactsPDF();

        $it = $filter->iterProfiles();
        while ($p = $it->next()) {
            $pdf = ContactsPDF::addContact($pdf, $p, $arg0 == 'photos' || $arg1 == 'photos');
        }
        $pdf->Output();

        exit;
    }

    function handler_rss(PlPage $page, PlUser $user)
    {
        $this->load('feed.inc.php');
        $feed = new CarnetFeed();
        return $feed->run($page, $user);
    }

    function buildBirthRef(Profile $profile)
    {
        $date = strtotime($profile->birthdate);
        $tomorrow = $date + 86400;
        return array(
            'timestamp' => $date,
            'date' => date('Ymd', $date),
            'tomorrow' => date('Ymd', $tomorrow),
            'email' => $profile->owner()->bestEmail(),
            'summary' => 'Anniversaire de ' . $profile->fullName(true)
        );
    }

    function handler_csv_birthday(PlPage $page, PlUser $user)
    {
        $page->changeTpl('carnet/calendar.outlook.tpl', NO_SKIN);
        $filter = new UserFilter(new UFC_Contact($user));
        $profiles = $filter->iterProfiles();
        $page->assign('events', PlIteratorUtils::map($profiles, array($this, 'buildBirthRef')));
        $years = array(date("Y"));
        for ($i = 1; $i <= 10; ++$i) {
            $years[] = $years[0] + $i;
        }
        $page->assign('years', $years);
        $lang = 'fr';
        if (preg_match('/([a-zA-Z]{2,8})($|[^a-zA-Z])/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
            $lang = strtolower($matches[1]);
        }
        $page->assign('lang', $lang);
        if ($lang == 'fr') {
            $encoding = 'iso8859-15';
        } else {
            $encoding = 'utf-8';
        }
        pl_cached_content_headers('text/comma-separated-values; charset=' . $encoding, 1);
    }

    function handler_ical(PlPage $page, PlUser $user)
    {
        require_once 'ical.inc.php';
        $page->changeTpl('carnet/calendar.tpl', NO_SKIN);
        $page->register_function('display_ical', 'display_ical');

        $filter = new UserFilter(new UFC_Contact($user));
        $profiles = $filter->iterProfiles();
        $page->assign('events', PlIteratorUtils::map($profiles, array($this, 'buildBirthRef')));

        pl_cached_content_headers('text/calendar', 1);
    }

    function handler_vcard($page, $photos = null)
    {
        $pf = new ProfileFilter(new UFC_Contact(S::user()));
        $vcard = new VCard($photos == 'photos');
        $vcard->addProfiles($pf->getProfiles(null, Profile::FETCH_ALL));
        $vcard->show();
    }

    function handler_csv(PlPage $page, PlUser $user)
    {
        $page->changeTpl('carnet/mescontacts.outlook.tpl', NO_SKIN);
        $pf = new ProfileFilter(new UFC_Contact($user));
        require_once 'carnet/outlook.inc.php';
        Outlook::output_profiles($pf->getProfiles(), 'fr');
    }

    function handler_batch($page)
    {
        $page->changeTpl('carnet/batch.tpl');
        $errors = false;
        $incomplete = array();

        if (Post::has('add')) {
            S::assert_xsrf_token();
            require_once 'userset.inc.php';
            require_once 'emails.inc.php';
            require_once 'marketing.inc.php';

            $list = explode("\n", Post::v('list'));
            $origin = Post::v('origin');

            foreach ($list as $item) {
                if ($item = trim($item)) {
                    $elements = preg_split("/\s/", $item);
                    $email = array_pop($elements);
                    if (!isvalid_email($email)) {
                        $page->trigError('Email invalide&nbsp;: ' . $email);
                        $incomplete[] = $item;
                        $errors = true;
                        continue;
                    }

                    $user = User::getSilent($email);
                    if (is_null($user)) {
                        $details = implode(' ', $elements);
                        $promo = trim(array_pop($elements));
                        $cond = new PFC_And();
                        if (preg_match('/^[MDX]\d{4}$/', $promo)) {
                            $cond->addChild(new UFC_Promo('=', UserFilter::DISPLAY, $promo));
                        } else {
                            $cond->addChild(new UFC_NameTokens($promo));
                        }
                        foreach ($elements as $element) {
                            $cond->addChild(new UFC_NameTokens($element));
                        }
                        $uf = new UserFilter($cond);
                        $count = $uf->getTotalCount();
                        if ($count == 0) {
                            $page->trigError('Les informations : « ' . $item . ' » ne correspondent à aucun camarade.');
                            $incomplete[] = $item;
                            $errors = true;
                            continue;
                        } elseif ($count > 1) {
                            $page->trigError('Les informations : « ' . $item . ' » sont ambigues et correspondent à plusieurs camarades.');
                            $incomplete[] = $item;
                            $errors = true;
                            continue;
                        } else {
                            $user = $uf->getUser();
                        }
                    }

                    if ($user->state == 'active') {
                        $this->addRegistered($page, $user->profile());
                    } else {
                        if (!User::isForeignEmailAddress($email)) {
                            $page->trigError('Email pas encore attribué&nbsp;: ' . $email);
                            $incomplete[] = $item;
                            $errors = true;
                        } else {
                            $this->addNonRegistered($page, $user);
                            if (!Marketing::get($user->id(), $email, true)) {
                                check_email($email, "Une adresse surveillée est proposée au marketing par " . S::user()->login());
                                $market = new Marketing($user->id(), $email, 'default', null, $origin, S::v('uid'), null);
                                $market->add();
                            }
                        }
                    }
                }
            }
        }
        $page->assign('errors', $errors);
        $page->assign('incomplete', $incomplete);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
