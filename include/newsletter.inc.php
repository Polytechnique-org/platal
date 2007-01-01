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

// {{{ requires + defines

require_once("xorg.misc.inc.php");

define('FEMME', 1);
define('HOMME', 0);

// }}}
// {{{ class MassMailer

abstract class MassMailer
{
    private $_tpl;
    private $_css;
    private $_prefix;

    public $_id;
    public $_shortname;
    public $_title;
    public $_title_mail;

    public $_head;

    function __construct($tpl, $css, $prefix)
    {
        $this->_tpl    = $tpl;
        $this->_css    = $css;
        $this->_prefix = $prefix;
    }

    public function id()
    {
        return is_null($this->_shortname) ? $this->_id : $this->_shortname;
    }

    public function title($mail = false)
    {
        return $mail ? $this->_title_mail : $this->_title;
    }

    public function head($prenom = null, $nom = null, $sexe = null, $type = 'text')
    {
        if (is_null($prenom)) {
            return $this->_head; 
        } else {
            $head = $this->_head;
            $head = str_replace('<cher>',   $sexe ? 'Chère' : 'Cher', $head);
            $head = str_replace('<prenom>', $prenom, $head);
            $head = str_replace('<nom>',    $nom,    $head);
            if ($type == 'text') {
                $head = enriched_to_text($head, false, true, 2, 64);
            } else {
                $head = enriched_to_text($head, true);
            }
            return $head;
        }
    }

    public function css(&$page = null)
    {
        if (!is_null($page)) {
            $page->addCssLink($this->_css);
            return true;
        } else {
            $css = file_get_contents(dirname(__FILE__) . '/../htdocs/css/' . $this->_css);
            return preg_replace('@/\*.*?\*/@s', '', $css);
        }
    }

    public function toText(&$page, $prenom, $nom, $sexe)
    {
        $this->css($page);
        $page->assign('is_mail', false);
        $page->assign('html_version', false);
        $page->assign('prenom', $prenom);
        $page->assign('nom', $nom);
        $page->assign('sexe', $sexe);
        $this->assignData($page);
    }

    public function toHtml(&$page, $prenom, $nom, $sexe)
    {
        $this->css($page);
        $page->assign('prefix', $this->_prefix . '/' . $this->id());
        $page->assign('is_mail', false);
        $page->assign('html_version', true);
        $page->assign('prenom', $prenom);
        $page->assign('nom', $nom);
        $page->assign('sexe', $sexe);
        $this->assignData($page);
    }

    public function sendTo($prenom, $nom, $login, $sexe, $html)
    {
        global $globals;

        $mailer = new PlMailer($this->_tpl);
        $this->assignData($mailer);
        $mailer->assign('is_mail', true);
        $mailer->assign('prenom',  $prenom);
        $mailer->assign('nom',     $nom);
        $mailer->assign('sexe',    $sexe);
        $mailer->assign('prefix',  null);
        $mailer->addTo("\"$prenom $nom\" <$login@{$globals->mail->domain}>");
        $mailer->send($html);
    }

    public function sendToAll()
    {
        $this->setSent();
        $query = "SELECT  u.user_id, a.alias,
                          u.prenom, IF(u.nom_usage='', u.nom, u.nom_usage),
                          FIND_IN_SET('femme', u.flags),
                          q.core_mail_fmt AS pref
                    FROM  {$this->subscriptionTable()}  AS ni
              INNER JOIN  auth_user_md5   AS u  USING(user_id)
              INNER JOIN  auth_user_quick AS q  ON(q.user_id = u.user_id)
              INNER JOIN  aliases         AS a  ON(u.user_id=a.id AND FIND_IN_SET('bestalias',a.flags))
               LEFT JOIN  emails          AS e  ON(e.uid=u.user_id AND e.flags='active')
                   WHERE  ({$this->subscriptionWhere()}) AND e.email IS NOT NULL
                GROUP BY  u.user_id
                   LIMIT  60";
        while (true) {
            $res = XDB::iterRow($query);
            if (!$res->total()) {
                exit;
            }
            $sent = array();
            while (list($uid, $bestalias, $prenom, $nom, $sexe, $fmt) = $res->next()) {
                $sent[] = "user_id='$uid'";
                $this->sendTo($prenom, $nom, $bestalias, $sexe, $fmt=='html');
            }
            XDB::execute("UPDATE  {$this->subscriptionTable()}
                             SET  {$this->subscriptionUpdate()}
                           WHERE " . implode(' OR ', $sent));
            sleep(60);
        }
    }

    abstract protected function assignData(&$smarty);
    abstract protected function setSent();
    abstract static public function subscribe($uid = -1);
    abstract static public function unsubscribe($uid = -1);
    abstract static public function subscriptionState($uid = -1);

    abstract protected function subscriptionTable();
    abstract protected function subscriptionWhere();
    abstract protected function subscriptionUpdate(); 
}

// }}}
// {{{ class NewsLetter

class NewsLetter extends MassMailer
{
    public $_date;
    public $_cats = Array();
    public $_arts = Array();

