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

    return stripslashes(implode($sep,$params));
}

function function_dyn($params) {
    return stripslashes(implode(' ',$params));
}

function escape_html(&$string) {
    return is_string($string) ? htmlspecialchars($string) : $string;
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
        
        $this->register_modifier('escape_html', 'escape_html');
	$this->default_modifiers = Array('escape_html');
        $this->config_overwrite  = false;
        $this->compile_check     = isset($site_dev);
        $this->caching	         = ($type == SKINNED);

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
        global $baseurl, $site_dev, $globals;
        if($this->_page_type == NO_SKIN)
            parent::display($this->_tpl);
        else {
            if(isset($_SESSION['suid'])) $this->caching=false;
            $id = $this->make_id($append_to_id);
            if($site_dev) {
                $this->assign('db_trace', $globals->db->trace_format($this, 'database-debug.tpl'));
                $this->assign('validate', urlencode($baseurl.'/valid.html'));

		$result = $this->fetch('skin/'.$_SESSION['skin'], $id);
                $fd = fopen($this->cache_dir."valid.html","w");
                fwrite($fd, $result);
                fclose($fd);
		
		exec($globals->spoolroot."/scripts/xhtml/validate.pl ".$this->cache_dir."valid.html", $val);
		foreach($val as $h)
		    if(preg_match("/^X-W3C-Validator-Errors: (\d+)$/", $h, $m)) {
			if($m[1]) {
			    echo str_replace("@NB_ERR@",
				"<span class='erreur'><a href='http://validator.w3.org/check?uri=$baseurl/valid.html&amp;ss=1#result'>{$m[1]} ERREUR(S) !!!</a></span><br />", $result);
			} else {
			    echo str_replace("@NB_ERR@", "", $result);
			}
			exit;
		    }
            } else
                parent::display('skin/'.$_SESSION['skin'], $id);
        }
        exit;
    }

    function failure() {
        $this->_page_type = SKINNED;
        $this->_tpl = 'failure.tpl';
        $this->assign('xorg_tpl', 'failure.tpl');
        $this->caching=0;
        $this->run();
    }

    function xorg_is_cached($append_to_id="") {
        if($this->_page_type == NO_SKIN)
            return parent::is_cached($this->_tpl);
        else
            return parent::is_cached('skin/'.$_SESSION['skin'], $this->make_id($append_to_id));
    }

    function xorg_clear_cache($tpl) {
        if($this->_page_type == NO_SKIN)
            return parent::clear_cache($tpl);
        else
            return parent::clear_cache(null, $tpl);
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

    function mysql_assign($sql_query,$var_name,$var_nb_name='',$var_found_rows='') {
        global $globals;
        //lorsqu'on désire obtenir found_rows il faut désactiver la trace du résultat
        $switch_trace = false;
        if(!empty($var_found_rows) && $globals->db->_trace==1) {
            $switch_trace = true;
            $globals->db->trace_off();
        }
        
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
        
        if(!empty($var_found_rows)) {
            $n_res = $globals->db->query('SELECT FOUND_ROWS()');
            $r = mysql_fetch_row($n_res);
            $this->assign($var_found_rows, $r[0]);
            mysql_free_result($n_res);
            //si la trace était activée on affiche la trace sur la requête initiale
            if ($switch_trace) {
                $globals->db->trace_on();
                $sql = $globals->db->query($sql_query);
                mysql_free_result($sql);
            }
        }
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
