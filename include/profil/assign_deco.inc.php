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


$res    = $globals->xdb->iterator("SELECT * FROM profile_medals_grades ORDER BY mid, pos");
$grades = Array();
while ($tmp = $res->next()) {
    $grades[$tmp['mid']][] = $tmp;
}

$res    = $globals->xdb->iterator("SELECT * FROM profile_medals ORDER BY type, text");
$mlist  = Array();
while ($tmp = $res->next()) {
    $mlist[$tmp['type']][] = $tmp;
}

$trad = Array('ordre' => 'Ordres ...', 'croix' => 'Croix ...', 'militaire' => 'Médailles militaires ...',
        'honneur' => 'Médailles d\'honneur', 'resistance' => 'Médailles de la résistance ...', 'prix' => 'Prix ...');

$page->gassign('grades');
$page->gassign('medals');
$page->gassign('trad');
$page->assign('medal_list', $mlist);

?>
