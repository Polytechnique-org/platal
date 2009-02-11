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
            'carnet/contacts/pdf'   => $this->make_hook('pdf',      AUTH_COOKIE, 'user', NO_HTTPS),
            'carnet/contacts/ical'  => $this->make_hook('ical',     AUTH_PUBLIC, 'user', NO_HTTPS),
            'carnet/contacts/vcard' => $this->make_hook('vcard',    AUTH_COOKIE, 'user', NO_HTTPS),

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
            $page->trigError('Promotion invalide : ' . $promo);
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
            $page->trigError("Intervale non valide : " . $promo);
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

        $uid  = S::v('uid');
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
                        $page->trigSuccess("Contact retiré !");
                    }
                }
                break;

            case 'ajouter':
                if (($user = User::get(Env::v('user')))) {
                    if (XDB::execute("REPLACE INTO  contacts (uid, contact)
                                            VALUES  ({?}, {?})", $uid, $user->id())) {
                        $page->trigSuccess('Contact ajouté !');
                    } else {
                        $page->trigWarning('Contact déjà dans la liste !');
                    }
                }
                break;
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

            Platal::load('search', 'classes.inc.php');
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
        $this->load('contacts.pdf.inc.php');
        require_once 'user.func.inc.php';

        Platal::session()->close();

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

        $res = XDB::iterRow(
                'SELECT u.prenom,
                        IF(u.nom_usage = \'\',u.nom,u.nom_usage) AS nom,
                        u.promo,
                        u.naissance,
                        DATE_ADD(u.naissance, INTERVAL 1 DAY) AS end,
                        u.date_ins,
                        u.hruid
                   FROM contacts      AS c
             INNER JOIN auth_user_md5 AS u ON (u.user_id = c.contact)
             INNER JOIN aliases       AS a ON (u.user_id = a.id AND a.type = \'a_vie\')
                  WHERE c.uid = {?}', $user->id());

        $annivs = Array();
        while (list($prenom, $nom, $promo, $naissance, $end, $ts, $hruid) = $res->next()) {
            $naissance = str_replace('-', '', $naissance);
            $end       = str_replace('-', '', $end);
            $annivs[] = array(
                'timestamp' => strtotime($ts),
                'date'      => $naissance,
                'tomorrow'  => $end,
                'hruid'     => $hruid,
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
        $vcard = new VCard($photos == 'photos');
        $vcard->addUsers($res->fetchColumn());
        $vcard->show();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
