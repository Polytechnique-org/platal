<?php
require("diogenes.core.session.inc.php");
require("diogenes.misc.inc.php");

class XorgSession extends DiogenesCoreSession {
  function XorgSession()
  {
    $this->DiogenesCoreSession();
    $_SESSION['challenge']=rand_token();
    set_skin();
  }


  /** Try to do an authentication.
   *
   * @param page the calling page (by reference)
   */
  function doAuth(&$page) {
    if(identified()) { // ok, c'est bon, on n'a rien à faire
      return;
    }

    if (isset($_REQUEST['username']) and isset($_REQUEST['response'])
        and isset($_SESSION['challenge']))
    {
      // si on vient de recevoir une identification par passwordpromptscreen.tpl
      // ou passwordpromptscreenlogged.tpl
      $res = @mysql_query( "SELECT username,user_id,password FROM auth_user_md5 WHERE username='{$_REQUEST['username']}'");
      if(@mysql_num_rows($res) != 0) {
        list($username,$uid,$password)=mysql_fetch_row($res);
        mysql_free_result($res);
        $expected_response=md5("{$_REQUEST['username']}:$password:{$_SESSION['challenge']}");
        if($_REQUEST['response'] == $expected_response) {
          unset($_SESSION['challenge']);
          // on logge la réussite pour les gens avec cookie
          if(isset($_SESSION['log']))
            $_SESSION['log']->log("auth_ok");
          start_connexion($username, $uid, true);
          return true;
        } else {
          // mot de passe incorrect pour le login existant
          // on logge l'échec pour les gens avec cookie
          if(isset($_SESSION['log']))
            $_SESSION['log']->log("auth_fail","bad password");
          $this->doLogin($page);
        }
      } else {
        // login inexistant dans la base de donnees
        // on logge l'échec pour les gens avec cookie
        if(isset($_SESSION['log']))
          $_SESSION['log']->log("auth_fail","bad login");
        $this->doLogin($page);
      }
    } else {
      // ni loggué ni tentative de login
      $this->doLogin($page);
    }
  }


  /** Try to do a cookie-based authentication.
   *
   * @param page the calling page (by reference)
   */
  function doAuthCookie(&$page) {
    // si on est deja connecté, c'est bon, rien à faire
    if(logged())
      return;

    // on vient de recevoir une demande d'auth, on passe la main a doAuth
    if (isset($_REQUEST['username']) and isset($_REQUEST['response']))
      return $this->doAuth($page);

    // sinon, on vérifie que les bons cookies existent
    if(!isset($_COOKIE['ORGaccess']) or $_COOKIE['ORGaccess'] == ''
        or !isset($_COOKIE['ORGlogin']))
      return $this->doAuth($page);


    // les bons cookies existent, donc ça veut dire que la session a expirée
    // il faut donc vérifier que les cookies sont bons et recréer la session
    // et d'authoriser l'accès
    $res = @mysql_query( "SELECT user_id,password FROM auth_user_md5 WHERE username='{$_COOKIE['ORGlogin']}'");
    if(@mysql_num_rows($res) != 0) {
      list($uid,$password)=mysql_fetch_row($res);
      mysql_free_result($res);
      $expected_value=md5($password);
      if($expected_value == $_COOKIE['ORGaccess']) {
        //session_start();
        start_connexion($_COOKIE['ORGlogin'], $uid, false);
        return true;
      } else {
        // ORGaccess n'est pas bon
        // cette variable failed_ORGaccess permet à
        // controlauthentication.inc.php de mettre
        // passwordpromtscreen.inc.php plutôt que
        // passwordpromtscreenlogged.inc.php dans le
        // cas ou ORGaccess n'est pas bon, permettant à l'utilisateur
        // de changer son login ci-nécessaire.
        $failed_ORGaccess = true;
        return $this->doAuth($page);
      }
    } else {
      // ORGlogin n'est pas bon
      return $this->doAuth($page);
    }
  }

  /** Display login screen.
   */
  function doLogin(&$page) {
    global $failed_ORGaccess,$site_dev;

    if(isset($_COOKIE['ORGaccess']) and isset($_COOKIE['ORGlogin']) and !isset($failed_ORGaccess)) {
      $page->_tpl = 'password_prompt_logged.tpl';
      $page->assign("xorg_head", "password_prompt_logged.head.tpl");
      $page->assign("xorg_tpl", "password_prompt_logged.tpl");
      $page->display();
    } else {
      $page->_tpl = 'password_prompt.tpl';
      $page->assign("xorg_head", "password_prompt.head.tpl");
      $page->assign("xorg_tpl", "password_prompt.tpl");
      $page->display();
    }
    exit;
  }

}

/** verifie si un utilisateur a les droits pour voir une page
 ** si ce n'est pas le cas, on affiche une erreur
 * @return void
 * TODO RECODER
 */
