<?php

$tabname_array = Array(
	"general"  => "Informations<br/>générales",
	"adresses" => "Adresses<br/>personnelles",
	"poly"     => "Informations<br/>polytechniciennes",
	"emploi"   => "Informations<br/>professionnelles",
	"skill"    => "Compétences<br/>diverses",
	"mentor"   => "Mentoring"
	);
	
$opened_tab = 'general';

$page->assign("onglets",$tabname_array);
$page->assign("onglet_last",'mentor');

function get_last_tab(){
	end($GLOBALS['tabname_array']);
	return key($GLOBALS['tabname_array']);
}

function get_next_tab($tabname){
	global $tabname_array;
	reset($tabname_array);
	$marker = false;
	while(list($current_tab,$current_tab_desc) = each($tabname_array)){
		if($current_tab == $tabname){
			$res = key($tabname_array);// each() sets key to the next element
			if($res != NULL)// if it was the last call of each(), key == NULL => we return the first key
				return $res;
			else{
				reset($tabname_array);
				return key($tabname_array);
			}
		}
	}
	// We should not arrive to this point, but at least, we return the first key
	reset($tabname_array);
	return key($tabname_array);
}

function draw_all_tabs(){
	global $tabname_array, $new_tab;
	reset($tabname_array);
?>
<ul id="onglet">
<?php
	while(list($current_tab,$current_tab_desc) = each($tabname_array)){
		if($current_tab == $new_tab){
			draw_tab($current_tab, true);
		}
		else{
			draw_tab($current_tab, false);
		}
	}?>
</ul>
<?php
}

function draw_tab($tab_name, $is_opened){
	global $tabname_array;
	if($is_opened){?>
           <li class="actif">
              <?php echo $tabname_array["$tab_name"];?>
           </li>
  <?php }
	else{ ?>
           <li>
	      <a href="<?php echo "{$_SERVER['PHP_SELF']}?old_tab=$tab_name";?>">
                 <?php echo $tabname_array["$tab_name"];?>
              </A>
           </li>
  <?php }
}


?>
