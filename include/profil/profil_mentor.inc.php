<?php

require_once('geoloc.inc.php');
require_once('secteur.emploi.inc.php');
function affiche_pays(){
	global $mentor_pid, $mentor_pays, $nb_mentor_pays, $max_mentor_pays;
	for($i = 1; $i <= $nb_mentor_pays ; $i++){
	    if ($i%2) echo '<tr class="pair">'; else echo '<tr class="impair">';
?>
	<td class="colg">
	<span class="valeur"><?php print_html($mentor_pays[$i]);?></span>
	</td>
	<td class="colm">
	<span class="valeur">&nbsp;&nbsp;</span>
	</td>
        <td class="cold">
	  <span class="lien"><a href="javascript:mentor_pays_del('<?php echo $mentor_pid[$i]; ?>');">retirer</a></span>
        </td>
      </tr>
<?php } if($nb_mentor_pays < $max_mentor_pays) {
          if ($i%2) echo '<tr class="pair">'; else echo '<tr class="impair">';
?>
       <td class="colg">
        <select name="mentor_pays_id_new">
          <?php geoloc_pays('00');?>
        </select>
       </td>
       <td class="colm">
       </td>
       <td class="cold">
        <span class="lien"><a href="javascript:mentor_pays_add();">ajouter</a></span>
       </td>
      </tr>

<?php
	}
}

function _print_pays_smarty($params){affiche_pays();}
$page->register_function('print_pays','_print_pays_smarty');


$max_mentor_pays = 10;
$max_mentor_secteurs = 10;

$page->assign('max_mentor_pays', $max_mentor_pays);
$page->assign('max_mentor_secteurs', $max_mentor_secteurs);

//suppression eventuelle d'un pays
if(isset($_POST['mentor_pays_op']) && ($_POST['mentor_pays_op'] == 'retirer'))
{
  if(isset($_POST['mentor_pays_id']))
  {
    $id_supprimee = $_POST['mentor_pays_id'];
    mysql_query("DELETE FROM mentor_pays WHERE uid = {$_SESSION['uid']} AND pid = '$id_supprimee' LIMIT 1");
  }
}

//suppression d'un secteur / ss-secteur
if(isset($_POST['mentor_secteur_op']) && ($_POST['mentor_secteur_op'] == 'retirer'))
{
  if(isset($_POST['mentor_secteur_id']))
  {
    $id_supprimee = $_POST['mentor_secteur_id'];
    mysql_query("DELETE FROM mentor_secteurs WHERE uid = {$_SESSION['uid']} AND secteur = '$id_supprimee' LIMIT 1");
  }
}

//recuperation de l'expertise
$res = mysql_query("SELECT expertise FROM mentor WHERE uid = {$_SESSION['uid']}");

if(mysql_num_rows($res) > 0){
  list($mentor_expertise) = mysql_fetch_row($res);
}
else{
  $mentor_expertise = '';
}
$mentor_expertise_bd = $mentor_expertise;

$page->assign_by_ref('mentor_expertise', $mentor_expertise);
//recuperation des pays
$res = mysql_query("SELECT m.pid, p.pays 
                    FROM mentor_pays AS m
		    LEFT JOIN geoloc_pays AS p ON(m.pid = p.a2) WHERE m.uid = {$_SESSION['uid']} LIMIT $max_mentor_pays");

$nb_mentor_pays = mysql_num_rows($res);
if($nb_mentor_pays > 0){
  for($i = 1; $i <= $nb_mentor_pays ; $i++)
    list($mentor_pid[$i], $mentor_pays[$i]) = mysql_fetch_row($res);
}
$page->assign_by_ref('mentor_pid', $mentor_pid);
$page->assign_by_ref('mentor_pays', $mentor_pays);
$page->assign_by_ref('nb_mentor_pays', $nb_mentor_pays);

//recuperation des secteurs
$res = mysql_query("SELECT m.secteur, s.label, m.ss_secteur, ss.label
                    FROM mentor_secteurs AS m
		    LEFT JOIN emploi_secteur AS s ON(m.secteur = s.id)
		    LEFT JOIN emploi_ss_secteur AS ss ON(s.id = ss.secteur AND m.ss_secteur = ss.id)
		    WHERE m.uid = {$_SESSION['uid']}
		    LIMIT $max_mentor_pays");
$nb_mentor_secteurs = mysql_num_rows($res);
if($nb_mentor_secteurs > 0){
  for($i = 1; $i <= $nb_mentor_secteurs ; $i++)
    list($mentor_sid[$i], $mentor_secteur[$i],
         $mentor_ssid[$i], $mentor_ss_secteur[$i]) = mysql_fetch_row($res);
}
$page->assign_by_ref('mentor_sid', $mentor_sid);
$page->assign_by_ref('mentor_secteur', $mentor_secteur);
$page->assign_by_ref('mentor_ssid', $mentor_ssid);
$page->assign_by_ref('mentor_ss_secteur', $mentor_ss_secteur);
$page->assign_by_ref('nb_mentor_secteurs', $nb_mentor_secteurs);

?>
