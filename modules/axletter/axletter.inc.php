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

class AXLetter extends MassMailer
{
    public $_body;
    public $_signature;
    public $_promo_min;
    public $_promo_max;
    public $_echeance;
    public $_date;
    public $_bits;

    function __construct($id)
    {
        parent::__construct('axletter/letter.tpl', 'ax.css', 'ax/show');
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
                                    WHERE  id = {?} OR shortname = {?}", $id, $id);
            }
            if (!$res->numRows()) {
                $this->_id = null;
                return;
            }
            $id = $res->fetchOneRow();
        }
        list($this->_id, $this->_shortname, $this->_title_mail, $this->_title,
             $this->_body, $this->_signature, $this->_promo_min, $this->_promo_max,
             $this->_echeance, $this->_date, $this->_bits) = $id;
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

    static public function create($subject, $title, $body, $signature, $promo_min, $promo_max, $date, $shortname = null)
    {
        $id = AXLetter::awaiting();
        if ($id) {
            return new AXLetter($id);
        }
        XDB::execute("INSERT INTO  axletter (shortname, echeance,  promo_min, promo_max,
                                     subject, title, body, signature,
                                     subject_ini, title_ini, body_ini, signature_ini)
                           VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})",
                     $shortname, $date, $promo_min, $promo_max,
                     $subject, $title, $body, $signature, $subject, $title, $body, $signature);
        return new AXLetter(XDB::insertId());
    }

    public function update($subject, $title, $body, $signature, $promo_min, $promo_max, $date, $shortname = null)
    {
        $this->_shortname  = $shortname;
        $this->_title      = $title;
        $this->_title_mail = $subject;
        $this->_body       = $body;
        $this->_signature  = $signature;
        $this->_promo_min  = $promo_min;
        $this->_promo_max  = $promo_max;
        $this->_date       = $date;
        return XDB::execute("UPDATE  axletter (shortname, subject, title, body, signature, promo_min, promo_max, echeance)
                                SET  shorname={?}, subject={?}, title={?}, body={?}, signature={?},
                                     promo_min={?}, promo_max={?}, echeance={?}
                              WHERE  id = {?}",
                            $shortname, $subject, $title, $body, $signature, $promo_min, $promo_max, $date, $this->_id);
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
    
    static public function unsubscribe($uid = null)
    {
        $user = is_null($uid) ? S::v('uid') : $uid;
        XDB::execute("DELETE FROM  axletter_ins
                            WHERE  user_id={?} OR hash = {?}", $user, $user);
    }

    static public function subscribe($uid = null)
    {
        $user = is_null($uid) ? S::v('uid') : $uid;
        XDB::execute("REPLACE INTO  axletter_ins (user_id,last)
                            VALUES  ({?}, 0)", $user);
    }

    static public function hasPerms()
    {
        if (S::has_perms()) {
            return true;
        }
        $res = XDB::query("SELECT  1
                             FROM  axletter_rights
                            WHERE  user_id = {?}", S::i('uid'));
        return $res->fetchOneCell();
    }

    protected function subscriptionTable()
    {
        return 'axletter_ins';
    }

    protected function subscriptionWhere()
    {
        return 'ni.last';
    }

    static public function awaiting()
    {
        $res = XDB::query("SELECT  id
                             FROM  axletter
                            WHERE  FIND_IN_SET('new', bits)");
        return $res->fetchOneCell();
    }

    static public function listSent()
    {   
        $res = XDB::query("SELECT  IF(shortname IS NULL, id, shortname) as id, date, subject AS titre
                             FROM  axletter
                            WHERE  NOT (FIND_IN_SET('new', bits))
                         ORDER BY  date DESC");
        return $res->fetchAllAssoc();
    }
    
    static public function listAll()
    {   
        $res = XDB::query("SELECT  IF(shortname IS NULL, id, shortname) as id, date, subject AS titre
                             FROM  axletter
                         ORDER BY  date DESC");
        return $res->fetchAllAssoc();
    }
}

// vim:set et sw=4 sts=4 sws=4:
?>
