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
 ***************************************************************************/

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
    if(is_string($string)) {
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
