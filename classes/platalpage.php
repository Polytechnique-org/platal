<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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
    private $_page_type;
    private $_tpl;
    private $_errors;
    private $_failure;
    private $_jsonVars;

    // {{{ function PlatalPage()

    public function __construct($tpl, $type = SKINNED)
    {
        parent::Smarty();

        global $globals;

        $this->caching       = false;
        $this->config_overwrite = false;
        $this->use_sub_dirs  = false;
        $this->template_dir  = $globals->spoolroot."/templates/";
        $this->compile_dir   = $globals->spoolroot."/spool/templates_c/";
        array_unshift($this->plugins_dir, $globals->spoolroot."/plugins/");
        $this->config_dir    = $globals->spoolroot."/configs/";

        $this->compile_check = !empty($globals->debug);

        $this->_page_type = $type;
        $this->_tpl       = $tpl;
        $this->_errors    = array();
        $this->_jsonVars  = array();
        $this->_failure   = false;

        $this->register_prefilter('at_to_globals');
        $this->addJsLink('xorg.js');
    }

    // }}}
    // {{{ function changeTpl()

    public function changeTpl($tpl, $type = SKINNED)
    {
    	$this->_tpl       = $tpl;
	    $this->_page_type = $type;
    	$this->assign('xorg_tpl', $tpl);
    }

    // }}}
    // {{{ function raw()

    public function raw()
    {
        global $globals;
        $this->assign('globals', $globals);
        return $this->fetch($this->_tpl);
    }

    // }}}
    // {{{ function _run()

    protected function _run($skin)
    {
        global $globals, $TIME_BEGIN;

        session_write_close();

        $this->register_prefilter('trimwhitespace');
        $this->register_prefilter('form_force_encodings');
        $this->assign('xorg_errors', $this->_errors);
        $this->assign('xorg_failure', $this->_failure);
        $this->assign('globals', $globals);

        if (Env::has('json') && count($this->_jsonVars)) {
            return $this->jsonDisplay();
        }
        
        if (Env::v('display') == 'light') {
            $this->_page_type = SIMPLE;
        } elseif (Env::v('display') == 'raw') {
            $this->_page_type = NO_SKIN;
        } elseif (Env::v('display') == 'full') {
            $this->_page_typ = SKINNED;
        }

        switch ($this->_page_type) {
          case NO_SKIN:
            error_reporting(0);
            $this->display($this->_tpl);
            exit;

          case SIMPLE:
            $this->assign('simple', true);

          case SKINNED:
    	    $this->register_modifier('escape_html', 'escape_html');
	        $this->default_modifiers = Array('@escape_html');
        }
        $this->register_outputfilter('hide_emails');
        $this->addJsLink('wiki.js');
        header("Accept-Charset: utf-8");
        if (Env::v('forceXml')) {
            header("Content-Type: text/xml; charset=utf-8");
        }

        if (!$globals->debug) {
            error_reporting(0);
            $this->display($skin);
            exit;
        }

        if ($globals->debug & 1) {
            PlBacktrace::clean();
            $this->assign_by_ref('backtraces', PlBacktrace::$bt);
        }

        $this->assign('validate', true);
        error_reporting(0);
        $result = $this->fetch($skin);
        $ttime  = sprintf('Temps total: %.02fs - Mémoire totale : %dKo<br />', microtime(true) - $TIME_BEGIN
                                                                                , memory_get_peak_usage(true) / 1024);
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
    // {{{ function nb_errs()

    public function nb_errs()
    {
        return count($this->_errors);
    }

    // }}}
    // {{{ function trig()

    public function trig($msg)
    {
        $this->_errors[] = $msg;
    }

    // }}}
    // {{{ function kill()

    public function kill($msg)
    {
        global $platal;

        $this->assign('platal', $platal);
        $this->trig($msg);
        $this->_failure = true;
        $this->run();
    }

    // }}}
    // {{{ function addJsLink

    public function addJsLink($path)
    {
        $this->append('xorg_js', $path);
    }

    // }}}
    // {{{ function addCssLink

    public function addCssLink($path)
    {
        $this->append('xorg_css', $path);
    }

    // }}}
    // {{{ function addCssInline

    public function addCssInline($css)
    {
        if (!empty($css)) {
            $this->append('xorg_inline_css', $css);
        }    
    }

    // }}}
    // {{{ function setRssLink

    public function setRssLink($title, $path)
    {
        $this->assign('xorg_rss', array('title' => $title, 'href' => $path));
    }

    // }}}
    // {{{ function jsonDisplay
    protected function jsonDisplay()
    {
        header("Content-type: text/javascript; charset=utf-8");
        array_walk_recursive($this->_jsonVars, "escape_xorgDB");
        $jsonbegin = Env::v('jsonBegin');
        $jsonend = Env::v('jsonEnd');
        if (Env::has('jsonVar')) {
            $jsonbegin = Env::v('jsonVar').' = ';
            $jsonend = ';';
        } elseif (Env::has('jsonFunc')) {
            $jsonbegin = Env::v('jsonFunc').'(';
            $jsonend = ');';
        }
        echo $jsonbegin, json_encode($this->_jsonVars), $jsonend;
        exit;
    }
    // }}}
    // {{{ function jsonAssign
    public function jsonAssign($var, $value)
    {
        $this->_jsonVars[$var] = $value;
    }
}

