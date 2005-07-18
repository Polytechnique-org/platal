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

$page->assign('mentor_secteur_id_new', $mentor_secteur_id_new);
$page->assign('can_add_pays', $nb_mentor_pays < $max_mentor_pays);
$page->assign('can_add_secteurs', $nb_mentor_secteurs < $max_mentor_secteurs);
$page->assign('mentor_expertise', $mentor_expertise);
$page->assign('mentor_pid', $mentor_pid);
$page->assign('mentor_pays', $mentor_pays);
$page->assign('mentor_sid', $mentor_sid);
$page->assign('mentor_secteur', $mentor_secteur);
$page->assign('mentor_ssid', $mentor_ssid);
$page->assign('mentor_ss_secteur', $mentor_ss_secteur);

?>
