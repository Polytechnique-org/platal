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
require_once 'platal.inc.php';

function smarty_compiler_icon($tag_attrs, &$compiler)
{
    extract($compiler->_parse_attrs($tag_attrs));

    $alt = 'alt=""';
    
    if (isset($title)) {
        $title = pl_entities(trim($title, '\'"'), ENT_QUOTES);
        $alt = 'alt="'.$title.'"';
        $title = 'title="'. $title.'" ';
    }

    $name = pl_entities(trim($name, '\'"'), ENT_QUOTES);
    $name = "images/icons/$name.gif";
    if ($full) {
        global $globals;
        $name = $globals->baseurl . '/' . $name;
    }

    return "?><img src='$name' $alt $title /><?php";
}

/* vim: set expandtab enc=utf-8: */

?>
