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
 ***************************************************************************
        $Id: gerer_listes.php,v 1.2 2004-08-31 10:03:29 x2000habouzit Exp $
 ***************************************************************************/

require('auto.prepend.inc.php');
new_admin_table_editor('listes_def','id');

$editor->add_join_table('aliases','id','aliases.type=\'liste\'');

$editor->add_join_field('aliases','alias','alias','','liste',true);
$editor->describe('topic','topic',true);
$editor->describe('type','type',true,'set');

$editor->assign('title', 'Gestion des liste des diffusion');

$editor->run();
?>
