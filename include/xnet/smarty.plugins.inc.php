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

// {{{  function list_all_my_groups

require_once $globals->spoolroot.'/plugins/function.rel.php';

function list_all_my_groups($params)
{
    if (!logged()) {
        return;
    }
    global $globals;
    $res = $globals->xdb->iterRow(
            "SELECT  a.nom, a.diminutif
               FROM  groupex.asso    AS a
         INNER JOIN  groupex.membres AS m ON m.asso_id = a.id
              WHERE  m.uid={?}", Session::getInt('uid'));
    $html = '<div>Mes groupes :</div>';
    while (list($nom, $mini) = $res->next()) {
        $html .= "<a class='gp' href='".smarty_function_rel()."/$mini/asso.php'>&bull; $nom</a>";
    }
    return $html;
}

// }}}
// {{{ cat_pp

function cat_pp($cat)
{
    $trans = array(
        'groupesx' => 'Groupes X' ,
        'binets'   => 'Binets' ,
        'institutions' => 'Institutions' ,
        'promotions' => 'Promotions'
    );

    return $trans[strtolower($cat)];
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
