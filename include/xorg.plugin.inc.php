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

// {{{ class XOrgPlugin

/**
 * XOrg Plugins class
 *
 * this class is used for plugins whose behavior depends only from the GET.
 *
 * @category XOrgCore
 * @package  XOrgCore
 * @author   Pierre Habouzit <pierre.habouzit@polytechnique.org>
 * @access   public
 * @since    Classe available since 0.9.2
 */
class XOrgPlugin
{
    // {{{ properties

    /** have to override, contents the fields names used to drive the plugin */
    var $_get_vars = array();
    /** some polymorphism at low cost, may be used, or not */
    var $_callback;

    // }}}
    // {{{ function XOrgPlugin()

    /** constructor.
     * the constructor override $_get_vars settings by prefixing the names with $prefix
     */
    function XOrgPlugin($funcname='', $prefix='')
    {
	$this->_callback = $funcname;
	$this->_prefix = $prefix;
	foreach ($this->_get_vars as $key=>$val) {
            $this->_get_vars[$key] = $prefix.$val;
        }
    }

    // }}}
    // {{{ function get_value()

    /** transparent access to $_GET, wrt the right $prefix
     */
    function get_value($key)
    {
        return Get::v($this->_prefix.$key);
    }

    // }}}
    // {{{ function make_url()

    /** construct an url with the given parameters to drive the plugin.
     * leave all other GET params alone
     */
    function make_url($params)
    {
	$get = Array();
	$args = isset($params) ? $params : Array();

	if (!is_array($args)) {
            $args = array($this->_get_vars[0]=>$params);
	}

	foreach ($_GET as $key=>$val) {
            if ($key == 'n') {
                continue;
            }
	    if (in_array($key, $this->_get_vars) && array_key_exists($key, $args)) {
                continue;
            }
	    $get[] = urlencode($key) . '=' . urlencode($val);
	}

	foreach ($this->_get_vars as $key) {
	    if (array_key_exists($key, $args)) {
		if ($args[$key]) {
                    $get[] = urlencode($key) . '=' . urlencode($args[$key]);
                }
            } elseif (Get::has('key')) {
		$get[] = urlencode($key) . '=' . urlencode(Get::v($key));
	    }
	}

	return pl_self() . '?' . join('&amp;', $get);
    }

    // }}}
    // {{{ function show()

    /** need to be overriden.  */
    function show ()
    { return ''; }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
