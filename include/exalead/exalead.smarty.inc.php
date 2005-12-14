<?php

$exa_max_length = 16;

function display_group(&$group, &$exalead_data, $keywords=false,$class = 'exa_groupe', $img_path = 'images/'){
	$compteur = 0;
	$titre = ($keywords)?'Mot-cl�s':$group->title;
	$gid = ($keywords)?'k':$group->gid;
	if($keywords){
		$array = & $group;
	}
	else{
		$array = & $group->categories;
	}
	$res="	<div class=\"exa_groupe\"
		<div class=\"titre\">".$titre.":</div>
		<ul id=\"exa_group_".$gid."\">";
	foreach($array as $categorie){
		$compteur++;
		$res+="<li class=\"exa_categorie\" id=\"exa_group_".$gid."_".$compteur.";\">";
		if($categorie->is_normal()){
			$res+="<a style=\"text-decoration: none;\"
				href=\"?_C=".$exalead_data->query->context."&".$categorie->refine_href.";?>&amp;_f=xml2\"
				title=\"Afficher seulement ces r�sultats\">
				<img style=\"vertical-align: text-bottom;\" src=\"images/select.png\" alt=\"[+]\" />";
			if (empty($categorie->display)){
				$res+=$categorie->name;
			}
			else{
				$res+=$categorie->display;
			}
			if(!empty($categorie->count)){
				$res+=$categorie->count;
			}
			$res+="</a>
				<a href=\"?_C=".$exalead_data->query->context."&".$categorie->exclude_href.";&amp;_f=xml2\"
				title=\"Ne pas afficher ces r�sultats\">
				<img style=\"vertical-align: text-bottom;\"  src=\"images/moins.png\" alt=\"[-]\"/></a>";
		}
		elseif($categorie->is_excluded()){
			$res+="<span style=\"text-decoration: line-through;\">
				<a href=\"?_C=".$exalead_data->query->context."&".$categorie->reset_href.";&amp;_f=xml2\">
				<img style=\"vertical-align: text-bottom;\" src=\"images/select.png\" alt=\"[+]\" /> 
				".$categorie->display.";</a>
				</span>";
		}
		else{
			$res+="<strong>".$categorie->display.";</strong>
				<a href=\"?_C=".$exalead_data->query->context."&".$categorie->reset_href.";&amp;_f=xml2\">
				<img style=\"vertical-align: text-bottom;\"  src=\"images/moins.png\" alt=\"[-]\"/></a>";
		}
		$res+="</li>";		
	}
	$res+="	</ul>
		</div>";
	return $res;
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

function _exa_navigation_barre($params, &$smarty){
  if(!empty($params['exalead_data'])){
    $exalead_data = &$GLOBALS[$params['exalead_data']];
  }
  else{
    $exalead_data = &$GLOBALS['exalead_data'];
  }
  if(!empty($params['nb_res_per_page'])){
    $nb_res_per_page = (int) $params['nb_res_per_page'];
  }
  else
    $nb_res_per_page = 10;//10 results per page
  if(!empty($params['max_results']) && ((int) $params['max_results'] < $exalead_data->nhits)){
    $nb_hits = (int) $params['max_results'];
  }
  else{
    $nb_hits = (int) $exalead_data->nhits;
  }
  $res = '';
  $nb_numero = 5;//We want 5 links
  $current_page = (empty($_GET['_s'])?1:1+(int) ($_GET['_s'] / $nb_res_per_page));
  $first_number = 1;
  if($nb_hits < ($nb_numero) * $nb_res_per_page){
    $nb_numero =  (int) ($nb_hits / $nb_res_per_page);
  }
  else{
    if($current_page > ((int)$nb_numero/2))
      $first_number = 1 + $current_page - (int)($nb_numero/2);
    if($nb_hits < (($first_number + $nb_numero - 1) * $nb_res_per_page)){
       $first_number = (int) ($nb_hits / $nb_res_per_page) - $nb_numero+2;
    }
  }
  
  if ($current_page*$nb_res_per_page >$nb_res_per_page)
  	$res.="<a href=\"?_C={$exalead_data->query->context}&_s=".(($current_page-2)*$nb_res_per_page)."\">Precedent</a>  ";
  for($i = $first_number; $i <= $nb_numero + $first_number-1; $i++){
    $k=$nb_res_per_page*($i-1)+1;
    $j=$nb_res_per_page*$i;
    if($i == $current_page){
	$res .= "<strong>$k-$j</strong> ";
    }
    else{
      	$res .= "<a href=\"?_C={$exalead_data->query->context}&_s=".(($i-1)*$nb_res_per_page)."\">$k-$j</a> ";
   }
  }
  if ($current_page*10<$nb_hits)
  	$res.="<a  href=\"?_C={$exalead_data->query->context}&_s=".(($current_page)*$nb_res_per_page)."\">Suivant</a>";
  $date=false;
  foreach($exalead_data->query->query_parameters as $parameter){
	if($parameter->name=="_sf"){
		if($parameter->value=="-date")
  			$date=true;
	}
  }
  
  if($date)
	$res.=" - <a href=\"?_C={$exalead_data->query->context}&amp;_sf=relevance&amp;_f=xml2\">[Classer par pertinence]</a>";
  else
	$res.=" - <a href=\"?_C={$exalead_data->query->context}&amp;_sf=-date&amp;_f=xml2\">[Classer par date]</a>"; 
  return $res;
}


function _little_nav_barre($params, &$smarty){
	if(!empty($params['exalead_data'])){
		$exalead_data = &$GLOBALS[$params['exalead_data']];  
	}
	else{    
		$exalead_data = &$GLOBALS['exalead_data'];  
	}
	$box=false;
	if(!empty($params['where']))
		if($params['where']=='box')
			$box=true;
	$date=false;  
	foreach($exalead_data->query->query_parameters as $parameter){        
		if($parameter->name=="_sf"){                
			if($parameter->value=="-date")                        
				$date=true;        
		}  
	}
	$dizaine=10*(int)($exalead_data->start/10);	
	$res="";
	if ($box)
		$res .= "<table>";
	else
		$res.= "<table class=\"table\">";
	$res.="<tr>";
	if($box)
		$res.="<td class=\"gauche\">";
	else
		$res .=" <td width=\"20%\" style=\"text-align: left;\">";
	if($exalead_data->start != 0){
		$num=$exalead_data->start-1;
		$res .="<a href=\"?_C={$exalead_data->query->context}&_s=".$num."\">Précédent</a>";
	}
	$num=$exalead_data->start+1;
	$res .= "</td>";
	if($box){
		$res.="<td class=\"centre\">
		CV : ".$num." / {$exalead_data->nhits}<br />";
	}
	else{
		$res.="<td width=\"60%\" style=\"text-align: center;\">
		        CV : ".$num." / {$exalead_data->nhits} -";
	}
	if($date)	
		$res.="<a href=\"ec_cherche_cv.php?_C={$exalead_data->query->context}/_sf=-date&amp;_f=xml2&amp;_s=".$dizaine."\">Retour à la recherche</a>";
	else
		$res.="<a href=\"ec_cherche_cv.php?_C={$exalead_data->query->context}/_sf=-relevance&amp;_f=xml2&amp;_s=".$dizaine."\">Retour à la recherche</a>";
	$res .="</td>";
	if($box)
		$res.="<td class=\"droite\">";
	else
		$res .= "<td width=\"20%\" style=\"text-align: right;\">";
	$num=$exalead_data->start+1;
	if( $exalead_data->start+1 < $exalead_data->nhits)
		$res .= "<a href=\"?_C={$exalead_data->query->context}&_s=".$num."\">Suivant</a>";
	$res .="</td>
		</tr>
		</table>";
	return $res;
}

//categorie = true if this line is for a category, false if this is for a keyword
function _display_3_columns($title, $count, $refine, $exclude, $categorie){
if ($title!='Inconnu'){
  global $exa_max_length;
  if($categorie) $title_exclude = 'Ne pas afficher cette catégorie';
  else $title_exclude = 'Ne pas afficher ce mot-clé';
  $extract = ((strlen($title) > $exa_max_length + 3)?substr($title,0,$exa_max_length).'...':$title);
  return "<tr class=\"categ\">
	                  <td>
			    <a style=\"text-decoration: none;\"
	                       href=\"?_C=".$refine."&amp;_f=xml2\"
		               title=\"$title\"
	                    >$extract</a></td><td width=\"10%\">$count</td><td width=\"10%\">
			    <a href=\"?_C=".$exclude."&amp;_f=xml2\"
                               title=\"$title_exclude\">[-]</a></td>
		        </tr>";
}
}

//excluded = true if this line is an excluded result, = false if this line is a refined result
//categorie = true if this line is for a category, false if this is for a keyword
function _display_2_columns($title, $reset, $excluded, $categorie){
  global $exa_max_length;
  if($excluded){
    if($categorie) $title_link = 'Afficher de nouveau cette catégorie';
    else $title_link = 'Afficher de nouveau ce mot-clé';
    $link = '[+]';
    $style = 'text-decoration: line-through;';
  } else{
    if($categorie) $title_link = 'Voir les autres catégories';
    else $title_link = 'Voir les autres mots-clés';
    $link = '[-]';
    $style = 'text-decoration: none; font-weight: bold;';
  }
  $extract = ((strlen($title) > $exa_max_length + 3)?substr($title,0,$exa_max_length).'...':$title);
   return "<tr class=\"categ\">
	     <td colspan=\"2\">
	      <a style=\"$style\" href=\"?_C=".$reset."&amp;_f=xml2\"
		 title=\"$title\">$extract</a>
             </td>
	     <td width=\"10%\"><a style=\"$style\"
			          href=\"?_C=".$reset."&amp;_f=xml2\"
				  title=\"$title_link\"
	                       >$link</a>
	     </td>
            </tr>";
}

function _display_resume_groupe_category(&$group, $context, $padding = ''){
     $result = '';
      foreach($group->categories as $categorie){
        $title = (empty($categorie->display)?$categorie->name:$categorie->display);
        $count = (empty($categorie->count)?'':' ('.$categorie->count.')');
        $refine = $context.'&'.$categorie->refine_href;
	$exclude = $context.'&'.$categorie->exclude_href;
	$reset = $context.'&'.$categorie->reset_href;
	
        if($categorie->display != ''){
	  if($categorie->is_normal()){
            $result .= _display_3_columns($padding.$title, $count, $refine, $exclude, true);
	  }
	  else{
            $result .= _display_2_columns($padding.$title, $reset, $categorie->is_excluded(), true);
	  }
	}
	if(count($categorie->categories) > 0){
          $result .= _display_resume_groupe_category($categorie, $context, $padding.'-');
	}
      }
      return $result;
}

/**
* This function is used to resume database content for given group (template argument 'groupe')
*/
function _display_resume_groupe($params, &$smarty){
  global $exa_max_length;
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
  $name = $params['display'];
  foreach($exalead_data->groups as $group){
    if($group->title == $groupe){
      $result = "<table class=\"exa_resume\"><th colspan=\"3\" class=\"titre\">".gettext($name)."</th>";
      $result .= _display_resume_groupe_category($group, $exalead_data->query->context);
      $result .= "</table>";
      return $result;
    }
  }
}

/**
* This function is used to resume database content for keywords
*/
function _display_resume_keywords($params, &$smarty){
  global $exa_max_length;
  if(!empty($params['exalead_data'])){
    $exalead_data = &$GLOBALS[$params['exalead_data']];
  }
  else{
    $exalead_data = &$GLOBALS['exalead_data'];
  }
  
  //if no keywrods, do not display anything
  if(count($exalead_data->keywords) == 0) return '';
  $name=$params['display']; 
  $result = "<table class=\"exa_resume\"><th colspan=\"3\" class=\"titre\">".gettext($name)."</th>";
  foreach($exalead_data->keywords as $keyword){
     if($keyword->display != ''){
       $title = (empty($keyword->display)?$keyword->name:$keyword->display);
       $count = (empty($keyword->count)?'':' ('.$keyword->count.')');
       $refine = $exalead_data->query->context.'/'.$keyword->refine_href;
       $exclude = $exalead_data->query->context.'/'.$keyword->exclude_href;
       $reset = $exalead_data->query->context.'/'.$keyword->reset_href;
       if($keyword->is_normal()){
         $result .= _display_3_columns($title, $count, $refine, $exclude, false);
       }
       else{
         $result .= _display_2_columns($title, $reset, $keyword->is_excluded(), false);
       }
     }
  }
  $result .= "</table>";
  return $result;
}

function register_smarty_exalead(&$page){
  $page->register_function('little_nav_barre','_little_nav_barre');
  $page->register_function('exa_display_groupes', '_display_groupes');
  $page->register_function('exa_display_resume_groupe', '_display_resume_groupe');
  $page->register_function('exa_display_resume_keywords', '_display_resume_keywords');
  $page->register_function('exa_display_keywords', '_display_keywords');
  $page->register_function('exa_navigation_gauche', '_exa_navigation_gauche');
  $page->register_function('exa_navigation_droite', '_exa_navigation_droite');
  $page->register_function('exa_navigation_barre', '_exa_navigation_barre');
}

if(isset($page)){
  register_smarty_exalead($page);
}

?>
