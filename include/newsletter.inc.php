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
        $Id: newsletter.inc.php,v 1.4 2004-10-15 15:39:40 x2000habouzit Exp $
 ***************************************************************************/


define('FEMME', 1);
define('HOMME', 0);

class NewsLetter {
    $_cats;
    
    function NewsLetter() {
	global $globals;

	$res = $globals->db->query("SELECT cid,title FROM newsletter_cat ORDER BY pos");
	$this->_cats = Array();
	while(list($cid,$title) = mysql_fetch_row($res)) {
	    $this->_cats[$cid] = $title;
	}
	mysql_free_result($res)
    
    }
}

class NLArticle {
    function NLArticle() { }
}

class NLConstraint {
    var $_func;
    var $_arg;
    
    function NLConstraint($func, $arg) {
	$this->_func = $func;
	$this->_arg = $arg;
    }

    function check($user) { return false; }
}

class NLPromoConstraint {
    function check($user) {
	$promo = $user['promo'];
	switch($this->_func) {
	    case 'eq':   return ( $promo == $this->_arg );
	    case 'neq':  return ( $promo != $this->_arg );
	    case 'geq':  return ( $promo >= $this->_arg );
	    case 'leq':  return ( $promo <= $this->_arg );
	    case 'odd':  return ( $promo % 2 == 1 );
	    case 'even': return ( $promo % 2 == 0 );
	    default: return false;
	}
    }
}

class NLSexeConstraint {
    function check($user) { return $user['sexe'] == $_arg; }
}
	

function get_nl_list() {
    global $globals;
    $res = $globals->db->query("SELECT id,date,titre FROM newsletter ORDER BY date DESC");
    $ans = Array();
    while($tmp = mysql_fetch_assoc($res)) $ans[] = $tmp;
    mysql_free_result($res);
    return $ans;
}

function get_nl_state() {
    global $globals;
    $res = $globals->db->query("SELECT COUNT(*)>0 FROM newsletter_ins WHERE user_id={$_SESSION['uid']}");
    list($b) = mysql_fetch_row($res);
    mysql_free_result($res);
    return $b;
}
 
function unsubscribe_nl() {
    global $globals;
    $globals->db->query("DELETE FROM newsletter_ins WHERE user_id={$_SESSION['uid']}");
}
 
function subscribe_nl() {
    global $globals;
    $globals->db->query("REPLACE INTO newsletter_ins (user_id,last) SELECT {$_SESSION['uid']}, MAX(id) FROM newsletter WHERE bits!='new'");
}
 
?>
