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
        $Id: newsletter.inc.php,v 1.28 2004-11-02 11:41:33 x2000habouzit Exp $
 ***************************************************************************/

define('FEMME', 1);
define('HOMME', 0);

require_once("xorg.misc.inc.php");
require_once("diogenes.misc.inc.php");

class NewsLetter {
    var $_id;
    var $_date;
    var $_title;
    var $_head;
    var $_cats = Array();
    var $_arts = Array();
    
    function NewsLetter($id=null) {
	global $globals;

	if(isset($id)) {
	    if($id == 'last') {
		$res = $globals->db->query("SELECT MAX(id) FROM newsletter WHERE bits!='new'");
		list($id) = mysql_fetch_row($res);
	    }
	    $res = $globals->db->query("SELECT * FROM newsletter WHERE id='$id'");
	} else {
	    $res = $globals->db->query("SELECT * FROM newsletter WHERE bits='new'");
	}
	$nl = mysql_fetch_assoc($res);
	$this->_id = $nl['id'];
	$this->_date = $nl['date'];
	$this->_title = $nl['titre'];
	$this->_head = $nl['head'];
	mysql_free_result($res);

	$res = $globals->db->query("SELECT cid,titre FROM newsletter_cat ORDER BY pos");
	while(list($cid,$title) = mysql_fetch_row($res)) {
	    $this->_cats[$cid] = $title;
	}
	mysql_free_result($res);
	
	$res = $globals->db->query("SELECT  a.title,a.body,a.append,a.aid,a.cid,a.pos
				      FROM  newsletter_art AS a
				INNER JOIN  newsletter     AS n USING(id)
				LEFT  JOIN  newsletter_cat AS c ON(a.cid=c.cid)
				     WHERE  a.id=$id
				  ORDER BY  c.pos,a.pos");
	while(list($title,$body,$append,$aid,$cid,$pos) = mysql_fetch_row($res)) {
	    $this->_arts[$cid]["a$aid"] = new NLArticle($title,$body,$append,$aid,$cid,$pos);
	}
	mysql_free_result($res);
    }

    function setSent() {
	global $globals;
	$globals->db->query("UPDATE  newsletter SET bits='sent' WHERE id='{$this->_id}'");
    }

    function save() {
	global $globals;
	$globals->db->query("UPDATE  newsletter
				SET  date='{$this->_date}',titre='{$this->_title}',head='{$this->_head}'
			      WHERE  id='{$this->_id}'");
    }

    function title($mail_enc=false) {
	$title = stripslashes($this->_title);
	return $mail_enc ? "=?ISO-8859-1?Q?".quoted_printable_encode(str_replace(' ','_',$title),200)."?=" : $title;;
    }
    function head() { return stripslashes($this->_head); }

    function getArt($aid) {
	foreach($this->_arts as $key=>$artlist) {
	    if(isset($artlist["a$aid"])) return $artlist["a$aid"];
	}
	return null;
    }

    function saveArticle(&$a) {
	global $globals;
	if($a->_aid>=0) {
	    $globals->db->query("REPLACE INTO  newsletter_art (id,aid,cid,pos,title,body,append)
				VALUES({$this->_id},{$a->_aid},{$a->_cid},{$a->_pos},
				       '{$a->_title}','{$a->_body}','{$a->_append}')");
	    $this->_arts['a'.$a->_aid] = $a;
	} else {
	    $globals->db->query(
		"INSERT INTO  newsletter_art
		      SELECT  {$this->_id},MAX(aid)+1,{$a->_cid},
			      ".($a->_pos ? $a->_pos : "MAX(pos)+1").",
			      '{$a->_title}','{$a->_body}','{$a->_append}'
			FROM  newsletter_art AS a
		       WHERE  a.id={$this->_id}");
	    $this->_arts['a'.$a->_aid] = $a;
	}
    }

    function delArticle($aid) {
	global $globals;
	$globals->db->query("DELETE FROM newsletter_art WHERE id='{$this->_id}' AND aid='$aid'");
	foreach($this->_arts as $key=>$art) {
	    unset($this->_arts[$key]["a$aid"]);
	}
    }

    function footer($html) {
	if($html) {
	    return '<div class="foot">Cette lettre est envoyée à tous les Polytechniciens sur Internet par l\'intermédiaire de Polytechnique.org.</div>'
	    .  '<div class="foot">'
	    .  '[<a href="http://www.polytechnique.org/newsletter/">archives</a>&nbsp;|&nbsp;'
	    .  '<a href="http://www.polytechnique.org/newsletter/submit.php">écrire dans la NL</a>&nbsp;|&nbsp;' 
	    .  '<a href="http://www.polytechnique.org/listes/?nl_unsub=1">ne plus recevoir</a>&nbsp;|&nbsp;'
	    .  '<a href="http://www.polytechnique.org/listes/?nl_sub=text">recevoir en texte brut</a>]'
	    .  '</div>';
	} else {
	    return "\n\n--------------------------------------------------------------------\n"
	         . "Cette lettre est envoyée à tous les Polytechniciens sur Internet par\n"
	         . "l'intermédiaire de Polytechnique.org.\n"
		 . "\n"
		 . "archives : [http://www.polytechnique.org/newsletter/]\n"
		 . "écrire   : [http://www.polytechnique.org/newsletter/submit.php]\n"
		 . "ne plus recevoir: [http://www.polytechnique.org/listes/?nl_unsub=1]\n"
		 . "recevoir en HTML: [http://www.polytechnique.org/listes/?nl_sub=html]\n";
	}
    }

    function toText($prenom,$nom,$sexe) {
	$res  = "====================================================================\n";
	$res .= ' '.$this->title()."\n";
	$res .= "====================================================================\n\n";

	$head = $this->head();
	$head = str_replace('<cher>',   $sexe ? 'Chère' : 'Cher', $head);
	$head = str_replace('<prenom>', $prenom, $head);
	$head = str_replace('<nom>',    $nom,    $head);
	$head = enriched_to_text($head,false,true,2,64);

	if($head) $res .= "\n$head\n\n\n";

	$i = 1;
	foreach($this->_arts as $cid=>$arts) {
	    $res .= "\n$i *{$this->_cats[$cid]}*\n";
	    foreach($arts as $art) {
		$res .= '- '.$art->title()."\n";
	    }
	    $i ++;
	}
	$res .= "\n\n";
	    
	foreach($this->_arts as $cid=>$arts) {
	    $res .= "--------------------------------------------------------------------\n";
	    $res .= "*{$this->_cats[$cid]}*\n";
	    $res .= "--------------------------------------------------------------------\n\n";
	    foreach($arts as $art) {
		$res .= $art->toText();
		$res .= "\n\n";
	    }
	}
	
	$res .= $this->footer(false);
	
	return $res;
    }
    
    function toHtml($prenom,$nom,$sexe,$body=false) {
	$res  = '<div class="title">'.$this->title().'</div>';
	
	$head = $this->head();
	$head = str_replace('<cher>',   $sexe ? 'Chère' : 'Cher', $head);
	$head = str_replace('<prenom>', $prenom, $head);
	$head = str_replace('<nom>',    $nom,    $head);
	$head = enriched_to_text($head,true);

	if($head) $res .= "<div class='intro'>$head</div>";


	$i = 1;
	foreach($this->_arts as $cid=>$arts) {
	    $res .= "<div class='lnk'><a href='#cat$cid'><strong>$i. {$this->_cats[$cid]}</strong></a>";
	    foreach($arts as $art) {
		$res .= "<a href='#art{$art->_aid}'>&nbsp;&nbsp;- ".htmlentities($art->title())."</a>";
	    }
	    $res .= '</div>';
	    $i ++;
	}

	foreach($this->_arts as $cid=>$arts) {
	    $res .= "<h1><a id='cat$cid'></a><span>".$this->_cats[$cid].'</span></h1>';
	    foreach($arts as $art) {
		$res .= $art->toHtml();
	    }
	}

	$res .= $this->footer(true);

	if($body) {
	    $res = <<<EOF
<html>
  <head>
    <style type="text/css">
      div.nl    { margin: auto; font-family: "Georgia","times new roman",serif; width: 60ex; text-align: justify; font-size: 10pt; }
      div.title { margin: 2ex 0ex 2ex 0ex; padding: 1ex; width: 100%; font-size: 140%; text-align: center;
		  font-weight: bold; border-bottom: 3px red solid; border-top: 3px red solid; }
      
      a[href]       { text-decoration: none; }
      a[href]:hover { text-decoration: underline; }
      
      div.lnk   { margin: 2ex 0ex 2ex 0ex; padding: 0ex 2ex 0ex 2ex; }
      div.lnk a { display: block; }
      
      h1 { margin: 6ex 0ex 4ex 0ex; padding: 2px 4ex 2px 0ex; width: 60ex; font-size: 100%;
	    border-bottom: 3px red solid; border-top: 3px red solid; }
      h2 { width: 100%; margin: 0ex 1ex 0ex 1ex; padding: 2px 0px 2px 0px; font-weight: bold; font-style: italic; font-size: 95%; }
      h1 span { font-size: 140%; padding: 2px 1ex 2px 1ex; border-bottom: 3px red solid; }
      h2 span { padding: 2px 4px 2px 4px; border-bottom: 2px yellow solid; }
      
      div.art   { padding: 2ex; margin: 0ex 1ex 2ex 1ex; width: 58ex; border-top: 2px yellow solid; }
      div.app   { padding: 2ex 3ex 0ex 3ex; width: 100%; margin: 0ex; text-align: left; font-size: 95%; }
      div.intro { padding: 2ex; }
      div.foot  { border-top: 1px #808080 dashed; font-size: 95%; padding: 1ex; color: #808080; background: inherit;
		  text-align: center; width: 100% }
    </style>
  </head>
  <body>
    <div class='nl'>
    $res
    </div>
  </body>
</html>
EOF;
	}
	return $res;
    }
    
    function sendTo($prenom,$nom,$forlife,$sex,$html) {
	require_once('diogenes.mailer.inc.php');
	$fullname = "=?ISO-8859-1?Q?".quoted_printable_encode(str_replace(' ','_',"$prenom $nom"),200)."?=";
	$mailer = new DiogenesMailer("Lettre Mensuelle Polytechnique.org <info+nlp@polytechnique.org>",
				     replace_accent("$fullname <$forlife@polytechnique.org>"),
				     $this->title(true),
				     $html);
	if($html) {
	    $mailer->addPart('text/plain; charset=iso-8859-1', 'iso-8859-1', $this->toText($prenom,$nom,$sex));
	    $mailer->addPart('text/html; charset=iso-8859-1', 'iso-8859-1', $this->toHtml($prenom,$nom,$sex,true));
	} else {
	    $mailer->setBody($this->toText($prenom,$nom,$sex));
	}
	$mailer->send();
				     
    }
}

class NLArticle {
    var $_aid;
    var $_cid;
    var $_pos;
    var $_title;
    var $_body;
    var $_append;
    
    function NLArticle($title='',$body='',$append='',$aid=-1,$cid=0,$pos=0) {
	$this->_body   = $body;
	$this->_title  = $title;
	$this->_append = $append;
	$this->_aid = $aid;
	$this->_cid = $cid;
	$this->_pos = $pos;
    }

    function title() { return stripslashes(trim($this->_title)); }
    function body() { return stripslashes(trim($this->_body)); }
    function append() { return stripslashes(trim($this->_append)); }

    function toText() {
	$title = '*'.$this->title().'*';
	$body  = enriched_to_text($this->_body,false,true);
	$app   = enriched_to_text($this->_append,false,false,4);
	return trim("$title\n\n$body\n\n$app")."\n";
    }

    function toHtml() {
	$title = "<h2><a id='art{$this->_aid}'></a><span>".htmlentities($this->title()).'</span></h2>';
	$body  = enriched_to_text($this->_body,true);
	$app   = enriched_to_text($this->_append,true);
	
	$art  = "$title\n";
	$art .= "<div class='art'>\n$body\n";
	if ($app) $art .= "<div class='app'>$app</div>";
	$art .= "</div>\n";
	
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

/////////////////////////
// functions ............
//
function insert_new_nl() {
    global $globals;
    $globals->db->query("INSERT INTO newsletter SET bits='new',date=NOW(),titre='to be continued'");
}

function get_nl_slist() {
    global $globals;
    $res = $globals->db->query("SELECT id,date,titre FROM newsletter ORDER BY date DESC");
    $ans = Array();
    while($tmp = mysql_fetch_assoc($res)) $ans[] = $tmp;
    mysql_free_result($res);
    return $ans;
}

function get_nl_list() {
    global $globals;
    $res = $globals->db->query("SELECT id,date,titre FROM newsletter WHERE bits!='new' ORDER BY date DESC");
    $ans = Array();
    while($tmp = mysql_fetch_assoc($res)) $ans[] = $tmp;
    mysql_free_result($res);
    return $ans;
}

function get_nl_state() {
    global $globals;
    $res = $globals->db->query("SELECT pref FROM newsletter_ins WHERE user_id={$_SESSION['uid']}");
    if(!(list($st) = mysql_fetch_row($res))) $st = false;
    mysql_free_result($res);
    return $st;
}
 
function unsubscribe_nl() {
    global $globals;
    $globals->db->query("DELETE FROM newsletter_ins WHERE user_id={$_SESSION['uid']}");
}
 
function subscribe_nl($html=true) {
    global $globals;
    $format = $html ? 'html' : 'text';
    $globals->db->query("REPLACE INTO  newsletter_ins (user_id,last,pref)
			       SELECT  {$_SESSION['uid']}, MAX(id), '$format'
				 FROM  newsletter WHERE bits!='new'");
}
 
function justify($text,$n) {
    $arr = split("\n",wordwrap($text,$n));
    $arr = array_map('trim',$arr);
    $res = '';
    foreach($arr as $key => $line) {
	$nxl = isset($arr[$key+1]) ? trim($arr[$key+1]) : '';
	$nxl_split = preg_split('! +!',$nxl);
	$nxw_len = count($nxl_split) ? strlen($nxl_split[0]) : 0;
	$line = trim($line);

	if(strlen($line)+1+$nxw_len < $n) {
	    $res .= "$line\n";
	    continue;
	}
	
	if(preg_match('![.:;]$!',$line)) {
	    $res .= "$line\n";
	    continue;
	}

	$tmp = preg_split('! +!',trim($line));
	$words = count($tmp);
	if($words <= 1) {
	    $res .= "$line\n";
	    continue;
	}

	$len = array_sum(array_map('strlen',$tmp));
	$empty = $n - $len;
	$sw = floatval($empty) / floatval($words-1);
	
	$cur = 0;
	$l = '';
	foreach($tmp as $word) {
	    $l .= $word;
	    $cur += $sw + strlen($word);
	    $l = str_pad($l,intval($cur+0.5));
	}
	$res .= trim($l)."\n";
    }
    return trim($res);
}


function enriched_to_text($input,$html=false,$just=false,$indent=0,$width=68) {
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
	$text = preg_replace('!(([a-zA-Z0-9\-_+.]*@[a-zA-Z0-9\-_+.]*)(?:\?[^\r\n\t ]*)?)!','<a href="mailto:\1">\2</a>', $text);
	return nl2br($text);
    } else {
	$text = preg_replace('!\[\/?b\]!','*',$text);
	$text = preg_replace('!\[\/?u\]!','_',$text);
	$text = preg_replace('!\[\/?i\]!','/',$text);
	$text = preg_replace('!((https?|ftp)://[^\r\n\t ]*)!','[\1]', $text);
	$text = preg_replace('!(([a-zA-Z0-9\-_+.]*@[a-zA-Z0-9\-_+.]*)(?:\?[^\r\n\t ]*)?)!','[mailto:\1]', $text);
	$text = $just ? justify($text,$width-$indent) : wordwrap($text,$width-$indent);
	if($indent) {
	    $ind = str_pad('',$indent);
	    $text = $ind.str_replace("\n","\n$ind",$text);
	}
	return $text;
    }
}
?>
