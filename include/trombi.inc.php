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
 $Id: trombi.inc.php,v 1.4 2004-11-02 07:48:41 x2000habouzit Exp $
 ***************************************************************************/

require_once('xorg.plugin.inc.php');
 
class Trombi extends XOrgPlugin {
    var $_get_vars = Array('offset');
    var $limit = 24;
    var $admin = false;
    var $showpromo = true;

    function setNbRows($row) { $this->limit = $row*3; }
    function setAdmin() { $this->admin = true; }
    function hidePromo() { $this->showpromo = false; }
    
    function show() {
	/* this point is nasty...  but since show() is called from the template ...
	 * I can't see any more clever way to achieve that
	 */
	global $page;

	$offset = $this->get_value('offset');
	list($total, $list) = call_user_func($this->_callback, $offset, $this->limit);
	$page_max = intval(($total-1)/$this->limit);

	$links = Array();
	if($offset) {
	    $links[] = Array('u'=> $this->make_url($offset-1), 'i' => $offset-1,  'text' => 'précédent');
	}
	for($i = 0; $i <= $page_max ; $i++)
	    $links[] = Array('u'=>$this->make_url($i), 'i' => $i, 'text' => $i+1);

	if($offset < $page_max) {
	    $links[] = Array ('u' => $this->make_url($offset+1), 'i' => $offset+1, 'text' => 'suivant');
	}

	$page->assign_by_ref('trombi_show_promo', $this->showpromo);
	$page->assign_by_ref('trombi_list', $list);
	$page->assign_by_ref('trombi_links', $links);
	$page->assign('trombi_admin', $this->admin);
	return $page->fetch('include/trombi.tpl');
    }
}

?>
