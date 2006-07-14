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

class XnetGrpModule extends PLModule
{
    function handlers()
    {
        return array(
            'grp'             => $this->make_hook('index', AUTH_PUBLIC),
            'grp/asso.php'    => $this->make_hook('index', AUTH_PUBLIC),
            'grp/logo'        => $this->make_hook('logo',  AUTH_PUBLIC),
        );
    }

    function handler_index(&$page)
    {
        global $globals;

        if (!$globals->asso('id')) {
            return PL_NOT_FOUND;
        }

        $page->changeTpl('xnet/groupe/asso.tpl');
        $page->useMenu();
        $page->setType($globals->asso('cat'));
        $page->assign('is_member', is_member());
        $page->assign('logged', logged());

        $page->assign('asso', $globals->asso());
    }

    function handler_logo(&$page)
    {
        global $globals;

        $res = $globals->xdb->query("SELECT logo, logo_mime
                                       FROM groupex.asso WHERE id = {?}",
                                    $globals->asso('id'));
        list($logo, $logo_mime) = $res->fetchOneRow();

        if (!empty($logo)) {
            header("Content-type: $mime");
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified:' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            echo $logo;
        } else {
            header('Content-type: image/jpeg');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified:' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            readfile(dirname(__FILE__).'/../htdocs.net/images/dflt_carre.jpg');
        }

        exit;
    }
}

?>
