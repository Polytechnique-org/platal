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
        $Id: profil_skill.inc.php,v 1.4 2004-08-31 11:16:48 x2000habouzit Exp $
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

if(isset($_REQUEST['langue_op']) && !$no_update_bd){
	if($_REQUEST['langue_op']=='retirer'){
		mysql_query("delete from langues_ins where uid='{$_SESSION['uid']}' and lid='{$_REQUEST['langue_id']}'");
	}
	else if($_REQUEST['langue_op'] == 'ajouter'){
		if(isset($_REQUEST['langue_id']) && ($_REQUEST['langue_id'] != ''))
		mysql_query("insert into langues_ins (uid,lid,level) VALUES('{$_SESSION['uid']}','{$_REQUEST['langue_id']}','{$_REQUEST['langue_level']}')");
	}
}

if(isset($_REQUEST['comppros_op']) && !$no_update_bd){
	if($_REQUEST['comppros_op']=='retirer'){
		mysql_query("delete from competences_ins where uid='{$_SESSION['uid']}' and cid='{$_REQUEST['comppros_id']}'");
	}
	else if($_REQUEST['comppros_op'] == 'ajouter'){
		if(isset($_REQUEST['comppros_id']) && ($_REQUEST['comppros_id'] != ''))
		mysql_query("insert into competences_ins (uid,cid,level) VALUES('{$_SESSION['uid']}','{$_REQUEST['comppros_id']}','{$_REQUEST['comppros_level']}')");
	}
}

// nombre maximum autorisé de langues
$nb_lg_max = 10;
// nombre maximum autorisé de compétences professionnelles
$nb_cpro_max = 20;
$page->assign('nb_lg_max', $nb_lg_max);
$page->assign('nb_cpro_max', $nb_cpro_max);

$res = mysql_query("SELECT ld.id, ld.langue_fr, li.level from langues_ins AS li, langues_def AS ld "
               ."where (li.lid=ld.id and li.uid='{$_SESSION['uid']}') LIMIT $nb_lg_max");

$nb_lg = mysql_num_rows($res);

for ($i = 1; $i <= $nb_lg; $i++) {
  list($langue_id[$i], $langue_name[$i], $langue_level[$i]) = mysql_fetch_row($res);
}
$page->assign('nb_lg', $nb_lg);
$page->assign_by_ref('langue_id', $langue_id);
$page->assign_by_ref('langue_name', $langue_name);
$page->assign_by_ref('langue_level', $langue_level);

$res = mysql_query("SELECT cd.id, cd.text_fr, ci.level from competences_ins AS ci, competences_def AS cd "
               ."where (ci.cid=cd.id and ci.uid='{$_SESSION['uid']}') LIMIT $nb_cpro_max");

$nb_cpro = mysql_num_rows($res);

for ($i = 1; $i <= $nb_cpro; $i++) {
  list($cpro_id[$i], $cpro_name[$i], $cpro_level[$i]) = mysql_fetch_row($res);
}
$page->assign('nb_cpro', $nb_cpro);
$page->assign_by_ref('cpro_id', $cpro_id);
$page->assign_by_ref('cpro_name', $cpro_name);
$page->assign_by_ref('cpro_level', $cpro_level);

//Definitions des tables de correspondances id => nom

$langues_levels = Array(
	1 => "1",
	2 => "2",
	3 => "3",
	4 => "4",
	5 => "5",
	6 => "6"
);
$page->assign_by_ref('langues_level',$langues_level);

$res = mysql_query("SELECT id, langue_fr FROM langues_def");
//echo mysql_error();

while(list($tmp_lid, $tmp_lg_fr) = mysql_fetch_row($res)){
	$langues_def[$tmp_lid] = $tmp_lg_fr;
}
$page->assign_by_ref('langues_def',$langues_def);

$comppros_levels = Array(
	'initié' => 'initié',
	'bonne connaissance' => 'bonne connaissance',
	'expert' => 'expert'
);
$page->assign_by_ref('comppros_level',$comppros_level);

$res = mysql_query("SELECT id, text_fr, FIND_IN_SET('titre',flags) FROM competences_def");
//echo mysql_error();

while(list($tmp_id, $tmp_text_fr, $tmp_title) = mysql_fetch_row($res)){
	$comppros_def[$tmp_id] = $tmp_text_fr;
	$comppros_title[$tmp_id] = $tmp_title;
}
$page->assign_by_ref('comppros_def',$comppros_def);
$page->assign_by_ref('comppros_title',$comppros_title);

?>
