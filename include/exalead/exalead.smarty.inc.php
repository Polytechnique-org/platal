<?php

function display_group(&$group, &$exalead_data, $keywords=false,$class = 'exa_groupe', $img_path = 'images/'){
  $compteur = 0;
  $titre = ($keywords)?'Mot-clés':$group->title;
  $gid = ($keywords)?'k':$group->gid;
  if($keywords)
    $array = & $group;
  else
    $array = & $group->categories;
?>
<div class="exa_groupe">
  <div class="titre"><?php echo $titre?> :</div>
  <ul id="exa_group_<?php echo $gid?>">
<?php
  foreach($array as $categorie){
    $compteur++;
?>
    <li class="exa_categorie" id="exa_group_<?php echo $gid.'_'.$compteur;?>">
<?php
    if($categorie->is_normal()){
?>
        <a style="text-decoration: none;"
	    href="?_C=<?php echo $exalead_data->query->context.'/'.$categorie->refine_href;?>&amp;_f=xml2"
		title="Afficher seulement ces résultats"
	    ><img style="vertical-align: text-bottom;" src="images/select.png" alt="[+]" /><?php echo (empty($categorie->display)?$categorie->name:$categorie->display).(empty($categorie->count)?'':' ('.$categorie->count.')');?></a>
	<a href="?_C=<?php echo $exalead_data->query->context.'/'.$categorie->exclude_href;?>&amp;_f=xml2"
		title="Ne pas afficher ces résultats"
	       ><img style="vertical-align: text-bottom;"  src="images/moins.png" alt="[-]"/></a>
<?php
    }
    elseif($categorie->is_excluded()){
?>
        <span style="text-decoration: line-through;">
	  <a href="?_C=<?php echo $exalead_data->query->context.'/'.$categorie->reset_href;?>&amp;_f=xml2"><img style="vertical-align: text-bottom;" src="images/select.png" alt="[+]" /> <?php echo $categorie->display;?></a>
	</span>
<?php
    }
    else{
?>
        <strong><?php echo $categorie->display;?></strong>
        <a href="?_C=<?php echo $exalead_data->query->context.'/'.$categorie->reset_href;?>&amp;_f=xml2"><img style="vertical-align: text-bottom;"  src="images/moins.png" alt="[-]"/></a>
<?php
    }
    echo '</li>';
  }
?>
  </ul>
</div>
<?php
}

function _display_groupes($params, &$smarty){

  if(!empty($params['exalead_data'])){
    $exalead_data = &$GLOBALS[$params['exalead_data']];
  }
  else{
    $exalead_data = &$GLOBALS['exalead_data'];
  }

  foreach($exalead_data->groups as $group){
    display_group($group, $exalead_data);
  }

}

function _display_keywords($params, &$smarty){
  if(!empty($params['exalead_data'])){
    $exalead_data = &$GLOBALS[$params['exalead_data']];
  }
  else{
    $exalead_data = &$GLOBALS['exalead_data'];
  }

  display_group($exalead_data->keywords, $exalead_data, true);
}

