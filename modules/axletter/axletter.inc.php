<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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

class AXLetter extends MassMailer
{
    public $_body;
    public $_signature;
    public $_promo_min;
    public $_promo_max;
    public $_subset;
    public $_subset_to;
    public $_subset_rm;
    public $_echeance;
    public $_date;
    public $_bits;

    function __construct($id)
    {
        parent::__construct('axletter/letter.mail.tpl', 'ax.css', 'ax/show', 'axletter', 'axletter_ins');
        $this->_head = '<cher> <prenom>,';

        if (!is_array($id)) {
            if ($id == 'last') {
                $res = XDB::query("SELECT  *
                                     FROM  axletter
                                    WHERE  FIND_IN_SET('sent', bits)
                                 ORDER BY  id DESC");
            } else {
                $res = XDB::query("SELECT  *
                                     FROM  axletter
                                    WHERE  id = {?} OR short_name = {?}", $id, $id);
            }
            if (!$res->numRows()) {
                throw new MailNotFound();
            }
            $id = $res->fetchOneRow();
        }
        list($this->_id, $this->_shortname, $this->_title_mail, $this->_title,
             $this->_body, $this->_signature, $this->_promo_min, $this->_promo_max,
             $this->_subset_to, $this->_subset_rm, $this->_echeance, $this->_date, $this->_bits) = $id;
        if ($this->_date == '0000-00-00') {
            $this->_date = 0;
        }
        $this->_subset_to = ($this->_subset_to ? explode("\n", $this->_subset_to) : null);
        $this->_subset = (count($this->_subset_to) > 0);
    }

    protected function assignData(&$smarty)
    {
        $smarty->assign_by_ref('am', $this);
    }

    public function body($format)
    {
        return format_text($this->_body, $format);
    }

    public function signature($format)
    {
        return format_text($this->_signature, $format, 10);
    }

    public function valid()
    {
        return XDB::execute("UPDATE  axletter
                                SET  echeance = NOW()
                              WHERE  id = {?}", $this->_id);
    }

    public function invalid()
    {
        return XDB::execute("UPDATE  axletter
                                SET  bits = 'invalid', date = CURDATE()
                              WHERE  id = {?}", $this->_id);
    }

    protected function setSent()
    {
        XDB::execute("UPDATE  axletter
                         SET  bits='sent', date=CURDATE()
                       WHERE  id={?}", $this->_id);
    }

    static public function subscriptionState($uid = null)
    {
        $user = is_null($uid) ? S::v('uid') : $uid;
        $res = XDB::query("SELECT  1
                             FROM  axletter_ins
                            WHERE  user_id={?}", $user);
        return $res->fetchOneCell();
    }

    static public function unsubscribe($uid = null, $hash = false)
    {
        $user = is_null($uid) ? S::v('uid') : $uid;
        $field = !$hash ? 'user_id' : 'hash';
        if (is_null($uid) && $hash) {
            return false;
        }
        $res = XDB::query("SELECT *
                             FROM axletter_ins
                            WHERE $field={?}", $user);
        if (!$res->numRows()) {
            return false;
        }
        XDB::execute("DELETE FROM  axletter_ins
                            WHERE  $field = {?}", $user);
        return true;
    }

    static public function subscribe($uid = null)
    {
        $user = is_null($uid) ? S::v('uid') : $uid;
        XDB::execute("REPLACE INTO  axletter_ins (user_id,last)
                            VALUES  ({?}, 0)", $user);
    }

    static public function hasPerms()
    {
        if (S::admin()) {
            return true;
        }
        $res = XDB::query("SELECT  COUNT(*)
                             FROM  axletter_rights
                            WHERE  user_id = {?}", S::i('uid'));
        return ($res->fetchOneCell() > 0);
    }

    static public function grantPerms($uid)
    {
        if (!is_numeric($uid)) {
            $res = XDB::query("SELECT id FROM aliases WHERE alias = {?}", $uid);
            $uid = $res->fetchOneCell();
        }
        if (!$uid) {
            return false;
        }
        return XDB::execute("INSERT IGNORE INTO axletter_rights SET user_id = {?}", $uid);
    }

    static public function revokePerms($uid)
    {
        if (!is_numeric($uid)) {
            $res = XDB::query("SELECT id FROM aliases WHERE alias = {?}", $uid);
            $uid = $res->fetchOneCell();
        }
        if (!$uid) {
            return false;
        }
        return XDB::execute("DELETE FROM axletter_rights WHERE user_id = {?}", $uid);
    }

    protected function subscriptionWhere()
    {
        if (!$this->_promo_min && !$this->_promo_max && !$this->_subset) {
            return '1';
        }
        $where = array();
        if ($this->_promo_min) {
            $where[] = "((ni.user_id = 0 AND ni.promo >= {$this->_promo_min}) OR (ni.user_id != 0 AND u.promo >= {$this->_promo_min}))";
        }
        if ($this->_promo_max) {
            $where[] = "((ni.user_id = 0 AND ni.promo <= {$this->_promo_max}) OR (ni.user_id != 0 AND u.promo <= {$this->_promo_max}))";
        }
        if ($this->_subset) {
            require_once("emails.inc.php");
            $ids = ids_from_mails($this->_subset_to);
            $ids_list = implode(',', $ids);
            if(count($ids) > 0) {
                if ($this->_subset_rm) {
                    $where[] = "ni.user_id NOT IN ($ids_list)";
                } else {
                    $where[] = "ni.user_id IN ($ids_list)";
                }
            } else {
                // No valid email
                $where[] = "0";
            }
        }
        return implode(' AND ', $where);
    }

    static public function awaiting()
    {
        $res = XDB::query("SELECT  *
                             FROM  axletter
                            WHERE  FIND_IN_SET('new', bits)");
        if ($res->numRows()) {
            return new AXLetter($res->fetchOneRow());
        }
        return null;
    }

    static public function toSend()
    {
        $res = XDB::query("SELECT  *
                             FROM  axletter
                            WHERE  FIND_IN_SET('new', bits) AND echeance <= NOW() AND echeance != 0");
        if ($res->numRows()) {
            return new AXLetter($res->fetchOneRow());
        }
        return null;
    }

    static public function listSent()
    {
        $res = XDB::query("SELECT  IF(short_name IS NULL, id, short_name) as id, date, subject AS titre
                             FROM  axletter
                            WHERE  NOT FIND_IN_SET('new', bits) AND NOT FIND_IN_SET('invalid', bits)
                         ORDER BY  date DESC");
        return $res->fetchAllAssoc();
    }

    static public function listAll()
    {
        $res = XDB::query("SELECT  IF(short_name IS NULL, id, short_name) as id, date, subject AS titre
                             FROM  axletter
                         ORDER BY  date DESC");
        return $res->fetchAllAssoc();
    }
}

// vim:set et sw=4 sts=4 sws=4 enc=utf-8:
?>
