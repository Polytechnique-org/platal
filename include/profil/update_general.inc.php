<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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

if ($appli_id1>0)
     $globals->xdb->execute("REPLACE INTO applis_ins SET uid= {?}, aid = {?}, type = {?}, ordre = 0", Session::getInt('uid', -1), $appli_id1, $appli_type1);
else
     $globals->xdb->execute("DELETE FROM applis_ins WHERE uid= {?} AND ordre=0", Session::getInt('uid', -1));

if ($appli_id2>0)
     $globals->xdb->execute("REPLACE INTO applis_ins SET uid= {?}, aid = {?}, type = {?}, ordre = 1", Session::getInt('uid', -1), $appli_id2, $appli_type2);
else
     $globals->xdb->execute("DELETE FROM applis_ins WHERE uid= {?} AND ordre=1", Session::getInt('uid', -1));

$globals->xdb->execute('UPDATE auth_user_quick SET profile_mobile_pub = {?}, profile_web_pub = {?}, profile_freetext_pub = {?} WHERE user_id = {?}',
    $mobile_pub, $web_pub, $freetext_pub, Session::getInt('uid', -1));
    
$sql = "UPDATE auth_user_md5
	   SET nationalite= {?} WHERE user_id= {?}";
$globals->xdb->execute($sql, $nationalite, Session::getInt('uid', -1));
$globals->xdb->execute("UPDATE auth_user_quick SET profile_nick={?}, profile_mobile={?}, profile_web={?}, profile_freetext={?} WHERE user_id = {?}", $nickname, $mobile, $web, $freetext, Session::getInt('uid', -1));

// vim:set et sws=4 sts=4 sw=4:
?>
