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
                onMouseOver="javascript:select_categorie(this, 'exa_group_<?php echo $gid?>')"
		onMouseOut="javascript:unselect_categorie(this, 'exa_group_<?php echo $gid?>')"
		title="Afficher seulement ces résultats"
	    ><img style="vertical-align: text-bottom;" src="images/select.png" alt="[+]" /><?php echo $categorie->display;?> (<?php echo $categorie->count;?>)</a>
	<a href="?_C=<?php echo $exalead_data->query->context.'/'.$categorie->exclude_href;?>&amp;_f=xml2"
                onMouseOver="javascript:exclude_categorie('exa_group_<?php echo $gid.'_'.$compteur;?>')"
		onMouseOut="javascript:unexclude_categorie('exa_group_<?php echo $gid.'_'.$compteur;?>')" 
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

function register_smarty_exalead(&$page){
  $page->register_function('exa_display_groupes', '_display_groupes');
  $page->register_function('exa_display_keywords', '_display_keywords');
}

if(isset($page)){
  register_smarty_exalead($page);
}

?>
