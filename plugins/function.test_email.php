<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

function smarty_function_test_email($params, $smarty) {
    $label = isset($params['title']) ? $params['title'] : 'Envoyer un email de test';
    $token = "'" . S::v('xsrf_token') . (isset($params['hruid']) ? "', " : "'");
    $hruid = isset($params['hruid']) ? "'" . $params['hruid'] . "'" : '';
    return '<div class="center">'
         . '  <div id="mail_sent" style="position: absolute;"></div><br />'
         . '  <div><input type="submit" name="send" value="' . $label . '" onclick="sendTestEmail(' . $token . $hruid . ')" /></div>'
         . '</div>';
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
