<?php

//ajout eventuel d'un pays
if(isset($_POST['mentor_pays_op']) && ($_POST['mentor_pays_op'] == 'ajouter') && ($nb_mentor_pays < $max_mentor_pays))
{
  if(isset($_POST['mentor_pays_id']) && ($_POST['mentor_pays_id'] != '00'))
  {
    $id_ajoutee = $_POST['mentor_pays_id'];
    mysql_query("INSERT INTO mentor_pays(uid, pid)
                 VALUES('{$_SESSION['uid']}', '$id_ajoutee')");
    if(mysql_affected_rows() == 1){
      $nb_mentor_pays++;
      $mentor_pid[$nb_mentor_pays] = $id_ajoutee;
      $mentor_pays[$nb_mentor_pays] = stripslashes($_POST['mentor_pays_name']);
    }
  }
}

//ajout d'un secteur
if(isset($_POST['mentor_secteur_op']) && ($_POST['mentor_secteur_op'] == 'ajouter') && ($nb_mentor_secteurs < $max_mentor_secteurs))
{
  if(isset($_POST['mentor_secteur_id']) && ($_POST['mentor_secteur_id'] != ''))
  {
    $sid_ajoutee = $_POST['mentor_secteur_id'];
    if(isset($_POST['mentor_ss_secteur_id']))
      $ssid_ajoutee = $_POST['mentor_ss_secteur_id'];
    mysql_query("INSERT INTO mentor_secteurs(uid, secteur, ss_secteur)
                 VALUES('{$_SESSION['uid']}', '$sid_ajoutee',
		 ".( ($ssid_ajoutee == '')?'NULL':"'$ssid_ajoutee'" ).")");
    if(mysql_affected_rows() == 1){
      $nb_mentor_secteurs++;
      $mentor_sid[$nb_mentor_secteurs] = $sid_ajoutee;
      $mentor_secteur[$nb_mentor_secteurs] = stripslashes($_POST['mentor_secteur_name']);
      $mentor_ssid[$nb_mentor_secteurs] = $ssid_ajoutee;
      $mentor_ss_secteur[$nb_mentor_secteurs] = stripslashes($_POST['mentor_ss_secteur_name']);
    }
  }
}
//au cas ou le submit du formulaire vient d'un changement du nouveau secteur
else if(isset($_POST['mentor_secteur_id_new'])){
  $mentor_secteur_id_new = $_POST['mentor_secteur_id_new'];
}
else{
  $mentor_secteur_id_new = '';
}
$page->assign_by_ref('mentor_secteur_id_new', $mentor_secteur_id_new);

if(isset($_POST['mentor_expertise'])){
  $mentor_expertise = stripslashes($_POST['mentor_expertise']);
  if(!empty($mentor_expertise)){
      if (strlen(strtok($mentor_expertise,"<>{}~§`|%$^")) < strlen($mentor_expertise)){//TODO: affiner la liste
	    $str_error = $str_error."L'expertise contient un caractère interdit.<BR />";
	}
  }
}

?>
