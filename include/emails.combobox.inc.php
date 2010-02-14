<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

function fill_email_combobox(PlPage& $page, $user = null, $profile = null)
{
    global $globals;

    if (is_null($user) && is_null($profile)) {
        $user = S::user();
        $profile = $user->profile();
    }
    $email_type = "directory";

    if ($profile) {
        $res = XDB::query(
                "SELECT  email_directory
                   FROM  profile_directory
                  WHERE  uid = {?}", $profile->id());
        $email_directory = $res->fetchOneCell();
        if ($email_directory) {
            $page->assign('email_directory', $email_directory);
            list($alias, $domain) = explode('@', $email_directory);
        } else {
            $page->assign('email_directory', '');
            $email_type = NULL;
            $alias = $domain = '';
        }
    }

    if ($user) {
        $melix = $user->emailAlias();
        if ($melix) {
            list($melix) = explode('@', $melix);
            $page->assign('melix', $melix);
            if (($domain == $globals->mail->alias_dom) || ($domain == $globals->mail->alias_dom2)) {
                $email_type = "melix";
            }
        }

        $res = XDB::query(
                "SELECT  alias
                   FROM  aliases
                  WHERE  id={?} AND (type='a_vie' OR type='alias')", $user->id());
        $res = $res->fetchAllAssoc();
        $page->assign('list_email_X', $res);
        if (($domain == $globals->mail->domain) || ($domain == $globals->mail->domain2)) {
            foreach ($res as $res_it) {
                if ($alias == $res_it['alias']) {
                    $email_type = "X";
                }
            }
        }

        require_once 'emails.inc.php';
        $redirect = new Redirect($user);
        $redir    = array();
        foreach ($redirect->emails as $redirect_it) {
            if ($redirect_it instanceof EmailRedirection) {
                $redir[] = $redirect_it->email;
                if ($email_directory == $redirect_it->email) {
                    $email_type = "redir";
                }
            }
        }
        $page->assign('list_email_redir', $redir);

        $res = XDB::query(
                "SELECT  email
                   FROM  profile_job
                  WHERE  uid = {?}", $user->id());
        $res = $res->fetchAllAssoc();
        $pro = array();
        foreach ($res as $res_it) {
            if ($res_it['email'] != '') {
                $pro[] = $res_it['email'];
                if ($email_directory == $res_it['email']) {
                    $email_type = "pro";
                }
            }
        }
        $page->assign('list_email_pro', $pro);
        $page->assign('email_type', $email_type);

    } else {
        $page->assign('list_email_X', array());
        $page->assign('list_email_redir', array());
        $page->assign('list_email_pro', array());
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
