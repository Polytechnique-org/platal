<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

global $tidy_config;
$tidy_config = array(
    'drop-empty-paras' => true,
    'drop-font-tags' => true,
    'drop-proprietary-attributes' => true,
    'hide-comments' => true,
    'logical-emphasis' => true,
    'output-xhtml' => true,
    'replace-color' => true,
    'show-body-only' => true,
    'clean' => false,
    'join-styles' => false,
    'join-classes' => false,
    'alt-text' => '[ inserted by TIDY ]',
    'wrap' => '120');

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
    global $tidy_config;
    return tidy_repair_string($content, $tidy_config, 'utf8');
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
