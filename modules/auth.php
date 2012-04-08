<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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
            'groupex/donne-chall.php'       => $this->make_hook('chall',              AUTH_PUBLIC),
            'groupex/export-econfiance.php' => $this->make_hook('econf',              AUTH_PUBLIC, 'user', NO_HTTPS),

            'webservices/manageurs.php'     => $this->make_hook('manageurs',          AUTH_PUBLIC, 'user', NO_HTTPS),

            'auth-redirect.php'             => $this->make_hook('redirect',           AUTH_COOKIE, 'user'),
            'auth-groupex.php'              => $this->make_hook('groupex_old',        AUTH_COOKIE, ''),
            'auth-groupex'                  => $this->make_hook('groupex',            AUTH_PUBLIC, ''),
            'admin/auth-groupes-x'          => $this->make_hook('admin_authgroupesx', AUTH_PASSWD, 'admin'),
        );
    }

    function handler_chall($page)
    {
        $_SESSION["chall"] = uniqid(rand(), 1);
        echo $_SESSION["chall"] . "\n" . session_id();
        exit;
    }

    function handler_econf($page)
    {
        global $globals;

        $cle = $globals->core->econfiance;

        $res = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n\n<membres>\n\n";

        if (S::v('chall') && Get::s('PASS') == md5(S::v('chall').$cle)) {
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

            pl_content_headers("text/xml");
            echo $res;
        }
        exit;
    }

    function handler_manageurs($page)
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
            pl_content_headers("text/xml");
            print $response;
            xmlrpc_server_destroy($server);
        }

        exit;
    }

    function handler_redirect($page)
    {
        http_redirect(Env::v('dest', '/'));
    }

    function handler_groupex_old($page)
    {
        return $this->handler_groupex($page, 'iso-8859-1');
    }

    /** Handles the 'auth-groupe-x' authentication.
     * Expects the following GET parameters:
     * - pass: the 'password' for the authentication
     * - challenge: the authentication challenge
     * - url: the return URL
     * - session: the remote PHP session ID
     */
    function handler_groupex($page, $charset = 'utf8')
    {
        $gpex_url = urldecode(Get::s('url'));

        if (!S::logged()) {
            $page->assign('external_auth', true);
            $page->assign('gpex_url', $gpex_url);
            $page->setTitle('Authentification');
            $page->setDefaultSkin('group_login');

            if (Get::has('group')) {
                $res = XDB::query('SELECT  nom
                                     FROM  groups
                                    WHERE  diminutif = {?}', Get::s('group'));
                $page->assign('group', $res->fetchOneCell());
            } else {
                $page->assign('group', null);
            }
            return PL_DO_AUTH;
        }

        if (!S::user()->checkPerms('groups')) {
            return PL_FORBIDDEN;
        }

        $this->load('auth.inc.php');

        $gpex_pass = Get::s('pass');
        if (Get::has('session')) {
            if (strpos($gpex_url, '?') === false) {
                $gpex_url .= "?PHPSESSID=" . Get::s('session');
            } else {
                $gpex_url .= "&PHPSESSID=" . Get::s('session');
            }
        }

        // Normalize the return URL.
        if (!preg_match("/^(http|https):\/\/.*/",$gpex_url)) {
            $gpex_url = "http://$gpex_url";
        }
        $gpex_challenge = Get::s('challenge');

        // Update the last login information (unless the user is in SUID).
        $uid = S::i('uid');
        if (!S::suid()) {
            global $platal;
            S::logger($uid)->log('connexion_auth_ext', $platal->path.' '.urldecode($_GET['url']));
        }

        if (Get::has('group')) {
            $req_group_id = XDB::fetchOneCell('SELECT  id
                                                 FROM  groups
                                                WHERE  diminutif = {?}',
                                              Get::s('group'));
        } else {
            $req_group_id = null;
        }

        // Iterate over the auth token to find which one did sign the request.
        $res = XDB::iterRow(
            'SELECT  ga.privkey, ga.name, ga.datafields, ga.returnurls,
                     ga.group_id, ga.flags, g.nom
               FROM  group_auth AS ga
          LEFT JOIN  groups AS g ON (g.id = ga.group_id)');

        while (list($privkey, $name, $datafields, $returnurls, $group_id, $group_flags, $group_name) = $res->next()) {
            if (md5($gpex_challenge.$privkey) == $gpex_pass) {
                $returnurls = trim($returnurls);
                // We check that the return url matches a per-key regexp to prevent
                // replay attacks (more exactly to force replay attacks to redirect
                // the user to the real GroupeX website, which defeats the attack).
                if (empty($returnurls) || @preg_match($returnurls, $gpex_url)) {
                    $returl = $gpex_url . gpex_make_params($gpex_challenge, $privkey, $datafields, $charset);
                    XDB::execute('UPDATE  group_auth
                                     SET  last_used = DATE(NOW())
                                   WHERE  name = {?}',
                                 $name);

                    // Two conditions control access to the return URL:
                    // - If that URL is attached to a group:
                    //   - If the user belongs to the group, OK
                    //   - If the user is 'xnet' and doesn't belong, NOK
                    //   - If the user is not 'xnet' and the group is not 'strict', OK
                    //   - If the user is not 'xnet' and the group is 'strict', NOK
                    // - Otherwise, all but 'xnet' accounts may access the URL.
                    $user_is_xnet = S::user()->type == 'xnet';
                    $group_flags = new PlFlagSet($group_flags);

                    // If this key is not attached to a group, but a group was
                    // requested (e.g query from wiki / blogs / ...), use the
                    // requested group_id.
                    if (!$group_id && $req_group_id) {
                        $group_id = $req_group_id;
                    }

                    if ($group_id) {
                        // Check group permissions
                        $is_member = XDB::fetchOneCell('SELECT  COUNT(*)
                                                          FROM  group_members
                                                         WHERE  uid = {?} AND asso_id = {?}',
                                                         S::user()->id(), $group_id);
                        if (!$is_member && ($user_is_xnet || $group_flags->hasFlag('group_only'))) {
                            $page->kill("Le site demandé est réservé aux membres du groupe $group_name.");
                        }

                    } else if ($user_is_xnet && !$group_flags->hasFlag('allow_xnet')) {
                        $page->kill("Le site demandé est réservé aux polytechniciens.");
                    }

                    // If we logged in specifically for this 'external_auth' request
                    // and didn't want to "keep access to services", we kill the session
                    // just before returning.
                    // See classes/xorgsession.php:startSessionAs
                    if (S::b('external_auth_exit')) {
                        S::logger()->log('decconnexion', @$_SERVER['HTTP_REFERER']);
                        Platal::session()->killAccessCookie();
                        Platal::session()->destroy();
                    }
                    http_redirect($returl);
                } else if (S::admin()) {
                    $page->kill("La requête d'authentification a échoué (url de retour invalide).");
                }
            }
        }

        // Otherwise (if no valid request was found, or if the return URL is not
        // acceptable), the user is redirected back to our homepage.
        pl_redirect('/');
    }

    function handler_admin_authgroupesx($page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Auth groupes X');
        $page->assign('title', 'Gestion de l\'authentification centralisée');
        $table_editor = new PLTableEditor('admin/auth-groupes-x','group_auth','id');
        $table_editor->describe('name','nom',true);
        $table_editor->describe('privkey','clé privée',false, true);
        $table_editor->describe('datafields','champs renvoyés',true);
        $table_editor->describe('returnurls','urls de retour',true);
        $table_editor->describe('last_used', 'dernière utilisation', true);
        $table_editor->apply($page, $action, $id);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
