<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

class XorgPage extends PlPage
{
    protected $forced_skin = null;
    protected $default_skin = null;

    public function __construct()
    {
        global $globals;
        parent::__construct();

        // Set the default page
        $this->changeTpl('platal/index.tpl');
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
        $this->setTitle('le site des élèves et anciens élèves de l\'École polytechnique');
        if (S::logged() && S::user()->checkPerms('admin')) {
            $types = array(S::user()->type);
            $perms = DirEnum::getOptions(DirEnum::ACCOUNTTYPES);
            ksort($perms);
            foreach ($perms as $type => $perm) {
                if (!empty($perm) && $type != $types[0]) {
                    $types[] = $type;
                }
            }
            $this->assign('account_types_list', $types);

            $skins = DirEnum::getOptions(DirEnum::SKINS);
            asort($skins);
            $this->assign('skin_list', $skins);
        }
    }

    /** Force the skin to use, bypassing user choice.
     * Typically used for the 'register' page.
     * @param $skin The skin to use.
     */
    public function forceSkin($skin)
    {
        $this->forced_skin = $skin;
    }

    /** Choose another 'default' skin.
     * Typically used for the 'Auth Groupe X' login page.
     * @param $skin The default skin to use.
     */
    public function setDefaultSkin($skin)
    {
        $this->default_skin = $skin;
    }

    public function run()
    {
        global $globals, $platal;
        if ($this->forced_skin !== null) {
            $skin = $this->forced_skin . '.tpl';
        } else {
            if ($this->default_skin === null) {
                $default_skin = $globals->skin;
            } else {
                $default_skin = $this->default_skin;
            }
            $skin = S::v('skin', $default_skin . '.tpl');
        }
        $this->_run('skin/' . $skin);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
