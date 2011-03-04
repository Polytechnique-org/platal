<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

function smarty_insert_getUsername()
{
    $id = Cookie::i('uid', -1);
    $id = S::v('uid', $id);

    if ($id < 0) {
        return '';
    }

    if (Cookie::v('domain', 'login') != 'alias') {
        return XDB::fetchOneCell('SELECT  email
                                    FROM  email_source_account
                                   WHERE  uid = {?} AND type != \'alias_aux\' AND FIND_IN_SET(\'bestalias\', flags)',
                                 $id);
    } else {
        return XDB::fetchOneCell('SELECT  email
                                    FROM  email_source_account
                                   WHERE  uid = {?} AND type = \'alias_aux\'',
                                 $id);
    }

     return '';
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
