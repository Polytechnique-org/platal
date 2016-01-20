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

class XnetPage extends PlPage
{
    public $nomenu = false;

    // {{{ function XnetPage()

    public function __construct()
    {
        global $globals;
        parent::__construct();

        $this->register_function('list_all_my_groups', 'list_all_my_groups');
        $this->register_modifier('cat_pp', 'cat_pp');
        $this->assign('it_is_xnet', true);

        global $globals;
        $this->assign('is_logged', S::logged());
        if ($globals->asso('id')) {
            $this->assign('asso', $globals->asso());
            $this->setType($globals->asso('cat'));
            $this->assign('is_admin', may_update());
            $this->assign('is_member', is_member());
        }
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
            $this->addJsLink('json2.js');
        }
        $this->addJsLink('jquery.xorg.js');
        $this->addJsLink('overlib.js');
        $this->addJsLink('core.js');
        $this->addJsLink('xorg.js');
        if ($globals->core->sentry_js_dsn) {
            $this->addJsLink('raven.min.js');
        }
        $this->setTitle('Les associations polytechniciennes');
    }

    // }}}
    // {{{ function run()

    public function run()
    {
        if (!$this->nomenu) {
            $this->useMenu();
        } else {
            $this->assign('menu', false);
        }
        $this->_run('xnet/skin.tpl');
    }

    // }}}
    // {{{ function setType

    public function setType($type)
    {
        $this->assign('xnet_type', strtolower($type));
    }

    // }}}
    // {{{ function useMenu

    private function useMenu()
    {
        global $globals;

        $menu = array();

        $sub = array();
        $sub['tous les groupes'] = 'plan';
        $sub['documentation']     = 'Xnet';
        if (S::user()->type == 'xnet') {
            $sub['mon compte'] = 'edit';
            $sub['mes préférences'] = $globals->xnet->xorg_baseurl . 'prefs';
        }
        $sub['signaler un bug']   = array('href' => 'send_bug/'.$_SERVER['REQUEST_URI'], 'class' => 'popup_840x600');
        $menu["no_title"]   = $sub;

        $perms = S::v('perms');
        $dim = $globals->asso('diminutif');
        if (S::logged() && $globals->asso()) {
            $sub = array();
            $sub['présentation'] = "login/$dim/";
            if ($perms->hasFlag('groupannu')) {
                $sub['annuaire du groupe'] = "$dim/annuaire";
                $sub['trombinoscope'] = "$dim/trombi";
            }
            if ($globals->asso('forum')) {
                $sub['forum'] = "$dim/forum";
            }
            if ($perms->hasFlag('groupmember')) {
                if ($globals->asso('mail_domain')) {
                    $sub['listes de diffusion'] = "$dim/lists";
                }
                if ($globals->asso('has_nl')) {
                    $sub['newsletter'] = "$dim/nl";
                }
            }
            $sub['événement'] = "$dim/events";
            if ($perms->hasFlag('groupadmin')) {
                $sub['télépaiement'] = "$dim/payment";
            }

            $menu[$globals->asso('nom')] = $sub;
        }

        if ($globals->asso() && is_object($perms) && $perms->hasFlag('groupadmin')) {
            $sub = array();
            $sub['modifier l\'accueil'] = "$dim/edit";
            $sub['gérer les annonces'] = "$dim/admin/announces";
            if ($globals->asso('mail_domain')) {
                if (!$globals->asso('disable_mails')) {
                    $sub['envoyer un mail']     = "$dim/mail";
                }
                $sub['créer une liste']     = "$dim/lists/create";
                $sub['créer un alias']      = "$dim/alias/create";
            }
            if (!$globals->asso('has_nl')) {
                $sub['créer la newsletter'] = "$dim/admin/nl/enable";
            }
            if (S::admin()) {
                $sub['gérer les groupes'] = array('href' => 'admin', 'style' => 'color: gray;');
                $sub['clear cache'] = array('href' => 'purge_cache?token=' . S::v('xsrf_token'), 'style' => 'color: gray;');
            }
            $menu['Administrer'] = $sub;
        } elseif (S::admin()) {
            $sub = array();
            $sub['gérer les groupes'] = 'admin';
            $sub['clear cache'] = 'purge_cache?token=' . S::v('xsrf_token');
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
    $res = XDB::iterRow('SELECT  a.nom, a.diminutif
                           FROM  groups    AS a
                     INNER JOIN  group_members AS m ON m.asso_id = a.id
                          WHERE  m.uid = {?}', S::i('uid'));
    $links = '<a href="exit">déconnexion</a>';
    $html = '<div>Mes groupes (' . $links . ') :</div>';
    while (list($nom, $mini) = $res->next()) {
        $html .= "<span class='gp'>&bull; <a href='login/$mini'>$nom</a></span>";
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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
