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

class CoreModule extends PLModule
{
    function menu_entries()
    {
        return array();
    }

    function handlers()
    {
        return array(
            '403'  => $this->make_hook('403', AUTH_PUBLIC),
            '404'  => $this->make_hook('404', AUTH_PUBLIC),
            'exit' => $this->make_hook('exit', AUTH_PUBLIC),
        );
    }

    function handler_exit(&$page, $level = null)
    {
        if (Session::has('suid')) {
            if (Session::has('suid')) {
                $a4l  = Session::get('forlife');
                $suid = Session::getMixed('suid');
                $log  = Session::getMixed('log');
                $log->log("suid_stop", Session::get('forlife') . " by " . $suid['forlife']);
                $_SESSION = $suid;
                Session::kill('suid');
                redirect($globals->baseurl.'/admin/utilisateurs.php?login='.$a4l);
            } else {
                redirect("login.php");
            }
        }

        if ($level == 'forget' || $level == 'forgetall') {
            setcookie('ORGaccess', '', time() - 3600, '/', '', 0);
            Cookie::kill('ORGaccess');
            if (isset($_SESSION['log']))
                $_SESSION['log']->log("cookie_off");
        }

        if ($level == 'forgetuid' || $level == 'forgetall') {
            setcookie('ORGuid', '', time() - 3600, '/', '', 0);
            Cookie::kill('ORGuid');
            setcookie('ORGdomain', '', time() - 3600, '/', '', 0);
            Cookie::kill('ORGdomain');
        }

        if (isset($_SESSION['log'])) {
            $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $_SESSION['log']->log('deconnexion',$ref);
        }

        XorgSession::destroy();

        if (Get::has('redirect')) {
            redirect(rawurldecode(Get::get('redirect')));
        } else {
            $page->changeTpl('exit.tpl');
        }
        return PL_OK;
    }

    function handler_403(&$page)
    {
        header('HTTP/1.0 403 Forbidden');
        $page->changeTpl('403.tpl');
        return PL_OK;
    }

    function handler_404(&$page)
    {
        header('HTTP/1.0 404 Not Found');
        $page->changeTpl('404.tpl');
        return PL_OK;
    }
}

?>
