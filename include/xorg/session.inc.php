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

require_once 'platal/session.inc.php';

// {{{ class XorgSession

class XorgSession
{
    var $challenge;

    // {{{ function XorgSession()

    function XorgSession()
    {
        $this->challenge = md5(uniqid(rand(), 1));

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
            
            if (Env::get('domain') == "alias") {
            
                $res = XDB::query(
                    "SELECT redirect
                       FROM virtual
                 INNER JOIN virtual_redirect USING(vid)
                      WHERE alias LIKE {?}", $uname."@".$globals->mail->alias_dom);
                $redirect = $res->fetchOneCell();
                if ($redirect) {
                    $login = substr($redirect, 0, strpos($redirect, '@'));
                } else {
                    $login = "";
                }
            } else {
                $login = $uname;
            }
    
    	    $field = (!$redirect && preg_match('/^\d*$/', $uname)) ? 'id' : 'alias';
    	    $res   = XDB::query(
                        "SELECT  u.user_id, u.password
                           FROM  auth_user_md5 AS u
                     INNER JOIN  aliases       AS a ON ( a.id=u.user_id AND type!='homonyme' )
                          WHERE  a.$field = {?} AND u.perms IN('admin','user')", $login);
    
            $logger =& Session::getMixed('log');
    	    if (list($uid, $password) = $res->fetchOneRow()) {
        	    require_once('secure_hash.inc.php');
        		$expected_response=hash_encrypt("$uname:$password:{$session->challenge}");
        		// le password de la base est peut-être encore encodé en md5
        		if (Env::get('response') != $expected_response) {
        		  $new_password = hash_xor(Env::get('xorpass'), $password);
        		  $expected_response = hash_encrypt("$uname:$new_password:{$session->challenge}");
        		  if (Env::get('response') == $expected_response) {
        		      XDB::execute("UPDATE auth_user_md5 SET password = {?} WHERE user_id = {?}", $new_password, $uid);
        		  }
        		}
        		if (Env::get('response') == $expected_response) {
                    if (Env::has('domain')) {
                        if (($domain = Env::get('domain', 'login')) == 'alias') {
                            setcookie('ORGdomain', "alias", (time()+25920000), '/', '', 0);
                        } else {
                            setcookie('ORGdomain', '', (time()-3600), '/', '', 0);
                        }
                        // pour que la modification soit effective dans le reste de la page
                        $_COOKIE['ORGdomain'] = $domain;
                    }
    
        		    unset($session->challenge);
        		    if ($logger) {
            			$logger->log('auth_ok');
                    }
        		    start_connexion($uid, true);
                    if (Env::get('remember', 'false') == 'true') {
                        $cookie = hash_encrypt(Session::get('password'));
                        setcookie('ORGaccess',$cookie,(time()+25920000),'/','',0);
                        if ($logger) {
                            $logger->log("cookie_on");
                        }
                    } else {
                        setcookie('ORGaccess', '', time() - 3600, '/', '', 0);
                        
                        if ($logger) {
                            $logger->log("cookie_off");
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
	if (logged()) {
	    return;
        }

	if (Env::has('username') and Env::has('response')) {
	    return $this->doAuth($page);
        }

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
            $page->addJsLink('javascript/do_challenge_response_logged.js');
            $page->assign("xorg_tpl", "password_prompt_logged.tpl");
            $page->run();
        } else {
            $page->changeTpl('password_prompt.tpl');
            $page->addJsLink('javascript/do_challenge_response.js');
            $page->assign("xorg_tpl", "password_prompt.tpl");
            
            global $globals;
            if ($globals->mail->alias_dom) {
                $page->assign("domains", Array(
                    $globals->mail->domain."/".$globals->mail->domain2,
                    $globals->mail->alias_dom."/".$globals->mail->alias_dom2));
                $page->assign("domains_value", Array("login", "alias"));
                $page->assign("r_domain", Cookie::get('ORGdomain', 'login'));
            }
	    $page->run();
    	}
    	exit;
    }

    // }}}
}

// }}}
// {{{ function try_cookie()

/** réalise la récupération de $_SESSION pour qqn avec cookie
 * @return  int     0 if all OK, -1 if no cookie, 1 if cookie with bad hash,
 *                  -2 should not happen
 */
function try_cookie()
{
    if (Cookie::get('ORGaccess') == '' or !Cookie::has('ORGuid')) {
	return -1;
    }

    $res = @XDB::query(
            "SELECT user_id,password FROM auth_user_md5 WHERE user_id = {?} AND perms IN('admin','user')",
            Cookie::getInt('ORGuid')
    );
    if ($res->numRows() != 0) {
	list($uid, $password) = $res->fetchOneRow();
	require_once('secure_hash.inc.php');
	$expected_value       = hash_encrypt($password);
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
    $res  = XDB::query("
	SELECT  u.user_id AS uid, prenom, nom, perms, promo, matricule, password, FIND_IN_SET('femme', u.flags) AS femme,
                UNIX_TIMESTAMP(s.start) AS lastlogin, s.host, a.alias AS forlife, a2.alias AS bestalias,
                q.core_mail_fmt AS mail_fmt, UNIX_TIMESTAMP(q.banana_last) AS banana_last, q.watch_last, q.core_rss_hash
          FROM  auth_user_md5   AS u
    INNER JOIN  auth_user_quick AS q  USING(user_id)
    INNER JOIN	aliases         AS a  ON (u.user_id = a.id AND a.type='a_vie')
    INNER JOIN  aliases		AS a2 ON (u.user_id = a2.id AND FIND_IN_SET('bestalias',a2.flags))
     LEFT JOIN  logger.sessions AS s  ON (s.uid=u.user_id AND s.suid=0)
         WHERE  u.user_id = {?} AND u.perms IN('admin','user')
      ORDER BY  s.start DESC
         LIMIT  1", $uid);
    $sess = $res->fetchOneAssoc();
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
	$res = XDB::query("SELECT  skin,skin_tpl
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
