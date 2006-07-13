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

class XnetModule extends PLModule
{
    function handlers()
    {
        return array(
            'index'     => $this->make_hook('index',     AUTH_PUBLIC),
            'exit'      => $this->make_hook('exit',      AUTH_PUBLIC),

            'about'     => $this->make_hook('about',     AUTH_PUBLIC),
            'article12' => $this->make_hook('article12', AUTH_PUBLIC),
            'article16' => $this->make_hook('article16', AUTH_PUBLIC),
            'creategpx' => $this->make_hook('creategpx', AUTH_PUBLIC),
            'services'  => $this->make_hook('services',  AUTH_PUBLIC),
            'manuel'    => $this->make_hook('manuel',    AUTH_PUBLIC),

            'plan'      => $this->make_hook('plan',      AUTH_PUBLIC),
        );
    }

    function handler_index(&$page)
    {
        $page->changeTpl('xnet/index.tpl');
        return PL_OK;
    }

    function handler_exit(&$page)
    {
        XnetSession::destroy();
        $page->changeTpl('xnet/deconnexion.tpl');
        $page->useMenu();
        return PL_OK;
    }

    function handler_about(&$page)
    {
        $page->changeTpl('xnet/apropos.tpl');
        $page->useMenu();
        return PL_OK;
    }

    function handler_article12(&$page)
    {
        $page->changeTpl('xnet/article12.tpl');
        $page->useMenu();
        return PL_OK;
    }

    function handler_article16(&$page)
    {
        $page->changeTpl('xnet/article16.tpl');
        $page->useMenu();
        return PL_OK;
    }

    function handler_creategpx(&$page)
    {
        $page->changeTpl('xnet/creation-groupex.tpl');
        $page->useMenu();
        return PL_OK;
    }

    function handler_creategpx(&$page)
    {
        $page->changeTpl('xnet/services.tpl');
        $page->useMenu();
        return PL_OK;
    }

    function handler_manuel(&$page)
    {
        $page->changeTpl('xnet/manuel.tpl');
        $page->useMenu();
        return PL_OK;
    }

    function handler_plan(&$page)
    {
        global $globals;

        $page->changeTpl('xnet/plan.tpl');

        $page->setType('plan');

        $res = $globals->xdb->iterator(
                'SELECT  dom.id, dom.nom as domnom, asso.diminutif, asso.nom
                   FROM  groupex.dom
             INNER JOIN  groupex.asso ON dom.id = asso.dom
                  WHERE  FIND_IN_SET("GroupesX", dom.cat) AND FIND_IN_SET("GroupesX", asso.cat)
               ORDER BY  dom.nom, asso.nom');
        $groupesx = array();
        while ($tmp = $res->next()) { $groupesx[$tmp['id']][] = $tmp; }
        $page->assign('groupesx', $groupesx);

        $res = $globals->xdb->iterator(
                'SELECT  dom.id, dom.nom as domnom, asso.diminutif, asso.nom
                   FROM  groupex.dom
             INNER JOIN  groupex.asso ON dom.id = asso.dom
                  WHERE  FIND_IN_SET("Binets", dom.cat) AND FIND_IN_SET("Binets", asso.cat)
               ORDER BY  dom.nom, asso.nom');
        $binets = array();
        while ($tmp = $res->next()) { $binets[$tmp['id']][] = $tmp; }
        $page->assign('binets', $binets);

        $res = $globals->xdb->iterator(
                'SELECT  asso.diminutif, asso.nom
                   FROM  groupex.asso
                  WHERE  cat LIKE "%Promotions%"
               ORDER BY  diminutif');
        $page->assign('promos', $res);

        $res = $globals->xdb->iterator(
                'SELECT  asso.diminutif, asso.nom
                   FROM  groupex.asso
                  WHERE  FIND_IN_SET("Institutions", cat)
               ORDER BY  diminutif');
        $page->assign('inst', $res);

        return PL_OK;
    }
}

?>
