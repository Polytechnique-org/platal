<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

class GadgetsModule extends PLModule
{
    function handlers()
    {
        return array(
            'gadgets/ig-events.xml' => $this->make_hook('ig_events_xml', AUTH_PUBLIC, 'user', NO_HTTPS),
            'gadgets/ig-events'     => $this->make_hook('ig_events',     AUTH_PUBLIC),
            'gadgets/ig-search.xml' => $this->make_hook('ig_search_xml', AUTH_PUBLIC, 'user', NO_HTTPS),
            'gadgets/ig-search'     => $this->make_hook('ig_search',     AUTH_PUBLIC),
        );
    }

    function handler_ig_events_xml(&$page)
    {
        require_once 'gadgets/gadgets.inc.php';
        init_igoogle_xml('gadgets/ig-events.xml.tpl');
    }

    function handler_ig_events(&$page)
    {
        require_once 'gadgets/gadgets.inc.php';
        init_igoogle_html('gadgets/ig-events.tpl', AUTH_COOKIE);

        $events = XDB::iterator('SELECT  SQL_CALC_FOUND_ROWS
                                         e.id, e.titre, UNIX_TIMESTAMP(e.creation_date) AS creation_date,
                                         IF(u.nom_usage = "", u.nom, u.nom_usage) AS nom, u.prenom, u.promo,
                                         ev.user_id IS NULL AS nonlu, e.uid
                                   FROM  announces AS e
                              LEFT JOIN  announce_read AS ev ON (e.id = ev.evt_id AND ev.uid = {?})
                                  WHERE  FIND_IN_SET("valide", e.flags) AND expiration >= NOW()
                               ORDER BY  e.creation_date DESC', S::i('uid'));
        $page->assign('event_count', XDB::query("SELECT FOUND_ROWS()")->fetchOneCell());

        Platal::load('events', 'feed.inc.php');
        $user = S::user();
        $data = array();
        while ($e = PlFeed::nextEvent($events, $user)) {
            $data[] = $e;
            if (count($data) == 5) {
                break;
            }
        }
        $page->assign('events', $data);
    }

    function handler_ig_search_xml(&$page) {
        require_once 'gadgets/gadgets.inc.php';
        init_igoogle_xml('gadgets/ig-search.xml.tpl');
    }

    function handler_ig_search(&$page)
    {
        if (Env::has('quick') && Env::s('quick') != '') {
            require_once 'userset.inc.php';
            $view = new SearchSet(true);
            $view->addMod('gadget', 'Gadget', true);
            $view->apply(null, $page);

            $nb_tot = $view->count();
            $page->assign('result_count', $nb_tot);

            if (!S::logged() && $nb_tot > $globals->search->public_max) {
                $page->assign('error', 'Votre recherche a généré trop de résultats pour un affichage public.');
            } elseif ($nb_tot > $globals->search->private_max) {
                $page->assign('error', 'Recherche trop générale.');
            } elseif (empty($nb_tot)) {
                $page->assign('error', 'Il n\'existe personne correspondant à ces critères dans la base !');
            }
        }

        require_once 'gadgets/gadgets.inc.php';
        init_igoogle_html('gadgets/ig-search.tpl', AUTH_PUBLIC);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
