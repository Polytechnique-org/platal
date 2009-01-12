<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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

class AuthModule extends PLModule
{
    function handlers()
    {
        return array(
            'groupex/donne-chall.php'
                                => $this->make_hook('chall',      AUTH_PUBLIC),
            'groupex/export-econfiance.php'
                                => $this->make_hook('econf',      AUTH_PUBLIC, 'user', NO_HTTPS),

            'webservices/manageurs.php'
                                => $this->make_hook('manageurs',  AUTH_PUBLIC, 'user', NO_HTTPS),

            'auth-redirect.php' => $this->make_hook('redirect',   AUTH_COOKIE),
            'auth-groupex.php'  => $this->make_hook('groupex_old',    AUTH_COOKIE),
            'auth-groupex'      => $this->make_hook('groupex',    AUTH_COOKIE),
            'admin/auth-groupes-x'         => $this->make_hook('admin_authgroupesx', AUTH_MDP, 'admin'),
        );
    }

    function handler_chall(&$page)
    {
        $_SESSION["chall"] = uniqid(rand(), 1);
        echo $_SESSION["chall"] . "\n" . session_id();
        exit;
    }

    function handler_econf(&$page)
    {
        global $globals;

        $cle = $globals->core->econfiance;

        $res = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n\n<membres>\n\n";

        if (S::v('chall') && $_GET['PASS'] == md5(S::v('chall').$cle)) {
            $list = new MMList(User::getWithUID(10154), "x-econfiance.polytechnique.org");
            $members = $list->get_members('membres');
            if (is_array($members)) {
                $membres = Array();
                foreach($members[1] as $member) {
                    $user = User::getSilent($member[1]);
                    if ($user && $user->hasProfile()) {
                        $profile = $user->profile();
                        $res .= "<membre>\n";
                        $res .= "\t<nom>" . $profile->lastName() . "</nom>\n";
                        $res .= "\t<prenom>" . $profile->firstName() . "</prenom>\n";
                        $res .= "\t<email>" . $user->forlifeEmail() . "</email>\n";
                        $res .= "</membre>\n\n";
                    }
                }
            }
            $res .= "</membres>\n\n";

            header('Content-Type: text/xml; charset="UTF-8"');
            echo $res;
        }
        exit;
    }

    function handler_manageurs(&$page)
    {
        global $globals;

        require_once 'webservices/manageurs.server.inc.php';

        $ips = array_flip(explode(' ', $globals->manageurs->authorized_ips));
        if ($ips && isset($ips[$_SERVER['REMOTE_ADDR']])) {
            $server = xmlrpc_server_create();

            xmlrpc_server_register_method($server, 'get_annuaire_infos', 'get_annuaire_infos');
            xmlrpc_server_register_method($server, 'get_nouveau_infos', 'get_nouveau_infos');

            $request  = @$GLOBALS['HTTP_RAW_POST_DATA'];
            $response = xmlrpc_server_call_method($server, $request, null);
            header('Content-Type: text/xml');
            print $response;
            xmlrpc_server_destroy($server);
        }

        exit;
    }

    function handler_redirect(&$page)
    {
        http_redirect(Env::v('dest', '/'));
    }

    function handler_groupex_old(&$page)
    {
        return $this->handler_groupex($page, 'iso-8859-1');
    }

    function handler_groupex(&$page, $charset = 'utf8')
    {
        $this->load('auth.inc.php');
        $page->assign('referer', true);

        $gpex_pass = $_GET["pass"];
        $gpex_url  = urldecode($_GET["url"]);
        if (strpos($gpex_url, '?') === false) {
            $gpex_url .= "?PHPSESSID=" . $_GET["session"];
        } else {
            $gpex_url .= "&PHPSESSID=" . $_GET["session"];
        }

        /* a-t-on besoin d'ajouter le http:// ? */
        if (!preg_match("/^(http|https):\/\/.*/",$gpex_url)) {
            $gpex_url = "http://$gpex_url";
        }
        $gpex_challenge = $_GET["challenge"];

        // mise à jour de l'heure et de la machine de dernier login sauf quand on est en suid
        $uid = S::i('uid');
        if (!S::suid()) {
            global $platal;
            S::logger($uid)->log('connexion_auth_ext', $platal->path);
        }

        /* on parcourt les entrees de groupes_auth */
        $res = XDB::iterRow('SELECT privkey, name, datafields, returnurls FROM groupesx_auth');

        while (list($privkey,$name,$datafields,$returnurls) = $res->next()) {
            if (md5($gpex_challenge.$privkey) == $gpex_pass) {
                $returnurls = trim($returnurls);
                if (empty($returnurls) || @preg_match($returnurls, $gpex_url)) {
                    $returl = $gpex_url . gpex_make_params($gpex_challenge, $privkey, $datafields, $charset);
                    http_redirect($returl);
                }
            }
        }

        /* si on n'a pas trouvé, on renvoit sur x.org */
        http_redirect('https://www.polytechnique.org/');
    }

    function handler_admin_authgroupesx(&$page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Auth groupes X');
        $page->assign('title', 'Gestion de l\'authentification centralisée');
        $table_editor = new PLTableEditor('admin/auth-groupes-x','groupesx_auth','id');
        $table_editor->describe('name','nom',true);
        $table_editor->describe('privkey','clé privée',false);
        $table_editor->describe('datafields','champs renvoyés',true);
        $table_editor->describe('returnurls','urls de retour',true);
        $table_editor->apply($page, $action, $id);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
