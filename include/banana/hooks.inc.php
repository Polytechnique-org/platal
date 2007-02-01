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

require_once 'banana/banana.inc.php';

function hook_formatDisplayHeader($_header, $_text, $in_spool = false)
{
    switch ($_header) {
      case 'from': case 'to': case 'cc': case 'reply-to':
        $addresses = preg_split("/ *, */", $_text);
        $text = '';
        foreach ($addresses as $address) {
            $address = BananaMessage::formatFrom(trim($address));
            if ($_header == 'from') {
                if ($id = Banana::$message->getHeaderValue('x-org-id')) {
                    return $address . ' <a href="profile/' . $id . '" class="popup2" title="' . $id . '">'
                        . '<img src="images/icons/user_suit.gif" title="fiche" alt="" /></a>';
                } elseif ($id = Banana::$message->getHeaderValue('x-org-mail')) {
                    list($id, $domain) = explode('@', $id);
                    return $address . ' <a href="profile/' . $id . '" class="popup2" title="' . $id . '">'
                        . '<img src="images/icons/user_suit.gif" title="fiche" alt="" /></a>';
                } else {
                    return $address;
                }    
            }
            if (!empty($text)) {
                $text .= ', ';
            }
            $text .= $address;
        }
        return $text;

      case 'subject':
        $link = null;
        $text = stripslashes($_text);
        if (preg_match('/^(.+?)\s*\[=> (.*?)\]\s*$/u', $text, $matches)) {
            $text = $matches[1];
            $group = $matches[2];
            if (Banana::$group == $group) {
                $link = ' [=>&nbsp;' . $group . ']';
            } else {
                $link = ' [=>&nbsp;' . Banana::$page->makeLink(array('group' => $group, 'text' => $group)) . ']';
            }
        }
        $text = banana_catchFormats(banana_htmlentities($text));
        if ($in_spool) {
            return array($text, $link);
        }
        return $text . $link;
    }
    return null;
}

function hook_platalMessageLink($params)
{
    $base = '';
    if (isset($params['first'])) {
        return $base . '/from/' . $params['first'];
    }
    if (isset($params['artid'])) {
        if (@$params['part'] == 'xface') {
            $base .= '/xface';
        } elseif (@$params['action'] == 'new') {
            $base .= '/reply';
        } elseif (@$params['action'] == 'cancel') {
            $base .= '/cancel';
        } elseif (@$params['part']) {
            if (strpos($params['part'], '.') !== false) {
                $params['artid'] .= '?part=' . urlencode($params['part']);
                $base = '/read';
            } else {
                $base .= '/' . str_replace('/', '.', $params['part']);
            }
        } else {
            $base .= '/read';
        }
        return $base . '/' . $params['artid'];
    }

    if (@$params['action'] == 'new') {
        return $base . '/new';
    }
    return $base;
}

function hook_makeImg($img, $alt, $height, $width)
{
    $url = 'images/banana/' . $img;

    if (!is_null($width)) {
        $width = ' width="' . $width . '"';
    }
    if (!is_null($height)) {
        $height = ' height="' . $height . '"';
    }

    return '<img src="' . $url . '"' . $height . $width . ' alt="' . $alt . '" />';
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
