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

function iterate_end($tag_attrs, &$compiler) {
    return 'endwhile;';
}

function smarty_compiler_iterate($tag_attrs, &$compiler)
{
    static $reg = false;
    if (!$reg) {
        $reg = true;
        $compiler->register_compiler_function("/iterate", 'iterate_end');
    }

    $_params = $compiler->_parse_attrs($tag_attrs);

    if (!isset($_params['from'])) {
        $compiler->_syntax_error("iterate: missing 'from' parameter", E_USER_ERROR, __FILE__, __LINE__);
        return;
    }

    if (empty($_params['item'])) {
        $compiler->_syntax_error("iterate: missing 'item' attribute", E_USER_ERROR, __FILE__, __LINE__);
        return;
    }

    $_from = $compiler->_dequote($_params['from']);
    $_item = $compiler->_dequote($_params['item']);

    return "\$_iterate_$_item = $_from;\n"
        .  "while ((\$this->_tpl_vars['$_item'] = \$_iterate_{$_item}->next()) !== null):";
}

/* vim: set expandtab enc=utf-8: */

?>
