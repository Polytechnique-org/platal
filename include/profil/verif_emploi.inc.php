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


function set_flag_i(&$var,$var_name,$i){
  $tab = Env::getMixed($var_name, Array());
  if(isset($tab[$i])){
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
replace_ifset_i($mobilepro,"mobilepro",$i);
replace_ifset_i($pubpro,"pubpro",$i);
replace_ifset_i($adr_pubpro,"adr_pubpro",$i);
replace_ifset_i($tel_pubpro,"tel_pubpro",$i);
replace_ifset_i($emailpro,"emailpro",$i);
replace_ifset_i($email_pubpro,"email_pubpro",$i);
replace_ifset($cv,"cv");

// validité de l'entreprise
$j = $i+1;
if (strlen(strtok($entreprise[$i],"<>{}#~;!§*`[]|%^=")) < strlen($entreprise[$i]))
{
$str_error = $str_error."Le champ 'Entreprise $j' contient un caractère interdit.<BR />";
}

//validité du poste
if (strlen(strtok($poste[$i],"<>{}~?!§*`|%$^=+")) < strlen($poste[$i]))
{
$str_error = $str_error."Le champ 'Poste $j' contient un caractère interdit.<BR />";
}

//validité du CV
if (strlen(strtok($cv,"<>{}~§`")) < strlen($cv))
{
  $str_error = $str_error."Le champ 'Curriculum vitae' contient un caractère interdit.<BR />";
}

if (strlen(strtok($adrpro1[$i],"<>{}@~?!§*`|%$^=+")) < strlen($adrpro1[$i]))
    {
      $str_error = $str_error."Le champ 'Adresse professionnelle $j - Ligne 1' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($adrpro2[$i],"<>{}@~?!§*`|%$^=+")) < strlen($adrpro2[$i]))
    {
      $str_error = $str_error."Le champ 'Adresse professionnelle $j - Ligne 2' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($adrpro3[$i],"<>{}@~?!§*`|%$^=+")) < strlen($adrpro3[$i]))
    {
      $str_error = $str_error."Le champ 'Adresse professionnelle $j - Ligne 3' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($cppro[$i],"<>{}@~?!§*`|%$^=+")) < strlen($cppro[$i]))
    {
      $str_error = $str_error."Le champ 'Code Postal professionnel $j' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($villepro[$i],"<>{}@~?!§*`|%$^=+")) < strlen($villepro[$i]))
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
  if (strlen(strtok($mobilepro[$i],"<>{}@&#~\/:;?,!§*_`[]|%$^=")) < strlen($mobilepro[$i]))
    {
      $str_error = $str_error."Le champ 'Mobile professionnel $j' contient un caractère interdit.<BR />";
    }

}
?>
