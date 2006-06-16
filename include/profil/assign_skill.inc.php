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

$page->assign('nb_lg_max', $nb_lg_max);
$page->assign('nb_cpro_max', $nb_cpro_max);
$page->assign('nb_lg', $nb_lg);
$page->assign_by_ref('langue_id', $langue_id);
$page->assign_by_ref('langue_name', $langue_name);
$page->assign_by_ref('langue_level', $langue_level);
$page->assign('nb_cpro', $nb_cpro);
$page->assign_by_ref('cpro_id', $cpro_id);
$page->assign_by_ref('cpro_name', $cpro_name);
$page->assign_by_ref('cpro_level', $cpro_level);
$page->assign_by_ref('langues_levels',$langues_levels);
$page->assign_by_ref('langues_def',$langues_def);
$page->assign_by_ref('comppros_levels',$comppros_levels);
$page->assign_by_ref('comppros_def',$comppros_def);
$page->assign_by_ref('comppros_title',$comppros_title);

?>
