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
        $Id: newsletter.inc.php,v 1.6 2004-10-16 17:49:38 x2000habouzit Exp $
 ***************************************************************************/


define('FEMME', 1);
define('HOMME', 0);

function enriched_to_text($input,$html=false) {
    $text = stripslashes(trim($input));
    if($html) {
	$text = htmlspecialchars($text);
	$text = str_replace('[b]','<strong>', $text);
	$text = str_replace('[/b]','</strong>', $text);
	$text = str_replace('[i]','<em>', $text);
	$text = str_replace('[/i]','</em>', $text);
	$text = str_replace('[u]','<span style="text-decoration: underline">', $text);
	$text = str_replace('[/u]','</span>', $text);
	$text = preg_replace('!((https?|ftp)://[^\r\n\t ]*)!','<a href="\1">\1</a>', $text);
	$text = preg_replace('!([a-zA-Z0-9\-_+.]*@[a-zA-Z0-9\-_+.]*)!','<a href="mailto:\1">\1</a>', $text);
	return nl2br($text);
    } else {
	$text = preg_replace('!\[\/?b\]!','*',$text);
	$text = preg_replace('!\[\/?u\]!','_',$text);
	$text = preg_replace('!\[\/?i\]!','/',$text);
	$text = preg_replace('!((https?|ftp)://[^\r\n\t ]*)!','[\1]', $text);
	$text = preg_replace('!([a-zA-Z0-9\-_+.]*@[a-zA-Z0-9\-_+.]*)!','[mailto:\1]', $text);
	return wordwrap($text, 68);
    }
}


class NewsLetter {
    var $_id;
    var $_cats;
    
    function NewsLetter($id=null) {
	global $globals;

	if(isset($id)) {
	    $res = $globals->db->query("SELECT * FROM newsletter WHERE id='$id'");
	} else {
	    $res = $globals->db->query("SELECT * FROM newsletter WHERE bits='new'");
	}
	$nl = mysql_fetch_assoc($res);
	$this->_id = $nl['id'];
	mysql_free_result($res);

	$res = $globals->db->query("SELECT cid,titre FROM newsletter_cat ORDER BY pos");
	$this->_cats = Array();
	while(list($cid,$title) = mysql_fetch_row($res)) {
	    $this->_cats[$cid] = $title;
	}
	mysql_free_result($res);
    
    }


    function saveArticle(&$a) {
	global $globals;
	if($a->_aid) {
	    $globals->db->query("REPLACE INTO newsletter_art (id,aid,cid,pos,title,body.append)
				VALUES({$this->_id},{$a->_aid},{$a->_cid},{$a->_pos},
				       '{$a->_title}','{$a->_body}','{$a->_append}')");
	} else {
	    $globals->db->query(
		"INSERT INTO  newsletter_art
		      SELECT  {$this->_id},MAX(aid)+1,0,100,'{$a->_title}','{$a->_body}','{$a->_append}'
			FROM  newsletter_art AS a
		       WHERE  a.id={$this->_id}");
	}
    }
}

class NLArticle {
    var $_aid;
    var $_cid;
    var $_pos;
    var $_title;
    var $_body;
    var $_append;
    
    function NLArticle($title,$body,$append,$aid=null,$cid=0,$pos=100) {
	$this->_body   = $body;
	$this->_title  = $title;
	$this->_append = $append;
	$this->_aid = $aid;
	$this->_cid = $aid;
	$this->_pos = $aid;
    }

    function body() { return stripslashes(trim($this->_body)); }
    function append() { return stripslashes(trim($this->_append)); }

    function toText() {
	$title = '*'.stripslashes($this->_title).'*';
	$body  = enriched_to_text($this->_body);
	$app   = enriched_to_text($this->_append);
	return trim("$title\n\n$body\n\n$app")."\n";
    }

    function toHtml() {
	$title = '<strong>'.stripslashes($this->_title).'</strong>';
	$body  = enriched_to_text($this->_body,true);
	$app   = enriched_to_text($this->_append,true);
	
	$art = "$title<br /><br />$body<br />";
	if ($app) $art .= "<br />$app<br />";
	
	return $art;
    }

    function check() {
	$text = enriched_to_text($this->_body);
	$arr = explode("\n",wordwrap($text,68));
	$c = 0;
	foreach($arr as $line) if(trim($line)) $c++;
	return $c<9;
    }
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
