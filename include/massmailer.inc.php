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

require_once("xorg.misc.inc.php");

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
            return format_text($head, $type, 2, 64);
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

    public function sendTo($prenom, $nom, $login, $sexe, $html, $hash = 0)
    {
        global $globals;
        if (strpos($login, '@') === false) {
            $login = "$login@{$globals->mail->domain}";
        }

        $mailer = new PlMailer($this->_tpl);
        $this->assignData($mailer);
        $mailer->assign('is_mail', true);
        $mailer->assign('prenom',  $prenom);
        $mailer->assign('nom',     $nom);
        $mailer->assign('sexe',    $sexe);
        $mailer->assign('prefix',  null);
        $mailer->assign('hash',    $hash);
        $mailer->addTo("\"$prenom $nom\" <$login>");
        $mailer->send($html);
    }

    protected function getAllRecipients()
    {
        return  "SELECT  u.user_id, a.alias,
                         u.prenom, IF(u.nom_usage='', u.nom, u.nom_usage),
                         FIND_IN_SET('femme', u.flags),
                         q.core_mail_fmt AS pref, 0 AS hash
                   FROM  {$this->subscriptionTable()}  AS ni
             INNER JOIN  auth_user_md5   AS u  USING(user_id)
             INNER JOIN  auth_user_quick AS q  ON(q.user_id = u.user_id)
             INNER JOIN  aliases         AS a  ON(u.user_id=a.id AND FIND_IN_SET('bestalias',a.flags))
              LEFT JOIN  emails          AS e  ON(e.uid=u.user_id AND e.flags='active')
                  WHERE  ni.last < {?} AND ({$this->subscriptionWhere()}) AND e.email IS NOT NULL
               GROUP BY  u.user_id";
    }

    public function sendToAll()
    {
        $this->setSent();
        $query = $this->getAllRecipients() . " LIMIT {?}";
        while (true) {
            $res = XDB::iterRow($query, $this->_id, 60);
            if (!$res->total()) {
                return;
            }
            $sent = array();
            while (list($uid, $bestalias, $prenom, $nom, $sexe, $fmt, $hash) = $res->next()) {
                $sent[] = "(user_id='$uid'" . (!$uid ? " AND email='$bestalias')": ')');
                $this->sendTo($prenom, $nom, $bestalias, $sexe, $fmt=='html', $hash);
            }
            XDB::execute("UPDATE  {$this->subscriptionTable()}
                             SET  last = {?}
                           WHERE " . implode(' OR ', $sent), $this->_id);
            sleep(60);
        }
    }

    abstract protected function assignData(&$smarty);
    abstract protected function setSent();

    abstract protected function subscriptionTable();
    abstract protected function subscriptionWhere();
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

function format_text($input, $format, $indent = 0, $width = 68)
{
    if ($format == 'text') {
        return enriched_to_text($input, false, true, $indent, $width);
    }
    return enriched_to_text($input, true);
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
        $text = preg_replace("!(\\s*\n)*\[title\]!",'<h1>',$text);
        $text = preg_replace("!\[\/title\](\\s*\n)*!", '</h1>',$text);
        $text = preg_replace("!(\\s*\n)*\[subtitle\]!",'<h2>',$text);
        $text = preg_replace("!\[\/subtitle\](\\s*\n)*!",'</h2>',$text);

        require_once('url_catcher.inc.php');
        $text = url_catcher($text);
        return nl2br($text);
    } else {
        $text = preg_replace('!\[\/?b\]!','*',$text);
        $text = preg_replace('!\[\/?u\]!','_',$text);
        $text = preg_replace('!\[\/?i\]!','/',$text);
        $text = preg_replace('!\[\/?title\]!','***', $text);
        $text = preg_replace('!\[\/?subtitle\]!','**', $text);
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