function _exa_navigation_gauche($params, &$smarty){
  if(!empty($params['exalead_data'])){
    $exalead_data = &$GLOBALS[$params['exalead_data']];
  }
  else{
    $exalead_data = &$GLOBALS['exalead_data'];
  }
  $res = '';
  if($exalead_data->start > 0){
    $debut_g = $exalead_data->start - 9;
    $debut_d = $debut_g + 9;
    if($debut_g < 0){
      $res .= "<a href=\"?_C={$exalead_data->query->context}&_s=0\">[1-10]</a>";
    }
    else{
      $res .= "<a href=\"?_C={$exalead_data->query->context}&_s=".($debut_g-1)."\">[$debut_g-$debut_d]</a>";
    }
  }
  return $res;
}
function _exa_navigation_droite($params, &$smarty){
  if(!empty($params['exalead_data'])){
    $exalead_data = &$GLOBALS[$params['exalead_data']];
  }
  else{
    $exalead_data = &$GLOBALS['exalead_data'];
  }
  $max = -1;
  if(!empty($params['max'])){
    $max = (int) $params['max'];
    if(($max < 0) || ($max > $exalead_data->nhits)){
      $max = $exalead_data->nhits;
    }
  }
  else{
    $max = $exalead_data->nhits;
  }
  $res = '';
  if(($exalead_data->end < $max) && ($max > 10)){
    $fin_g = $exalead_data->end + 1;
    $fin_d = $fin_g + 10;
    if($fin_d > $max){
      $res .= "<a href=\"?_C={$exalead_data->query->context}&_s=".($max-10)."\">[".($max-10)."-".($max)."]</a>";
    }
    else{
      $res .= "<a href=\"?_C={$exalead_data->query->context}&_s=$fin_g\">[".($fin_g+1)."-$fin_d]</a>";
    }
  }
  return $res;
}

/**
* This function is used to resume database content for given group (template argument 'groupe')
*/
function _display_resume_groupe($params, &$smarty){
  if(!empty($params['exalead_data'])){
    $exalead_data = &$GLOBALS[$params['exalead_data']];
  }
  else{
    $exalead_data = &$GLOBALS['exalead_data'];
  }
  if(empty($params['groupe'])){
    return '';
  }
  $groupe = $params['groupe'];
  foreach($exalead_data->groups as $group){
    if($group->title == $groupe){
      $array = & $group->categories;
      $result = "<div class=\"exa_resume\"><div class=\"titre\">$groupe</div><ul>";
      foreach($array as $categorie){
        if($categorie->display != ''){
	  if($categorie->is_normal()){
            $result .= "<li>
	                  <div class=\"categ\">
			    <a style=\"text-decoration: none;\"
	                       href=\"?_C=".$exalead_data->query->context.'/'.$categorie->refine_href."&amp;_f=xml2\"
		               title=\"Parcourir seulement cette catégorie\"
	                    >".(empty($categorie->display)?$categorie->name:$categorie->display).(empty($categorie->count)?'':' ('.$categorie->count.')')."</a>
			    <a href=\"?_C=".$exalead_data->query->context.'/'.$categorie->exclude_href."?>&amp;_f=xml2\"
                               title=\"Ne pas afficher cette catégorie\">[-]</a>
			  </div>
		        </li>";
	  }
	  elseif($categorie->is_excluded()){
            $result .= "<li>
	                  <div class=\"categ\">
			      <a style=\"text-decoration: line-through;\"
	                         href=\"?_C=".$exalead_data->query->context.'/'.$categorie->reset_href."&amp;_f=xml2\"
		                 title=\"Afficher de nouveau cette catégorie\"
	                       >".(empty($categorie->display)?$categorie->name:$categorie->display)." [+]</a>
	                  </div>
			</li>";
	  }
	  else{
            $result .= "<li>
	                  <div class=\"categ\">
			      <strong><a style=\"text-decoration: none;\"
	                         href=\"?_C=".$exalead_data->query->context.'/'.$categorie->reset_href."&amp;_f=xml2\"
		                 title=\"Voir les autres catégories\"
	                       >".(empty($categorie->display)?$categorie->name:$categorie->display)." [-]</a></strong>
	                  </div>
			</li>";
          }
	}
      }
      $result .= "</ul></div>";
      return $result;
    }
  }
}

function register_smarty_exalead(&$page){
  $page->register_function('exa_display_groupes', '_display_groupes');
  $page->register_function('exa_display_resume_groupe', '_display_resume_groupe');
  $page->register_function('exa_display_keywords', '_display_keywords');
  $page->register_function('exa_navigation_gauche', '_exa_navigation_gauche');
  $page->register_function('exa_navigation_droite', '_exa_navigation_droite');
}

if(isset($page)){
  register_smarty_exalead($page);
}

?>
