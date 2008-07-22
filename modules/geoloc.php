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

class GeolocModule extends PLModule
{
    function handlers()
    {
        return array(
            'geoloc'             => $this->make_hook('default', AUTH_COOKIE),
            'admin/geoloc'           => $this->make_hook('admin', AUTH_MDP, 'admin'),
            'admin/geoloc/dynamap'   => $this->make_hook('admin_dynamap', AUTH_MDP, 'admin'),
            'admin/geoloc/country'   => $this->make_hook('admin_country',  AUTH_MDP, 'admin')
        );
    }

    function handler_default(&$page, $action = null, $subaction = null)
    {
        global $globals;

        $set = new UserSet();
        $set->addMod('geoloc', 'Geolocalisation', true);
        $set->apply('geoloc', $page, $action, $subaction);
    }

    function handler_admin(&$page, $action = false) {
        $page->changeTpl('geoloc/admin.tpl');
        require_once("geoloc.inc.php");
        $page->setTitle('Polytechnique.org - Administration - Geolocalisation');

        $nb_synchro = 0;

        if (Env::has('id') && is_numeric(Env::v('id'))) {
            if (synchro_city(Env::v('id'))) $nb_synchro ++;
        }

        if ($action == 'missinglat') {
            $res = XDB::iterRow("SELECT id FROM geoloc_city WHERE lat = 0 AND lon = 0");
            while ($a = $res->next()) if (synchro_city($a[0])) $nb_synchro++;
        }

        if ($nb_synchro)
            $page->trigSuccess(($nb_synchro > 1)?($nb_synchro." villes ont été synchronisées"):"Une ville a été synchronisée");

        $res = XDB::query("SELECT COUNT(*) FROM geoloc_city WHERE lat = 0 AND lon = 0");
        $page->assign("nb_missinglat", $res->fetchOneCell());
    }

    function handler_admin_dynamap(&$page, $action = false) {
        $page->changeTpl('geoloc/admin_dynamap.tpl');

        if ($action == 'cities_not_on_map') {
            require_once('geoloc.inc.php');
            if (!fix_cities_not_on_map(20))
                $page->trigError("Impossible d'accéder au webservice");
            else
                $refresh = true;
        }

        if ($action == 'smallest_maps') {
            require_once('geoloc.inc.php');
            set_smallest_levels();
        }

        if ($action == 'precise_coordinates') {
            XDB::execute("UPDATE  adresses AS a
                      INNER JOIN  geoloc_city AS c ON(a.cityid = c.id)
                             SET  a.glat = c.lat / 100000, a.glng = c.lon / 100000");
        }

        if ($action == 'newmaps') {
            require_once('geoloc.inc.php');
            if (!get_new_maps(Env::v('url')))
                $page->trigError("Impossible d'accéder aux nouvelles cartes");
        }

        $countMissing = XDB::query("SELECT COUNT(*) FROM geoloc_city AS c LEFT JOIN geoloc_city_in_maps AS m ON(c.id = m.city_id) WHERE m.city_id IS NULL");
        $missing = $countMissing->fetchOneCell();

        $countNoSmallest = XDB::query("SELECT SUM(IF(infos = 'smallest',1,0)) AS n FROM geoloc_city_in_maps GROUP BY city_id ORDER BY n");
        $noSmallest = $countNoSmallest->fetchOneCell() == 0;

        $countNoCoordinates = XDB::query("SELECT COUNT(*) FROM adresses WHERE cityid IS NOT NULL AND glat = 0 AND glng = 0");
        $noCoordinates = $countNoCoordinates->fetchOneCell();

        if (isset($refresh) && $missing) {
            $page->assign("pl_extra_header", "<meta http-equiv='Refresh' content='3'/>");
        }
        $page->assign("nb_cities_not_on_map", $missing);
        $page->assign("no_smallest", $noSmallest);
        $page->assign("no_coordinates", $noCoordinates);
    }

    function handler_admin_country(&$page, $action = 'list', $id = null)
    {
        $page->assign('xorg_title', 'Polytechnique.org - Administration - Pays');
        $page->assign('title', 'Gestion des pays');
        $table_editor = new PLTableEditor('admin/geoloc/country', 'geoloc_pays', 'a2', true);
        $table_editor->describe('a2', 'alpha-2', true);
        $table_editor->describe('a3', 'alpha-3', false);
        $table_editor->describe('n3', 'ISO numeric', false);
        $table_editor->describe('num', 'num', false);
        $table_editor->describe('worldrgn', 'Continent', false);
        $table_editor->describe('subd', 'Subdivisions territoriales', false);
        $table_editor->describe('post', 'post', false);
        $table_editor->describe('pays', 'Nom (fr)', true);
        $table_editor->describe('country', 'Nom (en)', true);
        $table_editor->describe('phoneprf', 'Préfixe téléphonique', true);
        $table_editor->describe('phoneformat', 'Format du téléphone (ex: (+p) ### ## ## ##)', false);
        $table_editor->describe('capital', 'Capitale', true);
        $table_editor->describe('nat', 'Nationalité', true);
        $table_editor->describe('display', 'Format des adresses', false);

        if ($action == 'update') {
            if (Post::has('a2') && (Post::v('a2') == $id) && Post::has('phoneprf') && (Post::v('phoneprf') != '')) {
                if (Post::has('phoneformat')) {
                    $new_format = Post::v('phoneformat');
                } else {
                    $new_format = '';
                }
                $res = XDB::query("SELECT phoneformat
                                     FROM geoloc_pays
                                    WHERE phoneprf = {?}
                                    LIMIT 1",
                                  Post::v('phoneprf'));
                $old_format = $res->fetchOneCell();
                if ($new_format != $old_format) {
                    require_once("profil.func.inc.php");
                    XDB::execute("UPDATE  geoloc_pays
                                     SET  phoneformat = {?}
                                   WHERE  phoneprf = {?}",
                                 $new_format, Post::v('phoneprf'));
                }
            }
        }
        $table_editor->apply($page, $action, $id);
    }

}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
