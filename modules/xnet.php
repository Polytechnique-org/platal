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

            'admin'     => $this->make_hook('admin',     AUTH_MDP, 'admin'),
            'groups'    => $this->make_hook('groups',    AUTH_PUBLIC),
            'groupes.php' => $this->make_hook('groups2', AUTH_PUBLIC),
            'plan'      => $this->make_hook('plan',      AUTH_PUBLIC),
        );
    }

    function handler_index(&$page)
    {
        $page->changeTpl('xnet/index.tpl');
    }

    function handler_exit(&$page)
    {
        XnetSession::destroy();
        $page->changeTpl('xnet/deconnexion.tpl');
        $page->useMenu();
    }

    function handler_about(&$page)
    {
        $page->changeTpl('xnet/apropos.tpl');
        $page->useMenu();
    }

    function handler_article12(&$page)
    {
        $page->changeTpl('xnet/article12.tpl');
        $page->useMenu();
    }

    function handler_article16(&$page)
    {
        $page->changeTpl('xnet/article16.tpl');
        $page->useMenu();
    }

    function handler_creategpx(&$page)
    {
        $page->changeTpl('xnet/creation-groupex.tpl');
        $page->useMenu();
    }

    function handler_services(&$page)
    {
        $page->changeTpl('xnet/services.tpl');
        $page->useMenu();
    }

    function handler_admin(&$page)
    {
        new_admin_page('xnet/admin.tpl');
        $page->useMenu();

        if (Get::has('del')) {
            $res = XDB::query('SELECT id, nom, mail_domain
                                           FROM groupex.asso WHERE diminutif={?}',
                                        Get::v('del'));
            list($id, $nom, $domain) = $res->fetchOneRow();
            $page->assign('nom', $nom);
            if ($id && Post::has('del')) {
                XDB::query('DELETE FROM groupex.membres WHERE asso_id={?}', $id);
                $page->trig('membres supprimés');

                if ($domain) {
                    XDB::query('DELETE FROM  virtual_domains WHERE domain={?}', $domain);
                    XDB::query('DELETE FROM  virtual, virtual_redirect
                                                USING  virtual INNER JOIN virtual_redirect USING (vid)
                                                WHERE  alias LIKE {?}', '%@'.$domain);
                    $page->trig('suppression des alias mails');

                    require_once('lists.inc.php');
                    $client =& lists_xmlrpc(S::v('uid'), S::v('password'), $domain);
                    if ($listes = $client->get_lists()) {
                        foreach ($listes as $l) {
                            $client->delete_list($l['list'], true);
                        }
                        $page->trig('mail lists surpprimées');
                    }
                }

                XDB::query('DELETE FROM groupex.asso WHERE id={?}', $id);
                $page->trig("Groupe $nom supprimé");
                Get::kill('del');
            }
            if (!$id) {
                Get::kill('del');
            }
        }

        if (Post::has('diminutif')) {
            XDB::query('INSERT INTO groupex.asso (id,diminutif)
                                 VALUES(NULL,{?})', Post::v('diminutif'));
            pl_redirect('../'.Post::v('diminutif').'/edit');
        }

        $res = XDB::query('SELECT nom,diminutif FROM groupex.asso ORDER by NOM');
        $page->assign('assos', $res->fetchAllAssoc());
    }

    function handler_plan(&$page)
    {
        $page->changeTpl('xnet/plan.tpl');

        $page->setType('plan');

        $res = XDB::iterator(
                'SELECT  dom.id, dom.nom as domnom, asso.diminutif, asso.nom
                   FROM  groupex.dom
             INNER JOIN  groupex.asso ON dom.id = asso.dom
                  WHERE  FIND_IN_SET("GroupesX", dom.cat) AND FIND_IN_SET("GroupesX", asso.cat)
               ORDER BY  dom.nom, asso.nom');
        $groupesx = array();
        while ($tmp = $res->next()) { $groupesx[$tmp['id']][] = $tmp; }
        $page->assign('groupesx', $groupesx);

        $res = XDB::iterator(
                'SELECT  dom.id, dom.nom as domnom, asso.diminutif, asso.nom
                   FROM  groupex.dom
             INNER JOIN  groupex.asso ON dom.id = asso.dom
                  WHERE  FIND_IN_SET("Binets", dom.cat) AND FIND_IN_SET("Binets", asso.cat)
               ORDER BY  dom.nom, asso.nom');
        $binets = array();
        while ($tmp = $res->next()) { $binets[$tmp['id']][] = $tmp; }
        $page->assign('binets', $binets);

        $res = XDB::iterator(
                'SELECT  asso.diminutif, asso.nom
                   FROM  groupex.asso
                  WHERE  cat LIKE "%Promotions%"
               ORDER BY  diminutif');
        $page->assign('promos', $res);

        $res = XDB::iterator(
                'SELECT  asso.diminutif, asso.nom
                   FROM  groupex.asso
                  WHERE  FIND_IN_SET("Institutions", cat)
               ORDER BY  diminutif');
        $page->assign('inst', $res);
        $page->useMenu();
    }

    function handler_groups2(&$page)
    {
        $this->handler_groups(&$page, Get::v('cat'), Get::v('dom'));
    }

    function handler_groups(&$page, $cat = null, $dom = null)
    {
        if (!$cat) {
            $this->handler_index(&$page);
        }

        $cat = strtolower($cat);

        $page->changeTpl('xnet/groupes.tpl');
        $page->assign('cat', $cat);
        $page->assign('dom', $dom);

        $res  = XDB::query("SELECT id,nom FROM groupex.dom
                             WHERE FIND_IN_SET({?}, cat)
                          ORDER BY nom", $cat);
        $doms = $res->fetchAllAssoc();
        $page->assign('doms', $doms);

        if (empty($doms)) {
            $res = XDB::query("SELECT diminutif, nom FROM groupex.asso
                                   WHERE FIND_IN_SET({?}, cat)
                                ORDER BY nom", $cat);
            $page->assign('gps', $res->fetchAllAssoc());
        } elseif (!is_null($dom)) {
            $res = XDB::query("SELECT diminutif, nom FROM groupex.asso
                                WHERE FIND_IN_SET({?}, cat) AND dom={?}
                             ORDER BY nom", $cat, $dom);
            $page->assign('gps', $res->fetchAllAssoc());
        }

        $page->useMenu();
        $page->setType($cat);
    }
}

?>
