<?php

function select_comppros_name($cproname){
	global $comppros_def, $comppros_title;
	reset($comppros_def);
	echo "<option value=\"\"".(($cproname == "")?" selected":"")."></option>";
	foreach( $comppros_def as $cid => $cname){
		if($comppros_title[$cid] == 1){
			//c'est un titre de categorie
			echo "<option value=\"$cid\"".(($cname == $cproname)?" selected":"").">$cname</option>";
		}
		else{
			echo "<option value=\"$cid\"".(($cname == $cproname)?" selected":"").">-&nbsp;$cname</option>";
		}
	}
}

function select_langue_name($lgname){
	global $langues_def;
	reset($langues_def);
	echo "<option value=\"\"".(($lgname == "")?" selected":"")."></option>";
	foreach( $langues_def as $lid => $lname){
		echo "<option value=\"$lid\"".(($lname == $lgname)?" selected":"").">$lname</option>";
	}
}

function select_langue_level($llevel){
        global $langues_levels;
        reset($langues_levels);
        echo "<option value=\"\"".(($lgname == "")?" selected":"")."></option>";
        foreach( $langues_levels as $level => $levelname){
                echo "<option value=\"$level\"".(($llevel == $level)?" selected":"").">&nbsp;$levelname&nbsp;</option>";
        }
}

function select_comppros_level(){
        global $comppros_levels;
        reset($comppros_levels);
        foreach( $comppros_levels as $level => $levelname){
                echo "<option value=\"$level\">$levelname</option>";
        }
}

function affiche_langues(){
	global $nb_lg, $nb_lg_max, $langue_name, $langue_level, $langue_id;
	for($i = 1; $i <= $nb_lg ; $i++){
	    if ($i%2) echo '<tr class="pair">'; else echo '<tr class="impair">';
?>
	<td class="colg">
	<span class="valeur"><?php echo $langue_name[$i];?></span>
	</td>
	<td class="colm">
	<span class="valeur">&nbsp;&nbsp;<?php echo ($langue_level[$i] == 0)?'-':$langue_level[$i];?></span>
	</td>
        <td class="cold">
	  <span class="lien"><a href="javascript:langue_del('<?php echo $langue_id[$i]; ?>');">retirer</a></span>
        </td>
      </tr>
<?php } if($nb_lg < $nb_lg_max) {
          if ($i%2) echo '<tr class="pair">'; else echo '<tr class="impair">';
?>
       <td class="colg">
        <select name="langue_sel_add">
          <?php select_langue_name("");?>
        </select>
       </td>
       <td class="colm">
        <select name="langue_level_sel_add">
           <?php select_langue_level(0);?>
        </select>
       </td>
       <td class="cold">
        <span class="lien"><a href="javascript:langue_add();">ajouter</a></span>
       </td>
      </tr>

<?php
	}
}

function affiche_comppros() {
	global $nb_cpro, $nb_cpro_max, $cpro_name, $cpro_level, $cpro_id;
	for($i = 1; $i <= $nb_cpro ; $i++){
	    if ($i%2) echo '<tr class="pair">'; else echo '<tr class="impair">';
?>
	<td class="colg">
	<span class="valeur"><?php echo $cpro_name[$i];?></span>
	</td>
	<td class="colm">
	<span class="valeur">&nbsp;&nbsp;<?php echo $cpro_level[$i];?></span>
	</td>
        <td class="cold">
	  <span class="lien"><a href="javascript:comppros_del('<?php echo $cpro_id[$i]; ?>');">retirer</a></span>
        </td>
      </tr>
<?php } if($nb_cpro < $nb_cpro_max) {
          if ($i%2) echo '<tr class="pair">'; else echo '<tr class="impair">';
?>
       <td class="colg">
        <select name="comppros_sel_add">
          <?php select_comppros_name("");?>
        </select>
       </td>
       <td class="colm">
        <select name="comppros_level_sel_add">
           <?php select_comppros_level();?>
        </select>
       </td>
       <td class="cold">
        <span class="lien"><a href="javascript:comppros_add();">ajouter</a></span>
       </td>
      </tr>

<?php
	}
}
function _print_langues_smarty($params){
  affiche_langues();
}
function _print_comppros_smarty($params){
  affiche_comppros();
}
$page->register_function('print_langues', '_print_langues_smarty');
$page->register_function('print_comppros', '_print_comppros_smarty');


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


$res = mysql_query("SELECT ld.id, ld.langue_fr, li.level from langues_ins AS li, langues_def AS ld "
               ."where (li.lid=ld.id and li.uid='{$_SESSION['uid']}') LIMIT $nb_lg_max");

$nb_lg = mysql_num_rows($res);

for ($i = 1; $i <= $nb_lg; $i++) {
  list($langue_id[$i], $langue_name[$i], $langue_level[$i]) = mysql_fetch_row($res);
}

$res = mysql_query("SELECT cd.id, cd.text_fr, ci.level from competences_ins AS ci, competences_def AS cd "
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

$res = mysql_query("SELECT id, langue_fr FROM langues_def");
//echo mysql_error();

while(list($tmp_lid, $tmp_lg_fr) = mysql_fetch_row($res)){
	$langues_def[$tmp_lid] = $tmp_lg_fr;
}

$comppros_levels = Array(
	'initié' => 'initié',
	'bonne connaissance' => 'bonne connaissance',
	'expert' => 'expert'
);

$res = mysql_query("SELECT id, text_fr, FIND_IN_SET('titre',flags) FROM competences_def");
//echo mysql_error();

while(list($tmp_id, $tmp_text_fr, $tmp_title) = mysql_fetch_row($res)){
	$comppros_def[$tmp_id] = $tmp_text_fr;
	$comppros_title[$tmp_id] = $tmp_title;
}

?>
