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

$tidy_on = Array(
    'drop-empty-paras',
    'drop-font-tags',
    'drop-proprietary-attributes',
    'hide-comments',
    'logical-emphasis',
    'output-xhtml',
    'replace-color',
    'show-body-only'
);
$tidy_off = Array(
    'clean',
    'join-styles',
    'join-classes'
);

foreach($tidy_on as $opt) { tidy_setopt($opt, true); }
foreach($tidy_off as $opt) { tidy_setopt($opt, false); }
tidy_setopt('alt-text', '[ inserted by TIDY ]');
tidy_setopt('wrap', '120');
unset($tidy_o, $tydy_off);

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     block.min_perms.php
 * Type:     block
 * Name:     min_perms
 * Purpose:  
 * -------------------------------------------------------------
 */
function smarty_block_tidy($params, $content, &$smarty)
{
    return tidy_repair_string($content);
}

?>
