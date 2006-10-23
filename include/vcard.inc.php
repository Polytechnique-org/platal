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

require_once('xorg.misc.inc.php');
require_once('user.func.inc.php');

class VCard
{
    var $users = array();
    var $photos;

    function VCard($users, $photos = true, $freetext = null)
    {
        $this->photos = $photos;
        if (is_array($users)) {
            foreach ($users as $user) {
                $this->add_user($user, $freetext);
            }
        } else {
            $this->add_user($users, $freetext);
        }
    }

    function escape($text)
    {
        return preg_replace('/[,;:]/', '\\\\$0', $text);
    }

    function format_adr($params, &$smarty)
    {
        // $adr1, $adr2, $adr3, $postcode, $city, $region, $country
        extract($params['adr']);
        $adr = trim($adr1);
        $adr = trim("$adr\n$adr2");
        $adr = trim("$adr\n$adr3");
        return $this->text_encode(';;'
                . $this->escape($adr) . ';'
                . $this->escape($city) . ';'
                . $this->escape($region) . ';'
                . $this->escape($postcode) . ';'
                . $this->escape($country), false);
    }

    function text_encode($text, $escape = true)
    {
        if ($escape) {
            $text = $this->escape($text);
        }
        return preg_replace("/(\r\n|\n|\r)/", '\n', $text);
    }

    function add_user($x, $freetext)
    {
        global $globals;

        $login = get_user_forlife($x);
        $user  = get_user_details($login);

        if (strlen(trim($user['freetext']))) {
            $user['freetext'] = html_entity_decode($user['freetext']);
        }
        if (!is_null($freetext)) {
            if (strlen(trim($user['freetext']))) {
                $user['freetext'] = $freetext . "\n" . $user['freetext'];
            } else {
                $user['freetext'] = $freetext;
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
        $this->users[] = $user;
    }

    function do_page(&$page)
    {
        $page->changeTpl('vcard.tpl', NO_SKIN);
        $page->register_modifier('vcard_enc',  array($this, 'text_encode'));
        $page->register_function('format_adr', array($this, 'format_adr'));
        $page->assign_by_ref('users', $this->users);

        header("Pragma: ");
        header("Cache-Control: ");
        header("Content-type: text/x-vcard; charset=iso-8859-15");
        header("Content-Transfer-Encoding: 8bit");
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
