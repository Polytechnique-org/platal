<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

// {{{ class MailNotFound

class MailNotFound extends Exception {
}

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

    public function head($user = null, $type = 'text')
    {
        if (is_null($user)) {
            return $this->_head;
        } else {
            $head = $this->_head;
            $head = str_replace('<cher>',   $user->isFemale() ? 'Chère' : 'Cher', $head);
            $head = str_replace('<prenom>', $user->displayName(), $head);
            $head = str_replace('<nom>', '', $head);
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

    public function toText(&$page, $user)
    {
        $this->css($page);
        $page->assign('is_mail', false);
        $page->assign('mail_part', 'text');
        $page->assign('user', $user);
        $this->assignData($page);
    }

    public function toHtml(&$page, $user)
    {
        $this->css($page);
        $page->assign('prefix', $this->_prefix . '/' . $this->id());
        $page->assign('is_mail', false);
        $page->assign('mail_part', 'html');
        $page->assign('user', $user);
        $this->assignData($page);
    }

    private function createHash($line, $key = null)
    {
        $hash = implode(time(), $line) . rand();
        $hash = md5($hash);
        return $hash;
    }

    public function sendTo($user, $hash = null)
    {
        if (is_null($hash)) {
            $hash = XDB::fetchOneCell("SELECT  hash
                                         FROM  {$this->_subscriptionTable}
                                        WHERE  uid = {?}", $user->id());
        }
        if (is_null($hash)) {
            $hash = $this->createHash(array($user->displayName(), $user->fullName(),
                                       $user->isFemale(), $user->isEmailFormatHtml(),
                                       rand(), "X.org rulez"));
            XDB::execute("UPDATE  {$this->_subscriptionTable} as ni
                             SET  ni.hash = {?}
                           WHERE  ni.uid != {?}",
                         $hash, $user->id());
        }

        $mailer = new PlMailer($this->_tpl);
        $this->assignData($mailer);
        $mailer->assign('is_mail', true);
        $mailer->assign('user', $user);
        $mailer->assign('prefix',  null);
        $mailer->assign('hash',    $hash);
        $mailer->addTo('"' . $user->fullName() . '" <' . $user->bestEmail() . '>');
        $mailer->send($user->isEmailFormatHtml());
    }

    protected function getAllRecipients()
    {
        global $globals;
        return  "SELECT  a.uid
                   FROM  {$this->_subscriptionTable}  AS ni
             INNER JOIN  accounts AS a ON (ni.uid = a.uid)
              LEFT JOIN  email_options AS eo ON (eo.uid = a.uid)
              LEFT JOIN  emails   AS e ON (e.uid = a.uid AND e.flags='active')
                  WHERE  ni.last < {?} AND ({$this->subscriptionWhere()}) AND
                         (e.email IS NOT NULL OR FIND_IN_SET('googleapps', eo.storage))
               GROUP BY  a.uid";
    }

    public function sendToAll()
    {
        $this->setSent();
        $query = XDB::format($this->getAllRecipients(), $this->_id) . ' LIMIT 60';
        $emailsCount = 0;

        while (true) {
            $users = User::getBulkUsersWithUIDs(XDB::fetchColumn($query));
            if (count($users) == 0) {
                return $emailsCount;
            }
            foreach ($users as $user) {
                $sent[] = XDB::format('uid = {?}', $user->id());
                $this->sendTo($user, $hash);
                ++$emailsCount;
            }
            print_r($sent);
            XDB::execute("UPDATE  {$this->_subscriptionTable}
                             SET  last = {?}
                           WHERE " . implode(' OR ', $sent), $this->_id);

            sleep(60);
        }
        return $emailsCount;
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
