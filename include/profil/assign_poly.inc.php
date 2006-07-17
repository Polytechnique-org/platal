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

$uid = S::v('uid');
 
$res = XDB::query(
        "SELECT  text,id
           FROM  binets_ins, binets_def
          WHERE  binets_def.id=binets_ins.binet_id AND user_id={?}", $uid);
$page->assign('binets', $res->fetchAllAssoc());

$res = XDB::query(
        "SELECT  text,id
           FROM  groupesx_ins, groupesx_def
          WHERE  groupesx_def.id=groupesx_ins.gid AND guid={?}", $uid);
$page->assign('groupesx', $res->fetchAllAssoc());

$page->assign('section', $section);

?>
