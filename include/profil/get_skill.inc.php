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


if(Env::has('langue_op')){
    if(Env::v('langue_op', '')=='retirer'){
        XDB::execute("DELETE FROM langues_ins WHERE uid = {?} AND lid = {?}", S::v('uid', -1), Env::v('langue_id', ''));
    } elseif(Env::v('langue_op', '') == 'ajouter'){
        if(Env::v('langue_id', '') != '')
            XDB::execute("INSERT INTO langues_ins (uid,lid,level) VALUES ({?}, {?}, {?})", S::v('uid', -1), Env::v('langue_id', ''), Env::v('langue_level', ''));
    }
}

if(Env::has('comppros_op')){
    if(Env::v('comppros_op', '')=='retirer'){
        XDB::execute("DELETE FROM competences_ins WHERE uid = {?} AND cid = {?}", S::v('uid', -1), Env::v('comppros_id', ''));
    } elseif(Env::v('comppros_op', '') == 'ajouter') {
        if(Env::v('comppros_id', '') != '')
	    XDB::execute("INSERT INTO competences_ins (uid,cid,level) VALUES({?}, {?}, {?})", S::v('uid', -1), Env::v('comppros_id', ''), Env::v('comppros_level', ''));
    }
}

// nombre maximum autorisé de langues
$nb_lg_max = 10;
// nombre maximum autorisé de compétences professionnelles
$nb_cpro_max = 20;

$res = XDB::iterRow("SELECT ld.id, ld.langue_fr, li.level FROM langues_ins AS li, langues_def AS ld "
               ."WHERE (li.lid=ld.id AND li.uid= {?}) LIMIT $nb_lg_max", S::v('uid', -1));

$nb_lg = $res->total();

for ($i = 1; $i <= $nb_lg; $i++) {
    list($langue_id[$i], $langue_name[$i], $langue_level[$i]) = $res->next();
}

$res = XDB::iterRow("SELECT cd.id, cd.text_fr, ci.level FROM competences_ins AS ci, competences_def AS cd "
               ."WHERE (ci.cid=cd.id AND ci.uid={?}) LIMIT $nb_cpro_max", S::v('uid', -1));

$nb_cpro = $res->total();

for ($i = 1; $i <= $nb_cpro; $i++) {
    list($cpro_id[$i], $cpro_name[$i], $cpro_level[$i]) = $res->next();
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

$res = XDB::iterRow("SELECT id, langue_fr FROM langues_def");

while(list($tmp_lid, $tmp_lg_fr) = $res->next()){
    $langues_def[$tmp_lid] = $tmp_lg_fr;
}

$comppros_levels = Array(
    'initié' => 'initié',
    'bonne connaissance' => 'bonne connaissance',
    'expert' => 'expert'
);

$res = XDB::iterRow("SELECT id, text_fr, FIND_IN_SET('titre',flags) FROM competences_def");

while(list($tmp_id, $tmp_text_fr, $tmp_title) = $res->next()){
    $comppros_def[$tmp_id] = $tmp_text_fr;
    $comppros_title[$tmp_id] = $tmp_title;
}

?>
