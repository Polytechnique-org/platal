<?php
require("diogenes.core.page.inc.php");

function block_dynamic($param, $content, &$smarty) { return $content; }

function function_dyn($params) { return implode(' ',$params); }

class XorgPage extends DiogenesCorePage {
  var $_page_type;
  var $_tpl;
  
  function XorgPage($tpl, $type=SKINNED) {
    global $site_dev,$marketing_admin;

    $this->_page_type = SKINNED;
    $this->_tpl = $tpl;

    $this->DiogenesCorePage();
    $this->register_block('dynamic', 'block_dynamic', false);
    $this->register_function('dyn', 'function_dyn', false);

    // if necessary, construct new session
    if (!session_is_registered('session')) {
      session_register('session');
      $_SESSION['session'] = new XorgSession;
    }

    $this->assign('site_dev',$site_dev);

    // si necessaire, c'est *ici* que se fait l'authentification
    $_no_legacy = true;
    $this->doAuth();
    $this->set_skin();
  }

  function display($append_to_id="") {
      $id = $this->make_id() . ($append_to_id ? "-$append_to_id" : "");
      if($this->_page_type == POPUP)
          parent::display('skin/'.$_SESSION['skin_popup'], $id);
      else
          parent::display('skin/'.$_SESSION['skin'], $id);
  }

  function make_id() {
      $auth = (empty($_SESSION['auth']) ? 0 : $_SESSION['auth']);
      $perms = (empty($_SESSION['perms']) ? 0 : $_SESSION['perms']);
      return $this->_tpl."-$auth-$perms";
  }

  function doAuth() { }
  
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

}


/** Une classe pour les pages nécessitant l'authentification.
 * (equivalent de controlauthentification.inc.php)
 */
class XorgAuth extends XorgPage
{
  function XorgAuth($tpl, $type=SKINNED)
  {
    $this->XorgPage($tpl, $type);
  }

  function doAuth()
  {
    $_SESSION['session']->doAuth($this);
  }
}


/** Une classe pour les pages nécessitant l'authentification permanente.
 * (equivalent de controlpermanent.inc.php)
 */
class XorgCookie extends XorgPage
{
  function XorgCookie($tpl, $type=SKINNED)
  {
    $this->XorgPage($tpl, $type);
  }

  function doAuth()
  {
    $_SESSION['session']->doAuthCookie($this);
  }
}


/** Une classe pour les pages réservées aux admins (authentifiés!).
 */
class XorgAdmin extends XorgAuth
{
  function XorgAdmin($tpl, $type=SKINNED)
  {
    $this->XorgAuth($tpl, $type);
    check_perms();
  }
}

?>
