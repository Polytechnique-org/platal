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
     $globals->db->query("replace into applis_ins set uid={$_SESSION['uid']},aid=$appli_id1,type='$appli_type1',ordre=0");
else
     $globals->db->query("delete from applis_ins where uid={$_SESSION['uid']} and ordre=0");

if ($appli_id2>0)
     $globals->db->query("replace into applis_ins set uid={$_SESSION['uid']},aid=$appli_id2,type='$appli_type2',ordre=1");
else
     $globals->db->query("delete from applis_ins where uid={$_SESSION['uid']} and ordre=1");

$sql = "UPDATE auth_user_md5
	   SET nationalite='$nationalite',web='$web',mobile='$mobile',libre='".put_in_db($libre)."' WHERE user_id={$_SESSION['uid']}";


$globals->db->query($sql);

?>
