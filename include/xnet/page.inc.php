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

class XnetPage extends PlatalPage
{
    var $nomenu = false;

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
        if (!$this->nomenu) {
            $this->useMenu();
        }
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

        $sub = array();
        $sub['liste des groupes'] = 'plan';
        $sub['documentation']     = 'Xnet';
        $sub['Signaler un bug']   = array('link' => 'send_bug', 'onclick' => 'send_bug();return false'); 
        $menu["no_title"]   = $sub;
        
        if (S::logged() && $globals->asso()) {
            $sub = array();
            $dim = $globals->asso('diminutif');
            $sub['présentation'] = "$dim/";
            if (may_update() || (is_member()  && $globals->asso('pub') == 'public')) {
                $sub['annuaire du groupe'] = "$dim/annuaire";
                $sub['trombinoscope'] = "$dim/trombi";
                $sub['carte'] = "$dim/geoloc";
            }
            if ((is_member() || may_update()) && $globals->asso('mail_domain')) {
                $sub['listes de diffusion'] = "$dim/lists";
            }
            $sub['événement'] = "$dim/events";
            if (may_update() || is_member()) {
                $sub['télépaiement'] = "$dim/paiement";
            }

            $menu[$globals->asso('nom')] = $sub;
        }

        if (S::logged() && may_update()) {
            $sub = array();
            $sub['modifier l\'accueil'] = "$dim/edit";
            $sub['gérer les annonces'] = "$dim/admin/announces";
            if ($globals->asso('mail_domain')) {
                $sub['envoyer un mail']     = "$dim/mail";
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
    $html = '<div>Mes groupes (<a href="exit">déconnexion</a>) :</div>';
    while (list($nom, $mini) = $res->next()) {
        $html .= "<span class='gp'>&bull; <a href='$mini/'>$nom</a></span>";
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
