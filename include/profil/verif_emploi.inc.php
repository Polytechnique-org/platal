<?php

function set_flag_i(&$var,$var_name,$i){
  if(isset($_REQUEST[$var_name][$i])){
  	$var[$i] = 1;
  }
  else
  	$var[$i] = '0';
}

for($i = 0; $i < 2; $i++){
replace_ifset_i($entreprise,"entreprise",$i);
replace_ifset_i($poste,"poste",$i);
replace_ifset_i($secteur,"secteur",$i);
replace_ifset_i($ss_secteur,"ss_secteur",$i);
replace_ifset_i($fonction,"fonction",$i);

replace_ifset_i($adrpro1,"adrpro1",$i);
replace_ifset_i($adrpro2,"adrpro2",$i);
replace_ifset_i($adrpro3,"adrpro3",$i);
replace_ifset_i($cppro,"cppro",$i);
replace_ifset_i($villepro,"villepro",$i);
replace_ifset_i($payspro,"payspro",$i);
replace_ifset_i($regionpro,"regionpro",$i);
replace_ifset_i($telpro,"telpro",$i);
replace_ifset_i($faxpro,"faxpro",$i);

if(isset($_REQUEST['adresse_flag'])){
  set_flag_i($entreprise_public,"entreprise_public",$i);
  set_flag_i($entreprise_ax,"entreprise_ax",$i);
  set_flag_i($adrpro_public,"adrpro_public",$i);
  set_flag_i($adrpro_ax,"adrpro_ax",$i);
  set_flag_i($telpro_public,"telpro_public",$i);
  set_flag_i($telpro_ax,"telpro_ax",$i);
}
replace_ifset($cv,"cv");

// validité de l'entreprise
$j = $i+1;
if (strlen(strtok($entreprise[$i],"<>{}#~;!§*`[]|%^=")) < strlen($entreprise[$i]))
{
$str_error = $str_error."Le champ 'Entreprise $j' contient un caractère interdit.<BR />";
}

//validité du poste
if (strlen(strtok(stripslashes($poste[$i]),"<>{}~?!§*`|%$^=+")) < strlen(stripslashes($poste[$i])))
{
$str_error = $str_error."Le champ 'Poste $j' contient un caractère interdit.<BR />";
}

//validité du CV
if (strlen(strtok($cv,"<>{}~§`")) < strlen($cv))
{
  $str_error = $str_error."Le champ 'Curriculum vitae' contient un caractère interdit.<BR />";
}

if (strlen(strtok(stripslashes($adrpro1[$i]),"<>{}@~?!§*`|%$^=+")) < strlen(stripslashes($adrpro1[$i])))
    {
      $str_error = $str_error."Le champ 'Adresse professionnelle $j - Ligne 1' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok(stripslashes($adrpro2[$i]),"<>{}@~?!§*`|%$^=+")) < strlen(stripslashes($adrpro2[$i])))
    {
      $str_error = $str_error."Le champ 'Adresse professionnelle $j - Ligne 2' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok(stripslashes($adrpro3[$i]),"<>{}@~?!§*`|%$^=+")) < strlen(stripslashes($adrpro3[$i])))
    {
      $str_error = $str_error."Le champ 'Adresse professionnelle $j - Ligne 3' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok(stripslashes($cppro[$i]),"<>{}@~?!§*`|%$^=+")) < strlen(stripslashes($cppro[$i])))
    {
      $str_error = $str_error."Le champ 'Code Postal professionnel $j' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok(stripslashes($villepro[$i]),"<>{}@~?!§*`|%$^=+")) < strlen(stripslashes($villepro[$i])))
    {
      $str_error = $str_error."Le champ 'Ville professionnelle $j' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($telpro[$i],"<>{}@&#~\/:;?,!§*_`[]|%$^=")) < strlen($telpro[$i]))
    {
      $str_error = $str_error."Le champ 'Téléphone professionnel $j' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($faxpro[$i],"<>{}@&#~\/:;?,!§*_`[]|%$^=")) < strlen($faxpro[$i]))
    {
      $str_error = $str_error."Le champ 'Fax professionnel $j' contient un caractère interdit.<BR />";
    }

}
?>
