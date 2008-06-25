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

function smarty_function_display_phones($param, &$smarty)
{
    $txthtml = "";
    if (count($param['tels'])) {
        foreach ($param['tels'] as $tel) {
            switch ($tel['tel_type']) {
            case 'fixed':
                $tel_type = 'Tél';
                break;
            case 'fax':
                $tel_type = 'Fax';
                break;
            case 'mobile':
                $tel_type = 'Mob';
                break;
            default:
                $tel_type = $tel['tel_type'];
            }
            $txthtml .= "<div>\n<em>" . $tel_type . "&nbsp;: </em>\n<strong>" . $tel['tel'] . "</strong>\n";
            $comment = "";
            if ($tel['comment'] != "") {
                $commentHtml = str_replace(array('&', '"'), array('&amp;', '&quot;'), $tel['comment']);
                $commentJs = str_replace(array('\\', '\''), array('\\\\', '\\\''), $commentHtml);
                $txthtml .= "<img style=\"margin-left: 5px;\" src=\"images/icons/comments.gif\""
                            . " onmouseover=\"return overlib('"
                            . $commentJs
                            . "',WIDTH,250);\""
                            . " onmouseout=\"nd();\""
                            . " alt=\"Commentaire\" title=\""
                            . $commentHtml
                            . "\"/>\n";
            }
            $txthtml .= "</div>\n";
        }
    }
    return $txthtml;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