function escape_xorgDB(&$item, $key)
{
    if (is_a($item, 'XOrgDBIterator')) {
        $expanded = array();
        while ($a = $item->next()) {
            $expanded[] = $a;
        }
        $item = $expanded;
    }
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
	    return strtr($string, $transtbl);
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
    $tags = '(script|pre|textarea)';
    preg_match_all("!<$tags.*?>.*?</(\\1)>!ius", $source, $tagsmatches);
    $source = preg_replace("!<$tags.*?>.*?</(\\1)>!ius", "&&&tags&&&", $source);

    // remove all leading spaces, tabs and carriage returns NOT
    // preceeded by a php close tag.
    $source = preg_replace('/((?<!\?>)\n)[\s]+/m', '\1', $source);
    $source = preg_replace("!&&&tags&&&!e",  'array_shift($tagsmatches[0])', $source);

    return $source; 
}

// }}}
// {{{

function form_force_encodings($source, &$smarty)
{
    return preg_replace('/<form[^\w]/',
                        '\0 accept-charset="utf-8" ',
                        $source);
}

// }}}
// {{{ function hide_emails

function _hide_email($source)
{
    $source = str_replace("\n", '', $source);
    return '<script type="text/javascript">//<![CDATA[' . "\n" .
           'Nix.decode("' . addslashes(str_rot13($source)) . '");' . "\n" .
           '//]]></script>'; 
}

function hide_emails($source, &$smarty)
{
    //prevent email replacement in <script> and <textarea>
    $tags = '(script|textarea|select)';
    preg_match_all("!<$tags.*?>.*?</(\\1)>!ius", $source, $tagsmatches);
    $source = preg_replace("!<$tags.*?>.*?</(\\1)>!ius", "&&&tags&&&", $source);

    //catch all emails in <a href="mailto:...">
    preg_match_all("!<a[^>]+href=[\"'][^\"']*[-a-z0-9+_.]+@[-a-z0-9_.]+[^\"']*[\"'].*?>.*?</a>!ius", $source, $ahref);
    $source = preg_replace("!<a[^>]+href=[\"'][^\"']*[-a-z0-9+_.]+@[-a-z0-9_.]+[^\"']*[\"'].*?>.*?</a>!ius", '&&&ahref&&&', $source);

    //prevant replacement in tag attributes
    preg_match_all("!<[^>]+[-a-z0-9_+.]+@[-a-z0-9_.]+.+?>!ius", $source, $misc);
    $source = preg_replace("!<[^>]+[-a-z0-9_+.]+@[-a-z0-9_.]+.+?>!ius", '&&&misc&&&', $source);

    //catch !
    $source = preg_replace('!([-a-z0-9_+.]+@[-a-z0-9_.]+)!iue', '_hide_email("\1")', $source); 
    $source = preg_replace('!&&&ahref&&&!e', '_hide_email(array_shift($ahref[0]))', $source);

    // restore data
    $source = preg_replace('!&&&misc&&&!e', 'array_shift($misc[0])', $source);
    $source = preg_replace("!&&&tags&&&!e",  'array_shift($tagsmatches[0])', $source);

    return $source;
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
