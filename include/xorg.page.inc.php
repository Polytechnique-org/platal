<?php
require("diogenes.core.page.inc.php");

function block_dynamic($param, $content, &$smarty) {
    if(isset($param['on']) xor empty($param['on']))
        return $content;
}

function function_implode($params) {
    $sep = ' ';
    if(isset($params['sep'])) {
        $sep = $params['sep'];
        unset($params['sep']);
    }
    foreach($params as $key=>$val)
        if(empty($params[$key]))
            unset($params[$key]);

    return stripslashes(htmlentities(implode($sep,$params)));
}

function function_dyn($params) {
    return stripslashes(htmlentities(implode(' ',$params)));
}

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
        $this->compile_check = isset($site_dev);
        $this->caching = ($type == SKINNED);

        $this->_page_type = $type;
        $this->_tpl = $tpl;

        $this->DiogenesCorePage();
        $this->register_block('dynamic', 'block_dynamic', false);
        $this->register_function('dyn', 'function_dyn', false);
        $this->register_function('implode', 'function_implode');

        // if necessary, construct new session
        if (empty($_SESSION['session']))
            $_SESSION['session'] = new XorgSession;

        $this->assign('site_dev',$site_dev);

        // si necessaire, c'est *ici* que se fait l'authentification
        $_no_legacy = true;
        $this->doAuth();
    }

    function run($append_to_id="") {
        global $baseurl, $site_dev;
        if($this->_page_type == NO_SKIN)
            parent::display($this->_tpl);
        else {
            if(isset($_SESSION['suid'])) $this->caching=false;
            $id = $this->make_id($append_to_id);
            if($site_dev) {
                $this->assign('validate', urlencode($baseurl.'/valid.html'));
                $result = $this->fetch('skin/'.$_SESSION['skin'], $id);
                $fd = fopen($this->cache_dir."valid.html","w");
                fwrite($fd, $result);
                fclose($fd);
                echo $result;
            } else
                parent::display('skin/'.$_SESSION['skin'], $id);
        }
        exit;
    }

    function xorg_is_cached($append_to_id="") {
        if($this->_page_type == NO_SKIN)
            return parent::is_cached($this->_tpl);
        else
            return parent::is_cached('skin/'.$_SESSION['skin'], $this->make_id($append_to_id));
    }

    function make_id($append_to_id="") {
        if($this->_page_type == NO_SKIN)
            return null;

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
        global $globals;
        $sql = $globals->db->query($sql_query);
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
