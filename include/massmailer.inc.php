<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

    protected $_table;
    protected $_subscriptionTable;

    function __construct($tpl, $css, $prefix, $tbl, $stbl)
    {
        $this->_tpl    = $tpl;
        $this->_css    = $css;
        $this->_prefix = $prefix;
        $this->_table  = $tbl;
        $this->_subscriptionTable = $stbl;
    }

    public function id()
    {
        return is_null($this->_shortname) ? $this->_id : $this->_shortname;
    }

    private function selectId($where)
    {
        $res = XDB::query("SELECT  IF (n.short_name IS NULL, n.id, n.short_name)
                             FROM  {$this->_table} AS n
                            WHERE  n.bits != 'new' AND {$where}
                            LIMIT  1");
        if ($res->numRows() != 1) {
            return null;
        }
        return $res->fetchOneCell();
    }

    public function prev()
    {
        static $val;
        if (!isset($val)) {
            $val = $this->selectId("n.id < {$this->_id} ORDER BY n.id DESC");
        }
        return $val;
    }

    public function next()
    {
        static $val;
        if (!isset($val)) {
            $val = $this->selectId("n.id > {$this->_id} ORDER BY n.id");
        }
        return $val;
    }

    public function last()
    {
        static $val;
        if (!isset($val)) {
            $res = XDB::query("SELECT  MAX(n.id)
                                 FROM  {$this->_table} AS n
                                WHERE  n.bits != 'new' AND n.id > {?}",
                              $this->_id);
            if ($res->numRows() != 1) {
                $val = null;
            } else {
                $val = $res->fetchOneCell();
            }
        }
        return $val;
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
            return preg_replace('@/\*.*?\*/@us', '', $css);
        }
    }

    public function toText(&$page, $prenom, $nom, $sexe)
    {
        $this->css($page);
        $page->assign('is_mail', false);
        $page->assign('mail_part', 'text');
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
        $page->assign('mail_part', 'html');
        $page->assign('prenom', $prenom);
        $page->assign('nom', $nom);
        $page->assign('sexe', $sexe);
        $this->assignData($page);
    }

    private function createHash($line, $key = null)
    {
        $hash = implode(time(), $line) . rand();
        $hash = md5($hash);
        return $hash;
    }

    public function sendTo($prenom, $nom, $login, $sexe, $html, $hash = 0)
    {
        global $globals;
        $alias = $login;
        if (strpos($login, '@') === false) {
            $login = "$login@{$globals->mail->domain}";
        }
        require_once('user.func.inc.php');
        $forlife = get_user_forlife($login, '_silent_user_callback');
        if ($forlife) {
            $alias = $forlife;
        }
        if (strpos($alias, '@') === false && (is_null($hash) || $hash == 0)) {

            $hash = $this->createHash(array($prenom, $nom, $login, $sexe, $html, rand(), "X.org rulez"));
            XDB::query("UPDATE  {$this->_subscriptionTable} as ni
                    INNER JOIN  aliases AS a ON (ni.user_id = a.id)
                           SET  ni.hash = {?}
                         WHERE  ni.user_id != 0 AND a.alias = {?}",
                       $hash, $alias);
        }

        $mailer = new PlMailer($this->_tpl);
        $this->assignData($mailer);
        $mailer->assign('is_mail', true);
        $mailer->assign('prenom',  $prenom);
        $mailer->assign('nom',     $nom);
        $mailer->assign('sexe',    $sexe);
        $mailer->assign('prefix',  null);
        $mailer->assign('hash',    $hash);
        $mailer->assign('email',   $login);
        $mailer->assign('alias',   $alias);
        $mailer->addTo("\"$prenom $nom\" <$login>");
        $mailer->send($html);
    }

    protected function getAllRecipients()
    {
        global $globals;
        return  "SELECT  u.user_id, CONCAT(a.alias, '@{$globals->mail->domain}'),
                         u.prenom, IF(u.nom_usage='', u.nom, u.nom_usage),
                         FIND_IN_SET('femme', u.flags),
                         q.core_mail_fmt AS pref, ni.hash AS hash
                   FROM  {$this->_subscriptionTable}  AS ni
             INNER JOIN  auth_user_md5   AS u  USING(user_id)
             INNER JOIN  auth_user_quick AS q  ON(q.user_id = u.user_id)
             INNER JOIN  aliases         AS a  ON(u.user_id=a.id AND FIND_IN_SET('bestalias',a.flags))
              LEFT JOIN  emails          AS e  ON(e.uid=u.user_id AND e.flags='active')
                  WHERE  ni.last < {?} AND ({$this->subscriptionWhere()}) AND
                         (e.email IS NOT NULL OR FIND_IN_SET('googleapps', u.mail_storage))
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
            XDB::execute("UPDATE  {$this->_subscriptionTable}
                             SET  last = {?}
                           WHERE " . implode(' OR ', $sent), $this->_id);

            sleep(60);
        }
    }

    abstract protected function assignData(&$smarty);
    abstract protected function setSent();

    abstract protected function subscriptionWhere();
}

// }}}
// {{{ Functions

function format_text($input, $format, $indent = 0, $width = 68)
{
    if ($format == 'text') {
        return MiniWiki::WikiToText($input, true, $indent, $width, "title");
    }
    return MiniWiki::WikiToHTML($input, "title");
}

// function enriched_to_text($input,$html=false,$just=false,$indent=0,$width=68)

// }}}

// vim:set et sw=4 sts=4 sws=4 enc=utf-8:
?>
