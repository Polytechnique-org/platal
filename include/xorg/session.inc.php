<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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

require_once("diogenes.core.session.inc.php");
require_once("diogenes.misc.inc.php");

// {{{ class XorgSession

class XorgSession extends DiogenesCoreSession
{
    // {{{ function XorgSession()

    function XorgSession()
    {
	$this->DiogenesCoreSession();
	if (!Session::has('uid')) {
	    try_cookie();
        }
	set_skin();
    }

    // }}}
    // {{{ function init
    
    function init() {
        @session_start();
        if (!Session::has('session')) {
            $_SESSION['session'] = new XorgSession;
        }
    }
    
    // }}}
    // {{{ function destroy()
    
    function destroy() {
        @session_destroy();
        unset($_SESSION);
        XorgSession::init();
    }
    
    // }}}
    // {{{ function doAuth()

    /** Try to do an authentication.
     *
     * @param page the calling page (by reference)
     */
    function doAuth(&$page,$new_name=false)
    {
	global $globals;
	if (identified()) { // ok, c'est bon, on n'a rien à faire
	    return true;
	}

        if (Session::has('session')) {
            $session =& Session::getMixed('session');
        }

	if (Env::has('username') && Env::has('response') && isset($session->challenge))
	{
	    // si on vient de recevoir une identification par passwordpromptscreen.tpl
	    // ou passwordpromptscreenlogged.tpl
            $uname = Env::get('username');
	    $field = preg_match('/^\d*$/', $uname) ? 'id' : 'alias';
	    $res   = $globals->xdb->query(
                    "SELECT  u.user_id, u.password
                       FROM  auth_user_md5 AS u
                 INNER JOIN  aliases       AS a ON ( a.id=u.user_id AND type!='homonyme' )
                      WHERE  a.$field = {?} AND u.perms IN('admin','user')", $uname);

            $logger =& Session::getMixed('log');

	    if (list($uid, $password) = $res->fetchOneRow()) {
		$expected_response=md5("$uname:$password:{$session->challenge}");
		if (Env::get('response') == $expected_response) {
		    unset($session->challenge);
		    if ($logger) {
			$logger->log('auth_ok');
                    }
		    start_connexion($uid, true);
                    if (Env::has('remember')) {
                        $cookie = md5(Session::get('password'));
                        setcookie('ORGaccess',$cookie,(time()+25920000),'/','',0);
                        if ($logger) {
                            $logger->log("cookie_on");
                        }
                    }
		    return true;
		} elseif ($logger) {
                    $logger->log('auth_fail','bad password');
                }
	    } elseif ($logger) {
                $logger->log('auth_fail','bad login');
            }
	}
        $this->doLogin($page,$new_name);
    }

    // }}}
    // {{{ function doAuthCookie()

    /** Try to do a cookie-based authentication.
     *
     * @param page the calling page (by reference)
     */
    function doAuthCookie(&$page)
    {
	global $failed_ORGaccess;
	// si on est deja connecté, c'est bon, rien à faire
	if (logged()) {
	    return;
        }

	// on vient de recevoir une demande d'auth, on passe la main a doAuth
	if (Env::has('username') and Env::has('response')) {
	    return $this->doAuth($page);
        }

	// sinon, on vérifie que les bons cookies existent
	if ($r = try_cookie()) {
	    return $this->doAuth($page,($r>0));
        }
    }

    // }}}
    // {{{ function doLogin()

    /** Display login screen.
     */
    function doLogin(&$page, $new_name=false)
    {
	if (logged() and !$new_name) {
	    $page->changeTpl('password_prompt_logged.tpl');
	    $page->caching = false;
	    $page->assign("xorg_head", "password_prompt_logged.head.tpl");
	    $page->assign("xorg_tpl", "password_prompt_logged.tpl");
	    $page->run();
	} else {
	    $page->changeTpl('password_prompt.tpl');
	    $page->caching = false;
	    $page->assign("xorg_head", "password_prompt.head.tpl");
	    $page->assign("xorg_tpl", "password_prompt.tpl");
	    $page->run();
	}
	exit;
    }

    // }}}
    // {{{ function getUserId()
    
    function getUserId($auth,$username)
    {
	global $globals;
	$res = $globals->xdb->query("SELECT id FROM aliases WHERE alias = {?}",$username);
        return $res->fetchOneCell();
    }

    // }}}
    // {{{ function getUsername()

    function getUsername($auth,$uid)
    {
	global $globals;
	$res = $globals->xdb->query("SELECT alias FROM aliases WHERE id = {?} AND type='a_vie'", $uid);
        return $res->fetchOneCell();
    }

    // }}}
}

// }}}
// {{{ function check_perms()

/** verifie si un utilisateur a les droits pour voir une page
 ** si ce n'est pas le cas, on affiche une erreur
 * @return void
 */
function check_perms()
{
    global $page;
    if (!has_perms()) {
	require_once("diogenes.core.logger.inc.php");
	$_SESSION['log']->log("noperms",$_SERVER['PHP_SELF']);
	$page->kill("Tu n'as pas les permissions nécessaires pour accéder à cette page.");
    }
}

// }}}
// {{{ function has_perms()

/** verifie si un utilisateur a les droits pour voir une page
 ** soit parce qu'il est admin, soit il est dans une liste
 ** supplementaire de personnes utilisées
 * @return BOOL
 */
    
function has_perms()
{
    return logged() && Session::get('perms')==PERMS_ADMIN;
}

// }}}
// {{{ function logged()

/** renvoie true si la session existe et qu'on est loggué correctement
 * false sinon
 * @return bool vrai si loggué
 * @see header2.inc.php
 */
function logged ()
{
    return Session::get('auth', AUTH_PUBLIC) >= AUTH_COOKIE;
}

// }}}
// {{{ function identified()

/** renvoie true si la session existe et qu'on est loggué correctement
 * et qu'on a été identifié par un mot de passe depuis le début de la session
 * false sinon
 * @return bool vrai si loggué
 * @see header2.inc.php
 */
function identified ()
{
    return Session::get('auth', AUTH_PUBLIC) >= AUTH_MDP;
}

// }}}
// {{{ function try_cookie()

/** réalise la récupération de $_SESSION pour qqn avec cookie
 * @return  int     0 if all OK, -1 if no cookie, 1 if cookie with bad hash,
 *                  -2 should not happen
 */
function try_cookie()
{
    global $globals;
    if (Cookie::get('ORGaccess') == '' or !Cookie::has('ORGuid')) {
	return -1;
    }

    $res = @$globals->xdb->query(
            "SELECT user_id,password FROM auth_user_md5 WHERE user_id = {?} AND perms IN('admin','user')",
            Cookie::getInt('ORGuid')
    );
    if ($res->numRows() != 0) {
	list($uid, $password) = $res->fetchOneRow();
	$expected_value       = md5($password);
	if ($expected_value == Cookie::get('ORGaccess')) {
	    start_connexion($uid, false);
	    return 0;
	} else {
            return 1;
        }
    }

    return -2;
}

// }}}
// {{{ function start_connexion()

/** place les variables de session dépendants de auth_user_md5
 * et met à jour les dates de dernière connexion si nécessaire
 * @return void
 * @see controlpermanent.inc.php controlauthentication.inc.php
 */
function start_connexion ($uid, $identified)
{
    global $globals;
    $res  = $globals->xdb->query("
	SELECT  u.user_id AS uid, prenom, nom, perms, promo, matricule, UNIX_TIMESTAMP(s.start) AS lastlogin, s.host,
                a.alias AS forlife, UNIX_TIMESTAMP(q.banana_last) AS banana_last, q.watch_last,
		a2.alias AS bestalias, password, FIND_IN_SET('femme', u.flags) AS femme
          FROM  auth_user_md5   AS u
    INNER JOIN  auth_user_quick AS q  USING(user_id)
    INNER JOIN	aliases         AS a  ON (u.user_id = a.id AND a.type='a_vie')
    INNER JOIN  aliases		AS a2 ON (u.user_id = a2.id AND FIND_IN_SET('bestalias',a2.flags))
     LEFT JOIN  logger.sessions AS s  ON (s.uid=u.user_id AND s.suid=0)
         WHERE  u.user_id = {?} AND u.perms IN('admin','user')
      ORDER BY  s.start DESC, !FIND_IN_SET('epouse', a2.flags), length(a2.alias)", $uid);
    $sess = $res->fetchOneAssoc();
    echo mysql_error();
    $suid = Session::getMixed('suid');
    
    if ($suid) {
	$logger = new DiogenesCoreLogger($uid, $suid);
	$logger->log("suid_start", Session::get('forlife')." by {$suid['uid']}");
        $sess['suid'] = $suid;
    } else {
        $logger = Session::getMixed('log', new DiogenesCoreLogger($uid));
        $logger->log("connexion", $_SERVER['PHP_SELF']);
        setcookie('ORGuid', $uid, (time()+25920000), '/', '', 0);
    }

    $_SESSION         = $sess;
    $_SESSION['log']  = $logger;
    $_SESSION['auth'] = ($identified ? AUTH_MDP : AUTH_COOKIE);
    set_skin();
}

// }}}
// {{{ function set_skin()

function set_skin()
{
    global $globals;
    if (logged() && $globals->skin->enable) {
        $uid = Session::getInt('uid');
	$res = $globals->xdb->query("SELECT  skin,skin_tpl
	                               FROM  auth_user_quick AS a
				 INNER JOIN  skins           AS s ON a.skin=s.id
			              WHERE  user_id = {?} AND skin_tpl != ''", $uid);
	if (list($_SESSION['skin_id'], $_SESSION['skin']) = $res->fetchOneRow()) {
            return;
        }
    }
    if ($globals->skin->enable) {
        $_SESSION['skin'] = $globals->skin->def_tpl;
        $_SESSION['skin_id'] = $globals->skin->def_id;
    } else {
        $_SESSION['skin'] = 'default.tpl';
        $_SESSION['skin_id'] = -1;
    }
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
