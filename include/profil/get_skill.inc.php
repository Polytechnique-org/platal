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


if(isset($_REQUEST['langue_op'])){
    if($_REQUEST['langue_op']=='retirer'){
        $globals->db->query("delete from langues_ins where uid='{$_SESSION['uid']}' and lid='{$_REQUEST['langue_id']}'");
    } elseif($_REQUEST['langue_op'] == 'ajouter'){
        if(isset($_REQUEST['langue_id']) && ($_REQUEST['langue_id'] != ''))
            $globals->db->query("insert into langues_ins (uid,lid,level) VALUES('{$_SESSION['uid']}','{$_REQUEST['langue_id']}','{$_REQUEST['langue_level']}')");
    }
}

if(isset($_REQUEST['comppros_op'])){
    if($_REQUEST['comppros_op']=='retirer'){
        $globals->db->query("delete from competences_ins where uid='{$_SESSION['uid']}' and cid='{$_REQUEST['comppros_id']}'");
    } elseif($_REQUEST['comppros_op'] == 'ajouter') {
        if(isset($_REQUEST['comppros_id']) && ($_REQUEST['comppros_id'] != ''))
	    $globals->db->query("insert into competences_ins (uid,cid,level) VALUES('{$_SESSION['uid']}','{$_REQUEST['comppros_id']}','{$_REQUEST['comppros_level']}')");
    }
}

// nombre maximum autorisé de langues
$nb_lg_max = 10;
// nombre maximum autorisé de compétences professionnelles
$nb_cpro_max = 20;

$res = $globals->db->query("SELECT ld.id, ld.langue_fr, li.level from langues_ins AS li, langues_def AS ld "
               ."where (li.lid=ld.id and li.uid='{$_SESSION['uid']}') LIMIT $nb_lg_max");

$nb_lg = mysql_num_rows($res);

for ($i = 1; $i <= $nb_lg; $i++) {
    list($langue_id[$i], $langue_name[$i], $langue_level[$i]) = mysql_fetch_row($res);
}

$res = $globals->db->query("SELECT cd.id, cd.text_fr, ci.level from competences_ins AS ci, competences_def AS cd "
               ."where (ci.cid=cd.id and ci.uid='{$_SESSION['uid']}') LIMIT $nb_cpro_max");

$nb_cpro = mysql_num_rows($res);

for ($i = 1; $i <= $nb_cpro; $i++) {
    list($cpro_id[$i], $cpro_name[$i], $cpro_level[$i]) = mysql_fetch_row($res);
}
//Definitions des tables de correspondances id => nom

$langues_levels = Array(
    1 => "1",
    2 => "2",
    3 => "3",
    4 => "4",
    5 => "5",
    6 => "6"
);

$res = $globals->db->query("SELECT id, langue_fr FROM langues_def");

while(list($tmp_lid, $tmp_lg_fr) = mysql_fetch_row($res)){
    $langues_def[$tmp_lid] = $tmp_lg_fr;
}

$comppros_levels = Array(
    'initié' => 'initié',
    'bonne connaissance' => 'bonne connaissance',
    'expert' => 'expert'
);

$res = $globals->db->query("SELECT id, text_fr, FIND_IN_SET('titre',flags) FROM competences_def");

while(list($tmp_id, $tmp_text_fr, $tmp_title) = mysql_fetch_row($res)){
    $comppros_def[$tmp_id] = $tmp_text_fr;
    $comppros_title[$tmp_id] = $tmp_title;
}

?>
