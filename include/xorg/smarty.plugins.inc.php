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
    $Id: smarty.plugins.inc.php,v 1.2 2004-11-23 12:01:32 x2000habouzit Exp $
 ***************************************************************************/

// {{{ function block_dynamic()

/**
 * block function used to delimit non-cached blocks.
 */
function block_dynamic($param, $content, &$smarty)
{
    if(isset($param['on']) xor empty($param['on'])) {
        return $content;
    }
}

// }}}
// {{{ function function_implode()

/**
 * smarty function equivalent to php implode one.
 */
function function_implode($params)
{
    $sep = ' ';
    if(isset($params['sep'])) {
        $sep = $params['sep'];
        unset($params['sep']);
    }
    foreach($params as $key=>$val) {
        if(empty($params[$key])) {
            unset($params[$key]);
        }
    }

    return stripslashes(implode($sep,$params));
}

// }}}
// {{{ function function_dyn()

/**
 * smarty function, woking like {dynamic} block
 *
 * @deprecated since 0.9.0
 */
function function_dyn($params)
{
    return stripslashes(implode(' ',$params));
}

// }}}
// {{{ function escape_html ()

/**
 * default smarty plugin, used to auto-escape dangerous html.
 * 
 * < --> &lt;
 * > --> &gt;
 * " --> &quot;
 * & not followed by some entity --> &amp;
 */
function escape_html(&$string)
{
    if(is_string($string)) {
	$transtbl = Array('<' => '&lt;', '>' => '&gt;', '"' => '&quot;');
	return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,4};)/", "&amp;" , strtr($string, $transtbl));
    } else {
	return $string;
    }
}

// }}}
// {{{ function triple_quote_to_gettext()

/**
 * compilation plugin used for i18n purposes.
 *
 * Not used.
 */
function triple_quote_to_gettext($tpl_source, &$smarty)
{
    return preg_replace('/"""(.*?)"""/se', 'gettext(stripslashes(\'\\1\'))',$tpl_source);
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
        return $globals->$t[0];
    } else {
        return $globals->$t[0]->$t[1];
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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
