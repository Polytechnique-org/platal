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
    $Id: xorg.page.inc.php,v 1.53 2004-11-23 12:01:31 x2000habouzit Exp $
 ***************************************************************************/

require_once("diogenes.core.page.inc.php");

// {{{ class XorgPage

/**
 * This class is the Core template compiler
 *
 * @category XOrgCore
 * @package  XOrgCore
 * @author   Jeremy Lainé <jeremy.laine@polytechnique.org>
 * @version  $Id: xorg.page.inc.php,v 1.53 2004-11-23 12:01:31 x2000habouzit Exp $
 * @access   public
 * @see      DiogenesCorePage
 */
class XorgPage extends DiogenesCorePage
{
    // {{{ properties
    
    var $_page_type;
    var $_tpl;

    // }}}
    // {{{ function XorgPage()

    function XorgPage($tpl, $type=SKINNED)
    {
        global $globals;

	$this->setLang();

        $this->template_dir  = $globals->spoolroot."/templates/";
        $this->compile_dir   = $globals->spoolroot."/templates_c/";
        $this->plugins_dir[] = $globals->spoolroot."/plugins/";
        $this->config_dir    = $globals->spoolroot."/configs/";
        $this->cache_dir     = $globals->spoolroot."/cache/";
	$this->use_sub_dirs  = false;


        $this->config_overwrite  = false;
        $this->compile_check     = !empty($globals->debug);
        $this->caching	         = ($type == SKINNED);
	if ($type == SKINNED) {
	    $this->register_modifier('escape_html', 'escape_html');
	    $this->default_modifiers = Array('escape_html');
	}

        $this->_page_type = $type;
        $this->_tpl = $tpl;

        $this->DiogenesCorePage();
	require_once('xorg/smarty.plugins.inc.php');
        $this->register_block('dynamic', 'block_dynamic', false);
        $this->register_function('dyn', 'function_dyn', false);
        $this->register_function('implode', 'function_implode');
        $this->register_prefilter('triple_quote_to_gettext');
        $this->register_prefilter('at_to_globals');

        // if necessary, construct new session
        if (empty($_SESSION['session']))
            $_SESSION['session'] = new XorgSession;

        $this->assign('site_dev',$globals->debug);
        $this->doAuth();
    }

    // }}}
    // {{{ function changeTpl()

    function changeTpl($tpl, $type=SKINNED)
    {
	$this->_tpl       = $tpl;
	$this->_page_type = $type;
        $this->caching	  = ($type == SKINNED);
	if ($type == SKINNED) {
	    $this->register_modifier('escape_html', 'escape_html');
	    $this->default_modifiers = Array('escape_html');
	}

        $this->_page_type = $type;
	$this->assign('xorg_tpl', $tpl);
    }

    // }}}
    // {{{ function setLang()

    function setLang($lang=null)
    {
	global $globals;
	$locale = empty($lang) ? 'fr_FR' : '$lang';
	setlocale(LC_MESSAGES, $locale);
	setlocale(LC_TIME, $locale);
	$this->compile_id = $locale;
	bindtextdomain('xorg', $globals->spoolroot.'/locale/');
	textdomain('xorg');
    }

    // }}}
    // {{{ function run()

    function run($append_to_id="")
    {
        global $globals, $TIME_BEGIN;
        if ($this->_page_type == NO_SKIN) {
            $this->display($this->_tpl);
        } else {
            if (isset($_SESSION['suid'])) {
                $this->caching=false;
            }
            $this->assign_by_ref('menu', $globals->menu->menu());
            $id = $this->make_id($append_to_id);
            if ($globals->debug) {
                $this->assign('db_trace', $globals->db->trace_format($this, 'database-debug.tpl'));
                $this->assign('validate', urlencode($globals->baseurl.'/valid.html'));

		$result = $this->fetch('skin/'.$_SESSION['skin'], $id);
		$total_time = sprintf('Temps total: %.02fs<br />', microtime_float() - $TIME_BEGIN);
                $fd = fopen($this->cache_dir."valid.html","w");
                fwrite($fd, $result);
                fclose($fd);
		
		exec($globals->spoolroot."/scripts/xhtml/validate.pl ".$this->cache_dir."valid.html", $val);
		foreach ($val as $h) {
		    if (preg_match("/^X-W3C-Validator-Errors: (\d+)$/", $h, $m)) {
			if ($m[1]) {
			    echo str_replace("@HOOK@",
				"$total_time<span class='erreur'><a href='http://validator.w3.org/check?uri="
                                .$globals->baseurl."/valid.html&amp;ss=1#result'>{$m[1]} ERREUR(S) !!!</a></span><br />",
                                $result);
			} else {
			    echo str_replace("@HOOK@", "$total_time", $result);
			}
			exit;
		    }
                }
            } else {
                $this->display('skin/'.$_SESSION['skin'], $id);
            }
        }
        exit;
    }

