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
 $Id: xorg.plugin.inc.php,v 1.1 2004-10-28 20:28:42 x2000habouzit Exp $
 ***************************************************************************/

class XOrgPlugin {
    var $_get_vars = Array();
    var $_callback;
    
    function XOrgPlugin($funcname) {
	$this->_callback = $funcname;
    }

    function make_url($params) {
	$get = Array();
	$args = empty($params) ? Array() : $params;

	if(!is_array($args)) {
	    if(count($this->_get_vars)!=1) {
		return "<p class='erreur'>params should be an array</p>";
	    } else {
		$args = Array($this->_get_vars[0]=>$params);
	    }
	}

	foreach($_GET as $key=>$val) {
	    if(in_array($key,$this->_get_vars) && array_key_exists($key,$args)) continue;
	    $get[] = urlencode($key) . '=' . urlencode($val);
	}

	foreach($this->_get_vars as $key) {
	    if(array_key_exists($key,$args)) {
		if($args[$key]) $get[] = urlencode($key) . '=' . urlencode($args[$key]);
	    } elseif(isset($_GET['key'])) {
		$get[] = urlencode($key) . '=' . urlencode($_GET[$key]);
		
	    }
	}

	return $_SERVER['PHP_SELF'] . '?' . join('&amp;',$get);
    }
}

?>
