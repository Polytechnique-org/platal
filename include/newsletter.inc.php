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

// {{{ requires + defines

require_once("xorg.misc.inc.php");

if (isset($page)) {
    $page->addCssLink('nl.css');
}

define('FEMME', 1);
define('HOMME', 0);

// }}}
// {{{ class NewsLetter

class NewsLetter
{
    // {{{ properties
    
    var $_id;
    var $_date;
    var $_title;
    var $_head;
    var $_cats = Array();
    var $_arts = Array();

    // }}}
    // {{{ constructor
    
    function NewsLetter($id=null)
    {
	if (isset($id)) {
	    if ($id == 'last') {
		$res = XDB::query("SELECT MAX(id) FROM newsletter WHERE bits!='new'");
                $id  = $res->fetchOneCell();
	    }
	    $res = XDB::query("SELECT * FROM newsletter WHERE id={?}", $id);
	} else {
	    $res = XDB::query("SELECT * FROM newsletter WHERE bits='new'");
        if (!$res->numRows()) {
            insert_new_nl();
        }
        $res = XDB::query("SELECT * FROM newsletter WHERE bits='new'");
    }
	$nl = $res->fetchOneAssoc();

	$this->_id    = $nl['id'];
	$this->_date  = $nl['date'];
	$this->_title = $nl['titre'];
	$this->_head  = $nl['head'];

	$res = XDB::iterRow("SELECT cid,titre FROM newsletter_cat ORDER BY pos");
	while (list($cid, $title) = $res->next()) {
	    $this->_cats[$cid] = $title;
	}
	
	$res = XDB::iterRow(
                "SELECT  a.title,a.body,a.append,a.aid,a.cid,a.pos
                   FROM  newsletter_art AS a
             INNER JOIN  newsletter     AS n USING(id)
             LEFT  JOIN  newsletter_cat AS c ON(a.cid=c.cid)
                  WHERE  a.id={?}
               ORDER BY  c.pos,a.pos", $this->_id);
	while (list($title, $body, $append, $aid, $cid, $pos) = $res->next()) {
	    $this->_arts[$cid]["a$aid"] = new NLArticle($title, $body, $append, $aid, $cid, $pos);
	}
    }

    // }}}
    // {{{ function setSent()

    function setSent()
    {
	    XDB::execute("UPDATE  newsletter SET bits='sent' WHERE id={?}", $this->_id);
    }

    // }}}
    // {{{ function save()

    function save()
    {
	XDB::execute('UPDATE newsletter SET date={?},titre={?},head={?} WHERE id={?}',
                     $this->_date, $this->_title, $this->_head, $this->_id);
    }

    // }}}
    // {{{ function title()

    function title()
    { return $this->_title; }

    // }}}
    // {{{ function head()
    
    function head()
    { return $this->_head; }

    // }}}
    // {{{ function getArt()
    
    function getArt($aid)
    {
	foreach ($this->_arts as $key=>$artlist) {
	    if (isset($artlist["a$aid"])) {
                return $artlist["a$aid"];
            }
	}
	return null;
    }

    // }}}
    // {{{ function saveArticle()

    function saveArticle(&$a)
    {
	if ($a->_aid>=0) {
	    XDB::execute('REPLACE INTO  newsletter_art (id,aid,cid,pos,title,body,append)
                                          VALUES  ({?},{?},{?},{?},{?},{?},{?})',
                                          $this->_id, $a->_aid, $a->_cid, $a->_pos,
                                          $a->_title, $a->_body, $a->_append);
	    $this->_arts['a'.$a->_aid] = $a;
	} else {
	    XDB::execute(
		'INSERT INTO  newsletter_art
		      SELECT  {?},MAX(aid)+1,{?},'.($a->_pos ? intval($a->_pos) : 'MAX(pos)+1').',{?},{?},{?}
			FROM  newsletter_art AS a
		       WHERE  a.id={?}',
                       $this->_id, $a->_cid, $a->_title, $a->_body, $a->_append, $this->_id);
	    $this->_arts['a'.$a->_aid] = $a;
	}
    }

    // }}}
    // {{{ function delArticle()
    
    function delArticle($aid)
    {
	XDB::execute('DELETE FROM newsletter_art WHERE id={?} AND aid={?}', $this->_id, $aid);
	foreach ($this->_arts as $key=>$art) {
	    unset($this->_arts[$key]["a$aid"]);
	}
    }

    // }}}
    // {{{ function footer

    function footer($html)
    {
        global $globals;
        $url = $globals->baseurl;

	if ($html) {
	    return '<div class="foot">Cette lettre est envoyée à tous les Polytechniciens sur Internet par l\'intermédiaire de Polytechnique.org.</div>'
	    .  '<div class="foot">'
	    .  "[<a href=\"$url/nl\">archives</a>&nbsp;|&nbsp;"
	    .  "<a href=\"$url/nl/submit\">écrire dans la NL</a>&nbsp;|&nbsp;"
	    .  "<a href=\"$url/nl/out\">ne plus recevoir</a>]"
	    .  '</div>';
	} else {
	    return "\n\n--------------------------------------------------------------------\n"
	         . "Cette lettre est envoyée à tous les Polytechniciens sur Internet par\n"
	         . "l'intermédiaire de Polytechnique.org.\n"
		 . "\n"
		 . "archives : [$url/nl]\n"
		 . "écrire   : [$url/nl/submit]\n"
		 . "ne plus recevoir: [$url/nl/out]\n";
	}
    }

    // }}}
    // {{{ function toText()

    function toText($prenom,$nom,$sexe)
    {
	$res  = "====================================================================\n";
	$res .= ' '.$this->title()."\n";
	$res .= "====================================================================\n\n";

	$head = $this->head();
	$head = str_replace('<cher>',   $sexe ? 'Chère' : 'Cher', $head);
	$head = str_replace('<prenom>', $prenom, $head);
	$head = str_replace('<nom>',    $nom,    $head);
	$head = enriched_to_text($head,false,true,2,64);

	if ($head) {
            $res .= "\n$head\n\n\n";
        }

	$i = 1;
	foreach ($this->_arts as $cid=>$arts) {
	    $res .= "\n$i *{$this->_cats[$cid]}*\n";
	    foreach ($arts as $art) {
		$res .= '- '.$art->title()."\n";
	    }
	    $i ++;
	}
	$res .= "\n\n";
	    
	foreach ($this->_arts as $cid=>$arts) {
	    $res .= "--------------------------------------------------------------------\n";
	    $res .= "*{$this->_cats[$cid]}*\n";
	    $res .= "--------------------------------------------------------------------\n\n";
	    foreach ($arts as $art) {
		$res .= $art->toText();
		$res .= "\n\n";
	    }
	}
	
	$res .= $this->footer(false);
	
	return $res;
    }

    // }}}
    // {{{ function toHtml()
    
    function toHtml($prenom,$nom,$sexe,$body=false)
    {
	$res  = '<div class="title">'.$this->title().'</div>';
	
	$head = $this->head();
	$head = str_replace('<cher>',   $sexe ? 'Chère' : 'Cher', $head);
	$head = str_replace('<prenom>', $prenom, $head);
	$head = str_replace('<nom>',    $nom,    $head);
	$head = enriched_to_text($head,true);

	if($head) {
            $res .= "<div class='intro'>$head</div>";
        }

	$i = 1;
	$res .= "<a id='top_lnk'></a>";
	foreach ($this->_arts as $cid=>$arts) {
	    $res .= "<div class='lnk'><a href='#cat$cid'><strong>$i. {$this->_cats[$cid]}</strong></a>";
	    foreach ($arts as $art) {
		$res .= "<a href='#art{$art->_aid}'>&nbsp;&nbsp;- ".htmlentities($art->title())."</a>";
	    }
	    $res .= '</div>';
	    $i ++;
	}

	foreach ($this->_arts as $cid=>$arts) {
	    $res .= "<h1><a id='cat$cid'></a><span>".$this->_cats[$cid].'</span></h1>';
	    foreach($arts as $art) {
    		$res .= $art->toHtml();
    		$res .= "<p><a href='#top_lnk'>Revenir au sommaire</a></p>";
	    }
	}

	$res .= $this->footer(true);

	if ($body) {
	    $res = <<<EOF
<html>
  <head>
    <style type="text/css">
    <!--
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
    -->
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

    // }}}
    // {{{ function sendTo()
    
    function sendTo($prenom, $nom, $login, $sex, $html)
    {
        global $globals;
	require_once('diogenes/diogenes.hermes.inc.php');

	$mailer = new HermesMailer();
	$mailer->setFrom($globals->newsletter->from);
	$mailer->setSubject($this->title());
	$mailer->addTo("\"$prenom $nom\" <$login@{$globals->mail->domain}>");
        if (!empty($globals->newsletter->replyto)) {
            $mailer->addHeader('Reply-To',$globals->newsletter->replyto);
        }
        if (!empty($globals->newsletter->retpath)) {
            $mailer->addHeader('Return-Path',$globals->newsletter->retpath);
        }
	$mailer->setTxtBody($this->toText($prenom,$nom,$sex));
	if ($html) {
	    $mailer->setHTMLBody($this->toHtml($prenom,$nom,$sex,true));
	}
	$mailer->send();
    }

    // }}}
}

// }}}
// {{{ class NLArticle

class NLArticle
{
    // {{{ properties
    
    var $_aid;
    var $_cid;
    var $_pos;
    var $_title;
    var $_body;
    var $_append;

    // }}}
    // {{{ constructor
    
    function NLArticle($title='', $body='', $append='', $aid=-1, $cid=0, $pos=0)
    {
	$this->_body   = $body;
	$this->_title  = $title;
	$this->_append = $append;
	$this->_aid    = $aid;
	$this->_cid    = $cid;
	$this->_pos    = $pos;
    }

    // }}}
    // {{{ function title()

    function title()
    { return trim($this->_title); }

    // }}}
    // {{{ function body()
    
    function body()
    { return trim($this->_body); }
    
    // }}}
    // {{{ function append()
    
    function append()
    { return trim($this->_append); }

    // }}}
    // {{{ function toText()

    function toText()
    {
	$title = '*'.$this->title().'*';
	$body  = enriched_to_text($this->_body,false,true);
	$app   = enriched_to_text($this->_append,false,false,4);
	return trim("$title\n\n$body\n\n$app")."\n";
    }

    // }}}
    // {{{ function toHtml()

    function toHtml()
    {
	$title = "<h2><a id='art{$this->_aid}'></a><span>".htmlentities($this->title()).'</span></h2>';
	$body  = enriched_to_text($this->_body,true);
	$app   = enriched_to_text($this->_append,true);
	
	$art   = "$title\n";
	$art  .= "<div class='art'>\n$body\n";
	if ($app) {
            $art .= "<div class='app'>$app</div>";
        }
	$art  .= "</div>\n";
	
	return $art;
    }

    // }}}
    // {{{ function check()

    function check()
    {
	$text = enriched_to_text($this->_body);
	$arr  = explode("\n",wordwrap($text,68));
	$c    = 0;
	foreach ($arr as $line) {
            if (trim($line)) {
                $c++;
            }
        }
	return $c<9;
    }

    // }}}
}

// }}}
// {{{ Functions

function insert_new_nl()
{
    XDB::execute("INSERT INTO newsletter SET bits='new',date=NOW(),titre='to be continued'");
}

function get_nl_slist()
{
    $res = XDB::query("SELECT id,date,titre FROM newsletter ORDER BY date DESC");
    return $res->fetchAllAssoc();
}

function get_nl_list()
{
    $res = XDB::query("SELECT id,date,titre FROM newsletter WHERE bits!='new' ORDER BY date DESC");
    return $res->fetchAllAssoc();
}

function get_nl_state()
{
    $res = XDB::query('SELECT 1 FROM newsletter_ins WHERE user_id={?}', S::v('uid'));
    return $res->fetchOneCell();
}
 
function unsubscribe_nl()
{
    XDB::execute('DELETE FROM newsletter_ins WHERE user_id={?}', S::v('uid'));
}
 
function subscribe_nl($uid=-1)
{
    $user = ($uid == -1) ? S::v('uid') : $uid;
    XDB::execute('REPLACE INTO  newsletter_ins (user_id,last)
                        VALUES  ({?}, 0)', $user);
}
 
function justify($text,$n)
{
    $arr = explode("\n",wordwrap($text,$n));
    $arr = array_map('trim',$arr);
    $res = '';
    foreach ($arr as $key => $line) {
	$nxl       = isset($arr[$key+1]) ? trim($arr[$key+1]) : '';
	$nxl_split = preg_split('! +!',$nxl);
	$nxw_len   = count($nxl_split) ? strlen($nxl_split[0]) : 0;
	$line      = trim($line);

	if (strlen($line)+1+$nxw_len < $n) {
	    $res .= "$line\n";
	    continue;
	}
	
	if (preg_match('![.:;]$!',$line)) {
	    $res .= "$line\n";
	    continue;
	}

	$tmp   = preg_split('! +!',trim($line));
	$words = count($tmp);
	if ($words <= 1) {
	    $res .= "$line\n";
	    continue;
	}

	$len   = array_sum(array_map('strlen',$tmp));
	$empty = $n - $len;
	$sw    = floatval($empty) / floatval($words-1);
	
	$cur = 0;
	$l   = '';
	foreach ($tmp as $word) {
	    $l   .= $word;
	    $cur += $sw + strlen($word);
	    $l    = str_pad($l,intval($cur+0.5));
	}
	$res .= trim($l)."\n";
    }
    return trim($res);
}

function enriched_to_text($input,$html=false,$just=false,$indent=0,$width=68)
{
    $text = trim($input);
    if ($html) {
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

// }}}

// vim:set et sw=4 sts=4 sws=4:
?>