function check_perms($auth_array=array()) {
  if (!has_perms($auth_array)) {
    $_SESSION['log']->log("noperms",$_SERVER['PHP_SELF']);
    echo "<div class=\"erreur\">";
    echo "Tu n'as pas les permissions n&eacute;cessaires pour acc&eacute;der &agrave; cette page.";
    echo "</div>";
    include("footer.inc.php");
    exit;
  }
}


/** verifie si un utilisateur a les droits pour voir une page
 ** soit parce qu'il est admin, soit il est dans une liste
 ** supplementaire de personnes utilisées
 * @return BOOL
 */
  function has_perms($auth_array=array()) {
    return logged()
      && ( (!empty($auth_array) && in_array($_SESSION['username'], $auth_array))
          || ($_SESSION['perms']==PERMS_ADMIN) );
  }


/** renvoie true si la session existe et qu'on est loggué correctement
 * false sinon
 * @return bool vrai si loggué
 * @see header2.inc.php
 */
function logged () {
  return(isset($_SESSION['auth']) and $_SESSION['auth']>=AUTH_COOKIE);
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


/** place les variables de session dépendants de auth_user_md5
 * et met à jour les dates de dernière connexion si nécessaire
 * @return void
 * @see controlpermanent.inc.php controlauthentication.inc.php
 */
function start_connexion ($username, $uid, $identified) {
  $result=mysql_query("SELECT prenom, nom, perms, promo, UNIX_TIMESTAMP(lastnewslogin), UNIX_TIMESTAMP(lastlogin), host, matricule FROM auth_user_md5 WHERE user_id=$uid;");
  list($prenom, $nom, $perms, $promo, $lastnewslogin, $lastlogin, $host, $matricule) = mysql_fetch_row($result);
  mysql_free_result($result);
  // on garde le logger si il existe (pour ne pas casser les sessions lors d'une
  // authentification avec le cookie
  // on vérifie que c'est bien un logger de l'utilisateur en question
  if(isset($_SESSION['log']) && $_SESSION['log']->uid==$uid)
    $logger = $_SESSION['log'];
  // on vide la session pour effacer les valeurs précédentes (notamment de skin)
  // qui peuvent être celles de quelqu'un d'autre ou celle par defaut
  $_SESSION = array();
  if (!isset($_SESSION['suid'])) {
    // mise à jour de la date de dernière connexion
    // sauf lorsque l'on est en SUID
    $newhost=strtolower(gethostbyaddr($_SERVER['REMOTE_ADDR']));
    mysql_query("UPDATE auth_user_md5 SET host='$newhost',lastlogin=NULL WHERE user_id=$uid;");
    $_SESSION['lastlogin'] = $lastlogin;
    $_SESSION['host'] = $host;
  }
  // mise en place des variables de session
  $_SESSION['auth'] = ($identified ? AUTH_MDP : AUTH_COOKIE);
  $_SESSION['uid'] = $uid;
  $_SESSION['username'] = $username;
  $_SESSION['prenom'] = $prenom;
  $_SESSION['nom'] = $nom;
  $_SESSION['perms'] = $perms;
  $_SESSION['promo'] = $promo;
  $_SESSION['lastnewslogin'] = $lastnewslogin;
  $res = mysql_query("SELECT flags FROM identification WHERE matricule = '$matricule' AND FIND_IN_SET(flags, 'femme')");
  $_SESSION['femme'] = mysql_num_rows($res) > 0;
  mysql_free_result($res);
  // on récupère le logger si il existe, sinon, on logge la connexion
  $_SESSION['log'] = (isset($logger) ? $logger : new DiogenesCoreLogger($uid));
  if(empty($logger))
    $_SESSION['log']->log("connexion",$_SERVER['PHP_SELF']);
  // le login est stocké pour un an
  setcookie('ORGlogin',$username,(time()+25920000),'/','',0);
  set_skin();
}

function set_skin() {
  if(logged()) {
    $result = mysql_query("SELECT skin FROM auth_user_md5 WHERE username = '{$_SESSION['uid']}'");
    if(list($skin) = mysql_fetch_row($result)) {
      $sql = "SELECT normal,popup FROM skins WHERE ";
      if ($_SESSION['skin'] == SKIN_STOCHASKIN_ID) {
        $sql .= " !FIND_IN_SET('cachee',type) order by rand() limit 1";
      } else {
        $sql .= "id='$skin'";
      }
      $res = mysql_query($sql);
      list($_SESSION['skin'], $_SESSION['skin_popup']) = mysql_fetch_row($res);
      mysql_free_result($res);
    } else {
      $_SESSION['skin'] = SKIN_COMPATIBLE;
      $_SESSION['skin_popup'] = SKIN_COMPATIBLE;
    }
    mysql_free_result($result);
  }

  if( !logged() || !isset($_SERVER['HTTP_USER_AGENT'])
      || ereg("Mozilla/4\.[0-9]{1,2} \[",$_SERVER['HTTP_USER_AGENT']) )
  {
    $_SESSION['skin'] = SKIN_COMPATIBLE;
    $_SESSION['skin_popup'] = SKIN_COMPATIBLE;
  }
}

?>
