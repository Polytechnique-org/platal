<?php
require("diogenes.core.page.inc.php");

function block_dynamic($param, $content, &$smarty) {
    if(!isset($param['on']) || !empty($param['on']))
        return $content;
}

function function_dyn($params) { return stripslashes(htmlentities(implode(' ',$params))); }

class XorgPage extends DiogenesCorePage {
  var $_page_type;
  var $_tpl;
  
  function XorgPage($tpl, $type=SKINNED) {
    global $site_dev,$marketing_admin,$globals;

    $this->template_dir = $globals->spoolroot."/templates/";
    $this->compile_dir  = $globals->spoolroot."/templates_c/";
    $this->plugins_dir[]= $globals->spoolroot."/plugins/";
    $this->config_dir   = $globals->spoolroot."/configs/";
    $this->cache_dir    = $globals->spoolroot."/cache/";

    $this->config_overwrite=false;
    $this->compile_check=true;
    $this->caching=true;

    $this->_page_type = SKINNED;
    $this->_tpl = $tpl;

    $this->DiogenesCorePage();
    $this->register_block('dynamic', 'block_dynamic', false);
    $this->register_function('dyn', 'function_dyn', false);

    // if necessary, construct new session
    if (empty($_SESSION['session']))
      $_SESSION['session'] = new XorgSession;

    $this->assign('site_dev',$site_dev);

    // si necessaire, c'est *ici* que se fait l'authentification
    $_no_legacy = true;
    $this->doAuth();
  }

  function display($append_to_id="") {
      $id = $this->make_id($append_to_id);
      if($this->_page_type == POPUP)
          parent::display('skin/'.$_SESSION['skin_popup'], $id);
      else
          parent::display('skin/'.$_SESSION['skin'], $id);
      exit;
  }

  function xorg_is_cached($append_to_id="") {
      $id = $this->make_id($append_to_id);
      if($this->_page_type == POPUP)
          return parent::is_cached('skin/'.$_SESSION['skin_popup'], $id);
      else
          return parent::is_cached('skin/'.$_SESSION['skin'], $id);
  }

  function make_id($append_to_id="") {
      $ret = $this->_tpl;
      if($append_to_id)
          $ret.="|$append_to_id";

      $auth_trans = Array(AUTH_PUBLIC => 'public', AUTH_COOKIE => 'cookie', AUTH_MDP => 'passwd');
      $ret .= '|A_'.$auth_trans[empty($_SESSION['auth']) ? AUTH_PUBLIC : $_SESSION['auth']];
      
      $ret .= '-'.(empty($_SESSION['perms']) ? PERMS_EXT : $_SESSION['perms']);

      return $ret;
  }

  function doAuth() { }

  function mysql_assign($sql_query,$var_name,$var_nb_name='') {
    $sql = mysql_query($sql_query);
    if(mysql_errno())
      return(mysql_error($sql));

    $array = Array();
    while($array[] = mysql_fetch_assoc($sql));
    array_pop($array);
    mysql_free_result($sql);

    $this->assign_by_ref($var_name,$array);
    if(!empty($var_nb_name))
      $this->assign($var_nb_name, count($array));
    return 0;
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
