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
 ***************************************************************************
        $Id: xorg.session.inc.php,v 1.39 2004-11-17 10:53:02 x2000habouzit Exp $
 ***************************************************************************/

require("diogenes.core.session.inc.php");
require("diogenes.misc.inc.php");

class XorgSession extends DiogenesCoreSession {
    function XorgSession()
    {
	$this->DiogenesCoreSession();
	if(empty($_SESSION['uid']))
	    try_cookie();
	set_skin();
    }

    /** Try to do an authentication.
     *
     * @param page the calling page (by reference)
     */
    function doAuth(&$page,$new_name=false) {
	global $globals;
	if(identified()) { // ok, c'est bon, on n'a rien à faire
	    return;
	}

	if (isset($_REQUEST['username']) and isset($_REQUEST['response'])
		and isset($_SESSION['session']->challenge))
	{
	    // si on vient de recevoir une identification par passwordpromptscreen.tpl
	    // ou passwordpromptscreenlogged.tpl
	    $field = preg_match('/^\d*$/', $_REQUEST['username']) ? 'id' : 'alias';
	    $res = @$globals->db->query( "SELECT  u.user_id,u.password
					    FROM  auth_user_md5 AS u
         			      INNER JOIN  aliases       AS a ON ( a.id=u.user_id AND type!='homonyme' )
				           WHERE  a.$field='{$_REQUEST['username']}'");
	    if(@mysql_num_rows($res) != 0) {
		list($uid,$password)=mysql_fetch_row($res);
		mysql_free_result($res);
		$expected_response=md5("{$_REQUEST['username']}:$password:{$_SESSION['session']->challenge}");
		if($_REQUEST['response'] == $expected_response) {
		    unset($_SESSION['session']->challenge);
		    // on logge la réussite pour les gens avec cookie
		    if(isset($_SESSION['log']))
			$_SESSION['log']->log("auth_ok");
		    start_connexion($uid, true);
		    return true;
		} else {
		    // mot de passe incorrect pour le login existant
		    // on logge l'échec pour les gens avec cookie
		    if(isset($_SESSION['log']))
			$_SESSION['log']->log("auth_fail","bad password");
		    $this->doLogin($page,$new_name);
		}
	    } else {
		// login inexistant dans la base de donnees
		// on logge l'échec pour les gens avec cookie
		if(isset($_SESSION['log']))
		    $_SESSION['log']->log("auth_fail","bad login");
		$this->doLogin($page,$new_name);
	    }
	} else {
	    // ni loggué ni tentative de login
	    $this->doLogin($page,$new_name);
	}
    }


    /** Try to do a cookie-based authentication.
     *
     * @param page the calling page (by reference)
     */
    function doAuthCookie(&$page) {
	global $failed_ORGaccess;
	// si on est deja connecté, c'est bon, rien à faire
	if(logged())
	    return;

	// on vient de recevoir une demande d'auth, on passe la main a doAuth
	if (isset($_REQUEST['username']) and isset($_REQUEST['response']))
	    return $this->doAuth($page);

	// sinon, on vérifie que les bons cookies existent
	if($r = try_cookie())
	    return $this->doAuth($page,($r>0));
    }

    /** Display login screen.
     */
    function doLogin(&$page, $new_name=false) {
	if(isset($_COOKIE['ORGaccess']) and isset($_COOKIE['ORGuid']) and !$new_name) {
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
    
    function getUserId($auth,$username) {
	global $globals;

	$res = $globals->db->query("SELECT id FROM aliases WHERE alias='$username'");
	list($uid) = mysql_fetch_row($res);
	mysql_free_result($res);
	return $uid;
    }


    function getUsername($auth,$uid) {
	global $globals;

	$res = $globals->db->query("SELECT alias FROM aliases WHERE id='$uid' AND type='a_vie'");
	list($username) = mysql_fetch_row($res);
	mysql_free_result($res);
	return $username;
    }
}

/** verifie si un utilisateur a les droits pour voir une page
 ** si ce n'est pas le cas, on affiche une erreur
 * @return void
 */
function check_perms() {
    global $page;
    if (!has_perms()) {
	require_once("diogenes.core.logger.inc.php");
	$_SESSION['log']->log("noperms",$_SERVER['PHP_SELF']);
	$page->failure();
    }
}

/** verifie si un utilisateur a les droits pour voir une page
 ** soit parce qu'il est admin, soit il est dans une liste
 ** supplementaire de personnes utilisées
 * @return BOOL
 */
    
function has_perms($auth_array=array()) {
    return logged()
	&& ( (!empty($auth_array) && in_array($_SESSION['uid'], $auth_array))
		|| ($_SESSION['perms']==PERMS_ADMIN) );
}


/** renvoie true si la session existe et qu'on est loggué correctement
 * false sinon
 * @return bool vrai si loggué
 * @see header2.inc.php
 */
function logged () {
    return(isset($_SESSION['auth']) and ($_SESSION['auth']>=AUTH_COOKIE));
}



/** renvoie true si la session existe et qu'on est loggué correctement
 * et qu'on a été identifié par un mot de passe depuis le début de la session
 * false sinon
 * @return bool vrai si loggué
 * @see header2.inc.php
 */
function identified () {
    return(isset($_SESSION['auth']) and $_SESSION['auth']>=AUTH_MDP);
}

/** réalise la récupération de $_SESSION pour qqn avec cookie
 * @return  int     0 if all OK, -1 if no cookie, 1 if cookie with bad hash,
 *                  -2 should not happen
 */
function try_cookie() {
    global $globals;
    if(!isset($_COOKIE['ORGaccess']) or $_COOKIE['ORGaccess'] == '' or !isset($_COOKIE['ORGuid']))
	return -1;

    $res = @$globals->db->query( "SELECT user_id,password FROM auth_user_md5 WHERE user_id='{$_COOKIE['ORGuid']}'");
    if(@mysql_num_rows($res) != 0) {
	list($uid,$password)=mysql_fetch_row($res);
	mysql_free_result($res);
	$expected_value=md5($password);
	if($expected_value == $_COOKIE['ORGaccess']) {
	    start_connexion($uid, false);
	    return 0;
	} else return 1;
    }
    return -2;
}

/** place les variables de session dépendants de auth_user_md5
 * et met à jour les dates de dernière connexion si nécessaire
 * @return void
 * @see controlpermanent.inc.php controlauthentication.inc.php
 */
function start_connexion ($uid, $identified) {
    global $globals;
    $result=$globals->db->query("
	SELECT  prenom, nom, perms, promo, matricule, UNIX_TIMESTAMP(s.start) AS lastlogin, s.host, a.alias,
		UNIX_TIMESTAMP(q.lastnewslogin), q.watch_last,
		a2.alias, password, FIND_IN_SET('femme', u.flags)
          FROM  auth_user_md5   AS u
    INNER JOIN  auth_user_quick AS q  USING(user_id)
    INNER JOIN	aliases         AS a  ON (u.user_id = a.id AND a.type='a_vie')
    INNER JOIN  aliases		AS a2 ON (u.user_id = a2.id AND FIND_IN_SET('bestalias',a2.flags))
     LEFT JOIN  logger.sessions AS s  ON (s.uid=u.user_id AND s.suid=0)
         WHERE  u.user_id=$uid
      ORDER BY  s.start DESC, !FIND_IN_SET('epouse', a2.flags), length(a2.alias)");
    list($prenom, $nom, $perms, $promo, $matricule, $lastlogin, $host, $forlife, 
	 $lastnewslogin, $watch_last,
	 $bestalias, $password, $femme) = mysql_fetch_row($result);
    mysql_free_result($result);
   
    // on garde le logger si il existe (pour ne pas casser les sessions lors d'une
    // authentification avec le cookie
    // on vérifie que c'est bien un logger de l'utilisateur en question
    if(isset($_SESSION['log']) && $_SESSION['log']->uid==$uid)
	$logger = $_SESSION['log'];

    // on vide la session pour effacer les valeurs précédentes (notamment de skin)
    // qui peuvent être celles de quelqu'un d'autre ou celle par defaut
    $suid = isset($_SESSION['suid']) ? $_SESSION['suid'] : null;
    if($suid) {
	$logger = new DiogenesCoreLogger($uid,$suid);
	$logger->log("suid_start","{$_SESSION['forlife']} by {$_SESSION['suid']}");
	$_SESSION = Array('suid'=>$_SESSION['suid'], 'slog'=>$_SESSION['slog'], 'log'=>$logger);
    } else {
	$_SESSION = Array();
	$_SESSION['log'] = (isset($logger) ? $logger : new DiogenesCoreLogger($uid));
	if(empty($logger)) $_SESSION['log']->log("connexion",$_SERVER['PHP_SELF']);
	setcookie('ORGuid',$uid,(time()+25920000),'/','',0);
    }

    // le login est stocké pour un an
    $_SESSION['lastlogin'] = $lastlogin;
    $_SESSION['lastnewslogin'] = $lastnewslogin;
    $_SESSION['watch_last'] = $watch_last;
    $_SESSION['host'] = $host;
    $_SESSION['auth'] = ($identified ? AUTH_MDP : AUTH_COOKIE);
    $_SESSION['uid'] = $uid;
    $_SESSION['prenom'] = $prenom;
    $_SESSION['nom'] = $nom;
    $_SESSION['perms'] = $perms;
    $_SESSION['promo'] = $promo;
    $_SESSION['forlife'] = $forlife;
    $_SESSION['bestalias'] = $bestalias;
    $_SESSION['matricule'] = $matricule;
    $_SESSION['password'] = $password;
    $_SESSION['femme'] = $femme;
    // on récupère le logger si il existe, sinon, on logge la connexion
    set_skin();
}

function set_skin() {
    global $globals;
    if(logged()) {
	$result = $globals->db->query("SELECT skin,skin_tpl
		FROM auth_user_quick AS a INNER JOIN skins AS s
		ON a.skin=s.id WHERE user_id='{$_SESSION['uid']}' AND skin_tpl != ''");
	if(list($_SESSION['skin_id'], $_SESSION['skin']) = mysql_fetch_row($result)) {
	    if ($_SESSION['skin_id'] == SKIN_STOCHASKIN_ID) {
		$res = $globals->db->query("SELECT id,skin FROM skins
			WHERE !FIND_IN_SET('cachee',type) order by rand() limit 1");
		list($_SESSION['skin_id'], $_SESSION['skin']) = mysql_fetch_row($res);
		mysql_free_result($res);
	    }
	} else {
	    $_SESSION['skin'] = SKIN_COMPATIBLE;
	    $_SESSION['skin_id'] = SKIN_COMPATIBLE_ID;
	}
	mysql_free_result($result);
    }

    if( !logged() || !isset($_SERVER['HTTP_USER_AGENT'])
	    || ereg("Mozilla/4\.[0-9]{1,2} \[",$_SERVER['HTTP_USER_AGENT']) )
    {
	$_SESSION['skin'] = SKIN_COMPATIBLE;
	$_SESSION['skin_id'] = SKIN_COMPATIBLE_ID;
    }
  
}

?>
