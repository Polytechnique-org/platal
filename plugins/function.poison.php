<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

function smarty_function_poison($params, $smarty) {
    if (S::logged()) {
        return '';
    }
    $count = isset($params['count']) ? $params['count'] : 20;
    $seed  = isset($params['seed']) ? $params['seed'] : date('r');
    Platal::load('poison', 'poison.inc.php');

    $emails = get_poison_emails($seed, $count);
    $str = "<textarea>";
    foreach ($emails as $email) {
        $str .= "<a href=\"mailto:$email\">$email</a> ";
    }
    return $str . '</textarea>';
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
