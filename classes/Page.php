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

require_once 'smarty/libs/Smarty.class.php';

class PlatalPage extends Smarty
{
    // {{{ properties

    var $_page_type;
    var $_tpl;
    var $_errors;
    var $_failure;

    // defaults
    var $caching          = false;
    var $config_overwrite = false;
    var $use_sub_dirs     = false;

    // }}}
    // {{{ function PlatalPage()

    function PlatalPage($tpl, $type = SKINNED)
    {
        global $globals;

        $this->Smarty();

        $this->template_dir  = $globals->spoolroot."/templates/";
        $this->compile_dir   = $globals->spoolroot."/spool/templates_c/";
        array_unshift($this->plugins_dir, $globals->spoolroot."/plugins/");
        $this->config_dir    = $globals->spoolroot."/configs/";

        $this->compile_check = !empty($globals->debug);

        $this->_page_type = $type;
        $this->_tpl       = $tpl;
        $this->_errors    = array();
        $this->_failure   = false;

        $this->register_prefilter('at_to_globals');
        $this->register_prefilter('trimwhitespace');
        $this->addJsLink('xorg.js');
    }

    // }}}
    // {{{ function fakeDiogenes()

    function fakeDiogenes()
    {
        require_once 'diogenes/diogenes.core.page.inc.php';
        $this->register_function("extval","diogenes_func_extval");
        $this->register_function("flags","diogenes_func_flags");
        $this->register_function("a","diogenes_func_a");
        $this->register_function("checkbox","diogenes_func_checkbox");
        $this->register_function("diff","diogenes_func_diff");
        $this->register_function("menu_item","diogenes_func_menu_item");
        $this->register_function("tag","diogenes_func_tag");
        $this->register_function("toolbar","diogenes_func_toolbar");
    }

    // }}}
    // {{{ function changeTpl()

    function changeTpl($tpl, $type = SKINNED)
    {
	$this->_tpl       = $tpl;
	$this->_page_type = $type;
        $this->_page_type = $type;
	$this->assign('xorg_tpl', $tpl);
    }

    // }}}
    // {{{ function _run()

    function _run($skin)
    {
        global $globals, $TIME_BEGIN;

        session_write_close();

        $this->assign("xorg_errors", $this->_errors);
        $this->assign("xorg_failure", $this->_failure);

        switch ($this->_page_type) {
          case NO_SKIN:
            $this->display($this->_tpl);
            exit;

          case SIMPLE:
            $this->assign('simple', true);
          case SKINNED:
	    $this->register_modifier('escape_html', 'escape_html');
	    $this->default_modifiers = Array('@escape_html');
        }

        if (!$globals->debug) {
            $this->display($skin);
            exit;
        }

        if ($globals->debug & 1) {
            $this->assign('db_trace', $globals->db->trace_format($this, 'database-debug.tpl'));
        }

        $this->assign('validate', true);
        $result = $this->fetch($skin);
        $ttime .= sprintf('Temps total: %.02fs<br />', microtime_float() - $TIME_BEGIN);
        $replc  = "<span class='erreur'>VALIDATION HTML INACTIVE</span><br />";

        if ($globals->debug & 2) {

            $fd = fopen($this->compile_dir."/valid.html","w");
            fwrite($fd, $result);
            fclose($fd);

            exec($globals->spoolroot."/bin/devel/xhtml.validate.pl ".$this->compile_dir."/valid.html", $val);
            foreach ($val as $h) {
                if (preg_match("/^X-W3C-Validator-Errors: (\d+)$/", $h, $m)) {
                    $replc = '<span style="color: #080;">HTML OK</span><br />';
                    if ($m[1]) {
                        $replc = "<span class='erreur'><a href='http://validator.w3.org/check?uri={$globals->baseurl}"
                            ."/valid.html&amp;ss=1#result'>{$m[1]} ERREUR(S) !!!</a></span><br />";
                    }
                    break;
                }
            }
        }

        echo str_replace("@HOOK@", $ttime.$replc, $result);
        exit;
    }

    // }}}
    // {{{ function run()

    function run()
    {
        die ("implement me");
    }

    // }}}
    // {{{ function nb_errs()

    function nb_errs()
    {
        return count($this->_errors);
    }

    // }}}
    // {{{ function trig()

    function trig($msg)
    {
        $this->_errors[] = $msg;
    }

    // }}}
    // {{{ function kill()

    function kill($msg)
    {
        $this->trig($msg);
        $this->_failure = true;
        $this->run();
    }

    // }}}
    // {{{ function loadModule()

    function loadModule($modname)
    {
        require_once("$modname.inc.php");
    }

    // }}}
    // {{{ function addJsLink

    function addJsLink($path)
    {
        $this->append('xorg_js', $path);
    }

    // }}}
    // {{{ function addCssLink

    function addCssLink($path)
    {
        $this->append('xorg_css', $path);
    }

    // }}}
}

// {{{ function escape_html ()

/**
 * default smarty plugin, used to auto-escape dangerous html.
 * 
 * < --> &lt;
 * > --> &gt;
 * " --> &quot;
 * & not followed by some entity --> &amp;
 */
function escape_html($string)
{
    if (is_string($string)) {
	$transtbl = Array('<' => '&lt;', '>' => '&gt;', '"' => '&quot;', '\'' => '&#39;');
	return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,4};)/", "&amp;" , strtr($string, $transtbl));
    } else {
	return $string;
    }
}

// }}}
// {{{ function at_to_globals()

/**
 * helper
 */

function _to_globals($s) {
    global $globals;
    $t = explode('.',$s);
    if (count($t) == 1) {
        return var_export($globals->$t[0],true);
    } else {
        return var_export($globals->$t[0]->$t[1],true);
    }
}

/**
 * compilation plugin used to import $globals confing through #globals.foo.bar# directives
 */

function at_to_globals($tpl_source, &$smarty)
{
    return preg_replace('/#globals\.([a-zA-Z0-9_.]+?)#/e', '_to_globals(\'\\1\')', $tpl_source);
}

// }}}
// {{{  function trimwhitespace

function trimwhitespace($source, &$smarty)
{
    $tags = array('script', 'pre', 'textarea');

    foreach ($tags as $tag) {
        preg_match_all("!<{$tag}[^>]+>.*?</{$tag}>!is", $source, ${$tag});
        $source = preg_replace("!<{$tag}[^>]+>.*?</{$tag}>!is", "&&&{$tag}&&&", $source);
    }

    // remove all leading spaces, tabs and carriage returns NOT
    // preceeded by a php close tag.
    $source = preg_replace('/((?<!\?>)\n)[\s]+/m', '\1', $source);

    foreach ($tags as $tag) {
        $source = preg_replace("!&&&{$tag}&&&!e",  'array_shift(${$tag}[0])', $source);
    }

    return $source; 
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
