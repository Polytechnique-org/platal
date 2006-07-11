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

function bugize($list)
{
    $list = split(',', $list);
    $ans  = array();

    foreach ($list as $bug) {
        $clean = str_replace('#', '', $bug);
        $ans[] = "<a href='http://trackers.polytechnique.org/task/$clean'>$bug</a>";
    }

    return join(',', $ans);
}


class CoreModule extends PLModule
{
    function handlers()
    {
        return array(
            '403'         => $this->make_hook('403', AUTH_PUBLIC),
            '404'         => $this->make_hook('404', AUTH_PUBLIC),
            'exit'        => $this->make_hook('exit', AUTH_PUBLIC),
            'cacert.pem'  => $this->make_hook('cacert', AUTH_PUBLIC),
            'changelog'   => $this->make_hook('changelog', AUTH_PUBLIC),
            'purge_cache' => $this->make_hook('purge_cache', AUTH_COOKIE, 'admin')
        );
    }

    function handler_index(&$page)
    {
        if (logged()) {
            redirect("events");
        }

        return PL_OK;
    }

    function handler_cacert(&$page)
    {
        $data = file_get_contents('/etc/ssl/xorgCA/cacert.pem');
        header('Content-Type: application/x-x509-ca-cert');
        header('Content-Length: '.strlen($data));
        echo $data;
        exit;
    }

    function handler_changelog(&$page)
    {
        $page->changeTpl('changeLog.tpl');

        $clog = htmlentities(file_get_contents(dirname(__FILE__).'/../ChangeLog'));
        $clog = preg_replace('!(#[0-9]+(,[0-9]+)*)!e', 'bugize("\1")', $clog);
        $page->assign('ChangeLog', $clog);
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
                redirect("events");
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

    function handler_purge_cache(&$page)
    {
        require_once 'wiki.inc.php';

        $page->clear_compiled_tpl();
        wiki_clear_all_cache();

        redirect(empty($_SERVER['HTTP_REFERER']) ? './' : $_SERVER['HTTP_REFERER']);
    }
}

?>
