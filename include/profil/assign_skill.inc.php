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
        $Id: assign_skill.inc.php,v 1.1 2004-08-31 15:12:37 x2000habouzit Exp $
 ***************************************************************************/


function select_comppros_name($cproname){
    global $comppros_def, $comppros_title;
    reset($comppros_def);
    echo "<option value=\"\"".(($cproname == "")?" selected='selected'":"")."></option>";
    foreach( $comppros_def as $cid => $cname){
        if($comppros_title[$cid] == 1){
            //c'est un titre de categorie
            echo "<option value=\"$cid\"".(($cname == $cproname)?" selected='selected'":"").">$cname</option>";
        }
        else{
            echo "<option value=\"$cid\"".(($cname == $cproname)?" selected='selected'":"").">-&nbsp;$cname</option>";
        }
    }
}
function _select_comppros_name($params){
  select_comppros_name($params['competence']);
}
$page->register_function('select_competence', '_select_comppros_name');

function select_langue_name($lgname){
    global $langues_def;
    reset($langues_def);
    echo "<option value=\"\"".(($lgname == "")?" selected='selected'":"")."></option>";
    foreach( $langues_def as $lid => $lname){
        echo "<option value=\"$lid\"".(($lname == $lgname)?" selected='selected'":"").">$lname</option>";
    }
}
function _select_langue_name($params){
  select_langue_name($params['langue']);
}
$page->register_function('select_langue', '_select_langue_name');

function select_langue_level($llevel){
        global $langues_levels;
        reset($langues_levels);
        echo "<option value=\"\"".(($lgname == "")?" selected='selected'":"")."></option>";
        foreach( $langues_levels as $level => $levelname){
                echo "<option value=\"$level\"".(($llevel == $level)?" selected='selected'":"").">&nbsp;$levelname&nbsp;</option>";
        }
}
function _select_langue_level($params){
  select_langue_level($params['level']);
}
$page->register_function('select_langue_level', '_select_langue_level');

function select_comppros_level(){
        global $comppros_levels;
        reset($comppros_levels);
        foreach( $comppros_levels as $level => $levelname){
                echo "<option value=\"$level\">$levelname</option>";
        }
}
function _select_cppro_level($params){
  select_comppros_level($params['level']);
}
$page->register_function('select_competence_level', '_select_cppro_level');

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
$page->assign_by_ref('langues_level',$langues_level);
$page->assign_by_ref('langues_def',$langues_def);
$page->assign_by_ref('comppros_level',$comppros_level);
$page->assign_by_ref('comppros_def',$comppros_def);
$page->assign_by_ref('comppros_title',$comppros_title);

?>