    function __construct($id = null)
    {
        parent::__construct('newsletter/nl.tpl', 'nl.css', 'nl/show');
        if (isset($id)) {
            if ($id == 'last') {
                $res = XDB::query("SELECT MAX(id) FROM newsletter WHERE bits!='new'");
                $id  = $res->fetchOneCell();
            }
            $res = XDB::query("SELECT * FROM newsletter WHERE id={?} OR short_name={?} LIMIT 1", $id, $id);
        } else {
            $res = XDB::query("SELECT * FROM newsletter WHERE bits='new'");
            if (!$res->numRows()) {
                Newsletter::create();
            }
            $res = XDB::query("SELECT * FROM newsletter WHERE bits='new'");
        }
        $nl = $res->fetchOneAssoc();

        $this->_id        = $nl['id'];
        $this->_shortname = $nl['short_name'];
        $this->_date      = $nl['date'];
        $this->_title     = $nl['titre'];
        $this->_title_mail = $nl['titre_mail'];
        $this->_head      = $nl['head'];

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

    public function save()
    {
        XDB::execute('UPDATE newsletter SET date={?},titre={?},titre_mail={?},head={?},short_name={?} WHERE id={?}',
                     $this->_date, $this->_title, $this->_title_mail, $this->_head, $this->_shortname,$this->_id);
    }
  
    public function getArt($aid)
    {
        foreach ($this->_arts as $key=>$artlist) {
            if (isset($artlist["a$aid"])) {
                return $artlist["a$aid"];
            }
        }
        return null;
    }

    public function saveArticle(&$a)
    {
        if ($a->_aid>=0) {
            XDB::execute('REPLACE INTO  newsletter_art (id,aid,cid,pos,title,body,append)
                                VALUES  ({?},{?},{?},{?},{?},{?},{?})',
                          $this->_id, $a->_aid, $a->_cid, $a->_pos,
                          $a->_title, $a->_body, $a->_append);
                          $this->_arts['a'.$a->_aid] = $a;
        } else {
            XDB::execute('INSERT INTO  newsletter_art
                               SELECT  {?},MAX(aid)+1,{?},'.($a->_pos ? intval($a->_pos) : 'MAX(pos)+1').',{?},{?},{?}
                                 FROM  newsletter_art AS a
                                WHERE  a.id={?}',
                         $this->_id, $a->_cid, $a->_title, $a->_body, $a->_append, $this->_id);
                         $this->_arts['a'.$a->_aid] = $a;
        }
    }
   
    public function delArticle($aid)
    {
        XDB::execute('DELETE FROM newsletter_art WHERE id={?} AND aid={?}', $this->_id, $aid);
        foreach ($this->_arts as $key=>$art) {
            unset($this->_arts[$key]["a$aid"]);
        }
    }

    protected function assignData(&$smarty)
    {
        $smarty->assign_by_ref('nl', $this);
    }

    protected function setSent()
    {
        XDB::execute("UPDATE  newsletter SET bits='sent' WHERE id={?}", $this->_id);
    }

    protected function subscriptionTable()
    {
        return 'newsletter_ins';
    }

    protected function subscriptionWhere()
    {
        return 'ni.last<' . $this->_id;
    }

    protected function subscriptionUpdate()
    {
        return 'last=' . $this->_id;
    }

    static public function subscriptionState($uid = -1)
    {
        $user = ($uid == -1) ? S::v('uid') : $uid;
        $res = XDB::query('SELECT 1 FROM newsletter_ins WHERE user_id={?}', $user);
        return $res->fetchOneCell();
    }

    static public function unsubscribe($uid = -1)
    {
        $user = ($uid == -1) ? S::v('uid') : $uid;
        XDB::execute('DELETE FROM newsletter_ins WHERE user_id={?}', $user);
    }

    static public function subscribe($uid = -1)
    {
        $user = ($uid == -1) ? S::v('uid') : $uid;
        XDB::execute('REPLACE INTO  newsletter_ins (user_id,last) VALUES  ({?}, 0)', $user);
    }

    static public function create()
    {
        XDB::execute("INSERT INTO newsletter
                              SET bits='new',date=NOW(),titre='to be continued',titre_mail='to be continued'");
    }

    static public function listSent()
    {
        $res = XDB::query("SELECT  IF(short_name IS NULL, id,short_name) as id,date,titre_mail AS titre
                             FROM  newsletter
                            WHERE  bits!='new'
                            ORDER  BY date DESC");
        return $res->fetchAllAssoc();
    }

    static public function listAll()
    {
        $res = XDB::query("SELECT  IF(short_name IS NULL, id,short_name) as id,date,titre_mail AS titre
                             FROM  newsletter
                         ORDER BY  date DESC");
        return $res->fetchAllAssoc();
    }
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
    
    function __construct($title='', $body='', $append='', $aid=-1, $cid=0, $pos=0)
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

    public function title()
    { return trim($this->_title); }

    // }}}
    // {{{ function body()
    
    public function body()
    { return trim($this->_body); }
    
    // }}}
    // {{{ function append()
    
    public function append()
    { return trim($this->_append); }

    // }}}
    // {{{ function toText()

    public function toText()
    {
        $title = '*'.$this->title().'*';
        $body  = enriched_to_text($this->_body,false,true);
        $app   = enriched_to_text($this->_append,false,false,4);
        return trim("$title\n\n$body\n\n$app")."\n";
    }

    // }}}
    // {{{ function toHtml()

    public function toHtml()
    {
        $title = "<h2 class='xorg_nl'><a id='art{$this->_aid}'></a>".htmlentities($this->title()).'</h2>';
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

    public function check()
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
        require_once('url_catcher.inc.php');
        $text = url_catcher($text);
        return nl2br($text);
    } else {
        $text = preg_replace('!\[\/?b\]!','*',$text);
        $text = preg_replace('!\[\/?u\]!','_',$text);
        $text = preg_replace('!\[\/?i\]!','/',$text);
        $text = preg_replace('!(((https?|ftp)://|www\.)[^\r\n\t ]*)!','[\1]', $text);
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
