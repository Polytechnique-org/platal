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

class XorgSession
{
    // {{{ public static function init

    public static function init() {
        S::init();
	if (!S::has('uid')) {
	    try_cookie();
        }
    }

    // }}}
    // {{{ public static function destroy()

    public static function destroy() {
        S::destroy();
        XorgSession::init();
    }

    // }}}
    // {{{ public static function doAuth()

    public static function doAuth($new_name = false)
    {
    	global $globals;
    	if (S::identified()) { // ok, c'est bon, on n'a rien à faire
    	    return true;
    	}

        if (!Env::has('username') || !Env::has('response')
        ||  !S::has('challenge'))
        {
            return false;
        }

        // si on vient de recevoir une identification par passwordpromptscreen.tpl
        // ou passwordpromptscreenlogged.tpl
        $uname = Env::v('username');

        if (Env::v('domain') == "alias") {

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

        $logger =& S::v('log');
        if (list($uid, $password) = $res->fetchOneRow()) {
                require_once('secure_hash.inc.php');
                    $expected_response=hash_encrypt("$uname:$password:".S::v('challenge'));
                    // le password de la base est peut-être encore encodé en md5
                    if (Env::v('response') != $expected_response) {
                      $new_password = hash_xor(Env::v('xorpass'), $password);
                      $expected_response = hash_encrypt("$uname:$new_password:".S::v('challenge'));
                      if (Env::v('response') == $expected_response) {
                          XDB::execute("UPDATE auth_user_md5 SET password = {?} WHERE user_id = {?}", $new_password, $uid);
                      }
                    }
                    if (Env::v('response') == $expected_response) {
                if (Env::has('domain')) {
                    if (($domain = Env::v('domain', 'login')) == 'alias') {
                        setcookie('ORGdomain', "alias", (time()+25920000), '/', '', 0);
                    } else {
                        setcookie('ORGdomain', '', (time()-3600), '/', '', 0);
                    }
                    // pour que la modification soit effective dans le reste de la page
                    $_COOKIE['ORGdomain'] = $domain;
                }

                S::kill('challenge');
                if ($logger) {
                    $logger->log('auth_ok');
                }
                        start_connexion($uid, true);
                if (Env::v('remember', 'false') == 'true') {
                    $cookie = hash_encrypt(S::v('password'));
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

        return false;
    }

    // }}}
    // {{{ public static function doAuthCookie()

    /** Try to do a cookie-based authentication.
     *
     * @param page the calling page (by reference)
     */
    public static function doAuthCookie()
    {
	if (S::logged()) {
	    return true;
        }

	if (Env::has('username') and Env::has('response')) {
	    return XorgSession::doAuth();
        }

	if ($r = try_cookie()) {
	    return XorgSession::doAuth(($r > 0));
        }

        return false;
    }

    // }}}
}

// {{{ function try_cookie()

/** réalise la récupération de $_SESSION pour qqn avec cookie
 * @return  int     0 if all OK, -1 if no cookie, 1 if cookie with bad hash,
 *                  -2 should not happen
 */
function try_cookie()
{
    if (Cookie::v('ORGaccess') == '' or !Cookie::has('ORGuid')) {
	return -1;
    }

    $res = @XDB::query(
            "SELECT user_id,password FROM auth_user_md5 WHERE user_id = {?} AND perms IN('admin','user')",
            Cookie::i('ORGuid')
    );
    if ($res->numRows() != 0) {
	list($uid, $password) = $res->fetchOneRow();
	require_once('secure_hash.inc.php');
	$expected_value       = hash_encrypt($password);
	if ($expected_value == Cookie::v('ORGaccess')) {
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
    $suid = S::v('suid');

    if ($suid) {
	$logger = new CoreLogger($uid, $suid);
	$logger->log("suid_start", S::v('forlife')." by {$suid['uid']}");
        $sess['suid'] = $suid;
    } else {
        global $platal;
        $logger = S::v('log', new CoreLogger($uid));
        $logger->log("connexion", $platal->path);
        setcookie('ORGuid', $uid, (time()+25920000), '/', '', 0);
    }

    $_SESSION         = $sess;
    $_SESSION['log']  = $logger;
    $_SESSION['auth'] = ($identified ? AUTH_MDP : AUTH_COOKIE);
    set_skin();
}

// }}}

function set_skin()
{
    global $globals;
    if (S::logged() && !S::has('skin')) {
        $uid = S::v('uid');
	$res = XDB::query("SELECT  skin_tpl
                             FROM  auth_user_quick AS a
                       INNER JOIN  skins           AS s ON a.skin = s.id
                            WHERE  user_id = {?} AND skin_tpl != ''", $uid);
	if ($_SESSION['skin'] = $res->fetchOneCell()) {
            return;
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
