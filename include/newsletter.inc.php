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

require_once("massmailer.inc.php");

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
        XDB::execute("UPDATE newsletter  SET bits='sent' WHERE id={?}", $this->_id);
    }   

    static public function subscriptionState($uid = null)
    {
        $user = is_null($uid) ? S::v('uid') : $uid;
        $res = XDB::query("SELECT  1
                             FROM  newsletter_ins
                            WHERE  user_id={?}", $user);
        return $res->fetchOneCell();
    }   
    
    static public function unsubscribe($uid = null)
    {
        $user = is_null($uid) ? S::v('uid') : $uid;
        XDB::execute("DELETE FROM  newsletter_ins
                            WHERE  user_id={?}", $user);
    }

    static public function subscribe($uid = null)
    {
        $user = is_null($uid) ? S::v('uid') : $uid;
        XDB::execute("REPLACE INTO  newsletter_ins (user_id,last)
                            VALUES  ({?}, 0)", $user);
    }

    protected function subscriptionTable()
    {
        return 'newsletter_ins';
    }

    protected function subscriptionWhere()
    {
        return '1';
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

// vim:set et sw=4 sts=4 sws=4 enc=utf-8:
?>