    // }}}
    // {{{ function failure()

    function failure()
    {
        $this->_page_type = SKINNED;
        $this->_tpl = 'failure.tpl';
        $this->assign('xorg_tpl', 'failure.tpl');
        $this->caching=0;
        $this->run();
    }

    // }}}
    // {{{ function xorg_is_cached()

    function xorg_is_cached($append_to_id="")
    {
        if ($this->_page_type == NO_SKIN) {
            return parent::is_cached($this->_tpl);
        } else {
            return parent::is_cached('skin/'.$_SESSION['skin'], $this->make_id($append_to_id));
        }
    }

    // }}}
    // {{{ function xorg_clear_cache()

    function xorg_clear_cache($tpl)
    {
        if ($this->_page_type == NO_SKIN) {
            return parent::clear_cache($tpl);
        } else {
            return parent::clear_cache(null, $tpl);
        }
    }

    // }}}
    // {{{ function make_id()

    function make_id($append_to_id="")
    {
        if ($this->_page_type == NO_SKIN) {
            return null;
        }

        $ret = $this->_tpl;
        if ($append_to_id) {
            $ret.="-$append_to_id";
        }

        $auth_trans = Array(AUTH_PUBLIC => 'public', AUTH_COOKIE => 'cookie', AUTH_MDP => 'passwd');
        $ret .= '-'.$auth_trans[empty($_SESSION['auth']) ? AUTH_PUBLIC : $_SESSION['auth']];
        $ret .= '-'.(empty($_SESSION['perms']) ? PERMS_EXT : $_SESSION['perms']);

        return $ret;
    }

    // }}}
    // {{{ function doAuth()

    function doAuth() { }
    
    // }}}
    // {{{ function mysql_assign()

    function mysql_assign($sql_query,$var_name,$var_nb_name='',$var_found_rows='') {
        global $globals;
        //lorsqu'on désire obtenir found_rows il faut désactiver la trace du résultat
        $switch_trace = false;
        if (!empty($var_found_rows) && $globals->db->_trace==1) {
            $switch_trace = true;
            $globals->db->trace_off();
        }
        
        $sql = $globals->db->query($sql_query);
        if (mysql_errno()) {
            return(mysql_error($sql));
        }

        $array = Array();
        while ($array[] = mysql_fetch_assoc($sql));
        array_pop($array);
        mysql_free_result($sql);
        $this->assign_by_ref($var_name,$array);
        if (!empty($var_nb_name)) {
            $this->assign($var_nb_name, count($array));
        }
        
        if (!empty($var_found_rows)) {
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

    // }}}
    // {{{ function loadModule()
    
    function loadModule($modname)
    {
        require_once("$modname.inc.php");
    }

    // }}}
}

// }}}
// {{{ class XOrgAuth

/** Une classe pour les pages nécessitant l'authentification.
 * (equivalent de controlauthentification.inc.php)
 */
class XorgAuth extends XorgPage
{
    // {{{ function XorgAuth()

    function XorgAuth($tpl, $type=SKINNED)
    {
        $this->XorgPage($tpl, $type);
    }

    // }}}
    // {{{ function doAuth()

    function doAuth()
    {
        $_SESSION['session']->doAuth($this);
    }
    
    // }}}
}

// }}}
// {{{ class XorgCookie

/** Une classe pour les pages nécessitant l'authentification permanente.
 * (equivalent de controlpermanent.inc.php)
 */
class XorgCookie extends XorgPage
{
    // {{{ function XorgCookie()
    
    function XorgCookie($tpl, $type=SKINNED)
    {
        $this->XorgPage($tpl, $type);
    }
    
    // }}}
    // {{{ function doAuth()

    function doAuth()
    {
        $_SESSION['session']->doAuthCookie($this);
    }
    
    // }}}
}

// }}}
// {{{ class XorgAdmin

/** Une classe pour les pages réservées aux admins (authentifiés!).
 */
class XorgAdmin extends XorgAuth
{
    // {{{ function XorgAdmin()
    
    function XorgAdmin($tpl, $type=SKINNED)
    {
        $this->XorgAuth($tpl, $type);
        check_perms();
    }
    
    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
