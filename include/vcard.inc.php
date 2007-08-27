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

require_once('xorg.misc.inc.php');
require_once('user.func.inc.php');

class VCardIterator implements PlIterator
{
    private $user_list = array();
    private $count     = 0;
    private $freetext  = null;
    private $photos    = true;

    public function __construct($photos, $freetext)
    {
        $this->freetext = $freetext;
        $this->photos   = $photos;
    }

    public function add_user($user)
    {
        $this->user_list[] = get_user_forlife($user);
        $this->count++;
    }

    public function first()
    {
        return count($this->user_list) == $this->count - 1;
    }

    public function last()
    {
        return count($this->user_list) == 0;
    }

    public function total()
    {
        return $this->count;
    }

    public function next()
    {
        if (!$this->user_list) {
            return null;
        }
        global $globals;
        $login = array_shift($this->user_list);
        $user  = get_user_details($login);

        if (strlen(trim($user['freetext']))) {
            $user['freetext'] = pl_entity_decode($user['freetext']);
        }
        if ($this->freetext) {
            if (strlen(trim($user['freetext']))) {
                $user['freetext'] = $this->freetext . "\n" . $user['freetext'];
            } else {
                $user['freetext'] = $this->freetext;
            }
        }

        // alias virtual
        $res = XDB::query(
                "SELECT alias
                   FROM virtual
             INNER JOIN virtual_redirect USING(vid)
             INNER JOIN auth_user_quick  ON ( user_id = {?} AND emails_alias_pub = 'public' )
                  WHERE ( redirect={?} OR redirect={?} )
                        AND alias LIKE '%@{$globals->mail->alias_dom}'",
                S::v('uid'),
                $user['forlife'].'@'.$globals->mail->domain,
                $user['forlife'].'@'.$globals->mail->domain2);

        $user['virtualalias'] = $res->fetchOneCell();
        $user['gpxs_vcardjoin'] = join(',', array_map(array('VCard', 'text_encode'), $user['gpxs_name']));
        $user['binets_vcardjoin'] = join(',', array_map(array('VCard', 'text_encode'), $user['binets']));
        // get photo
        if ($this->photos) {
            $res = XDB::query(
                    "SELECT attach, attachmime
                       FROM photo   AS p
                 INNER JOIN aliases AS a ON (a.id = p.uid AND a.type = 'a_vie')
                      WHERE a.alias = {?}", $login);
            if ($res->numRows()) {
                $user['photo'] = $res->fetchOneAssoc();
            }
        }
        return $user;
    }
}

class VCard
{
    private $iterator = null;

    public function __construct($users, $photos = true, $freetext = null)
    {
        $this->iterator = new VCardIterator($photos, $freetext);
        if (is_array($users)) {
            foreach ($users as $user) {
                $this->iterator->add_user($user);
            }
        } else {
            $this->iterator->add_user($users);
        }
    }

    public static function escape($text)
    {
        return preg_replace('/[,;]/', '\\\\$0', $text);
    }

    public static function format_adr($params, &$smarty)
    {
        // $adr1, $adr2, $adr3, $postcode, $city, $region, $country
        extract($params['adr']);
        $adr = trim($adr1);
        $adr = trim("$adr\n$adr2");
        $adr = trim("$adr\n$adr3");
        return VCard::text_encode(';;'
                . VCard::escape($adr) . ';'
                . VCard::escape($city) . ';'
                . VCard::escape($region) . ';'
                . VCard::escape($postcode) . ';'
                . VCard::escape($country), false);
    }

    public static function text_encode($text, $escape = true)
    {
        if (is_array($text)) {
            return implode(',', array_map(array('VCard', 'text_encode'), $text));
        }
        if ($escape) {
            $text = VCard::escape($text);
        }
        return preg_replace("/(\r\n|\n|\r)/", '\n', $text);
    }

    public function do_page(&$page)
    {
        $page->changeTpl('core/vcard.tpl', NO_SKIN);
        $page->register_modifier('vcard_enc',  array($this, 'text_encode'));
        $page->register_function('format_adr', array($this, 'format_adr'));
        $page->assign_by_ref('users', $this->iterator);

        header("Pragma: ");
        header("Cache-Control: ");
        header("Content-type: text/x-vcard; charset=UTF-8");
        header("Content-Transfer-Encoding: 8bit");
  }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
