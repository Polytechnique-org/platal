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

require_once dirname(__FILE__).'/../../classes/Page.php';

class XnetPage extends PlatalPage
{
    // {{{ function XnetPage()

    function XnetPage($tpl, $type=SKINNED)
    {
        $this->PlatalPage($tpl, $type);

        $this->register_function('list_all_my_groups', 'list_all_my_groups');
        $this->register_modifier('cat_pp', 'cat_pp');
        $this->assign('it_is_xnet', true);

        if (!S::logged() && Get::has('auth')) {
            XnetSession::doAuthX();
        }
    }

    // }}}
    // {{{ function run()

    function run()
    {
        $this->_run('xnet/skin.tpl');
    }

    // }}}
    // {{{ function setType

    function setType($type)
    {
        $this->assign('xnet_type', strtolower($type));
    }

    // }}}
    // {{{ function useMenu

    function useMenu()
    {
        global $globals;

        $menu = array();

        if (S::logged()) {
            $sub = array();
            $sub['déconnexion']   = 'exit';
            $menu['no_title'] = $sub;
        }

        $sub = array();
        $sub['accueil']           = '';
        $sub['liste des groupes'] = 'plan';
        $sub['documentation']     = 'Xnet';
        $menu["Menu Principal"]   = $sub;

        if (S::logged() && (is_member() || may_update())) {
            $sub = array();
            $dim = $globals->asso('diminutif');
            $sub['présentation'] = "$dim/";
            if (may_update() || $globals->asso('pub') == 'public') {
                $sub['annuaire du groupe'] = "$dim/annuaire";
                if ($globals->xnet->geoloc)
                    $sub['carte'] = "$dim/geoloc";
            }
            if ($globals->asso('mail_domain')) {
                $sub['listes de diffusion'] = "$dim/lists";
                $sub['envoyer un mail']     = "$dim/mail";
            }
            $sub['événement'] = "$dim/events";
            $sub['télépaiement'] = "$dim/paiement";

            $menu[$globals->asso('nom')] = $sub;
        }

        if (S::logged() && may_update()) {
            $sub = array();
            $sub['modifier l\'accueil'] = "$dim/edit";
            if ($globals->asso('mail_domain')) {
                $sub['créer une liste']     = "$dim/lists/create";
                $sub['créer un alias']      = "$dim/alias/create";
            }
            if (S::has_perms()) {
                $sub['gérer les groupes'] = 'admin';
            }
            $menu['Administrer'] = $sub;
        } elseif (S::has_perms()) {
            $sub = array();
            $sub['gérer les groupes'] = 'admin';
            $menu['Administrer'] = $sub;
        }

        $this->assign('menu', $menu);
    }

    // }}}
}

// {{{  function list_all_my_groups

function list_all_my_groups($params)
{
    if (!S::logged()) {
        return;
    }
    $res = XDB::iterRow(
            "SELECT  a.nom, a.diminutif
               FROM  groupex.asso    AS a
         INNER JOIN  groupex.membres AS m ON m.asso_id = a.id
              WHERE  m.uid={?}", S::v('uid'));
    $html = '<div>Mes groupes :</div>';
    while (list($nom, $mini) = $res->next()) {
        $html .= "<a class='gp' href='$mini/'>&bull; $nom</a>";
    }
    return $html;
}

// }}}
// {{{ cat_pp

function cat_pp($cat)
{
    $trans = array(
        'groupesx' => 'Groupes X' ,
        'binets'   => 'Binets' ,
        'institutions' => 'Institutions' ,
        'promotions' => 'Promotions'
    );

    return $trans[strtolower($cat)];
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
