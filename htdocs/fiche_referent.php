<?php

require("auto.prepend.inc.php");
new_simple_page('fiche_referent.tpl',AUTH_COOKIE, false, 'add_fiche_css.tpl');

//$isnetscape = !empty($_SESSION['skin_compatible']);

if (!isset($_REQUEST['user']))
  exit;

//presuppose magic_quote à 'on'
$reqsql = "SELECT prenom, nom, user_id, promo, cv, username"
         ." FROM auth_user_md5 as u"
// conversion du username en user_id si nécessaire
         ." WHERE username = '{$_REQUEST['user']}'";
$result = mysql_query($reqsql);
if (mysql_num_rows($result)!=1)
        exit;

if (list($prenom, $nom, $user_id, $promo, $cv, $username) = mysql_fetch_row($result))
  mysql_free_result($result);

$page->assign('prenom', $prenom);
$page->assign('nom', $nom);
$page->assign('promo', $promo);
$page->assign('cv', $cv);
$page->assign('username', $username);

//recuperation des infos professionnelles
$reqsql = 
   "SELECT e.entreprise, s.label as secteur , ss.label as sous_secteur , f.fonction_fr as fonction,
           e.poste, e.adr1, e.adr2, e.adr3, e.cp, e.ville,
	   gp.pays, gr.name, e.tel, e.fax
   FROM entreprises AS e
   LEFT JOIN emploi_secteur AS s ON(e.secteur = s.id)
   LEFT JOIN emploi_ss_secteur AS ss ON(e.ss_secteur = ss.id AND e.secteur = ss.secteur)
   LEFT JOIN fonctions_def AS f ON(e.fonction = f.id)
   LEFT JOIN geoloc_pays AS gp ON (gp.a2 = e.pays)
   LEFT JOIN geoloc_region AS gr ON (gr.a2 = e.pays and gr.region = e.region)
   WHERE e.uid = $user_id
   ORDER BY e.entrid
   ";

$result = mysql_query($reqsql);

$i = 0;
while(list($adr_pro[$i]['entreprise'], $adr_pro[$i]['secteur'], $adr_pro[$i]['ss_secteur'],
           $adr_pro[$i]['fonction'], $adr_pro[$i]['poste'],
	   $adr_pro[$i]['adr1'], $adr_pro[$i]['adr2'], $adr_pro[$i]['adr3'],
	   $adr_pro[$i]['cp'], $adr_pro[$i]['ville'],
	   $adr_pro[$i]['pays'], $adr_pro[$i]['region'],
	   $adr_pro[$i]['tel'], $adr_pro[$i]['fax']) = mysql_fetch_row($result)){
    if(!empty($adr_pro[$i]['entreprise']) || !empty($adr_pro[$i]['secteur']) ||
       !empty($adr_pro[$i]['fonction']) || !empty($adr_pro[$i]['poste']) ||
       !empty($adr_pro[$i]['adr1']) || !empty($adr_pro[$i]['adr2']) || !empty($adr_pro[$i]['adr3']) ||
       !empty($adr_pro[$i]['cp']) || !empty($adr_pro[$i]['ville']) ||
       !empty($adr_pro[$i]['pays']) || !empty($adr_pro[$i]['tel']) || !empty($adr_pro[$i]['fax'])
      ){
    $i++;
   }
}
unset($adr_pro[$i]);
$nb_infos_pro = $i;
$page->assign('nb_infos_pro', $nb_infos_pro);
$page->assign_by_ref('adr_pro', $adr_pro);
mysql_free_result($result);

/////  recuperations infos referent

//expertise
$result = mysql_query("SELECT expertise FROM mentor WHERE uid = $user_id");

if(mysql_num_rows($result) > 0)
list($expertise) = mysql_fetch_row($result);
mysql_free_result($result);

$page->assign('expertise', $expertise);

//secteurs
$result = mysql_query("SELECT s.label, ss.label
                       FROM mentor_secteurs AS m
		       LEFT JOIN emploi_secteur AS s ON(m.secteur = s.id)
		       LEFT JOIN emploi_ss_secteur AS ss ON(m.secteur = ss.secteur AND m.ss_secteur = ss.id)
                       WHERE uid = $user_id");
$nb_secteurs = mysql_num_rows($result);
$i = 1;
while(list($secteurs[$i], $ss_secteurs[$i]) = mysql_fetch_row($result))
  $i++;
unset($secteurs[$i]);
mysql_free_result($result);
$page->assign('nb_secteurs', $nb_secteurs);
$page->assign_by_ref('secteurs', $secteurs);
$page->assign_by_ref('ss_secteurs', $ss_secteurs);

//pays
$result = mysql_query("SELECT gp.pays
                       FROM mentor_pays AS m
		       LEFT JOIN geoloc_pays AS gp ON(m.pid = gp.a2)
                       WHERE uid = $user_id");
$nb_pays = mysql_num_rows($result);
$i = 1;
while(list($pays[$i]) = mysql_fetch_row($result)){
  $i++;
}
unset($pays[$i]);
mysql_free_result($result);
$page->assign('nb_pays', $nb_pays);
$page->assign_by_ref('pays', $pays);

$page->run();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php  if (empty($_SESSION['skin_compatible'])) { ?>
    <link href="Sk/Base/x.css" rel="StyleSheet" type="text/css" media="screen">
<?php  } ?>
    <link href="Sk/<?php echo $_SESSION['name'];?>/x.css" rel="StyleSheet" type="text/css">
    <title><?php echo "$prenom $nom"; ?> - Fiche référent</title>
  </head>
  <body>

<script language="JavaScript" type="text/javascript">
  <!--
  function popWin(theURL) {
    if (theURL.indexOf('?')==-1)
      a = '?';
    else
      a = '&';
    theURL += <?php echo (isset($_COOKIE[session_name()]) ? "\"\"" : "a + \"".SID."\""); ?>;                        
    self.close();
    window.focus("main");
    open(theURL,"main","");
  }
  function x() { return; }
  // -->
</script>
<div class="nom"><?php echo "$prenom $nom"; ?></div><br /><br />
<span><?php echo "X$promo";?>&nbsp;-&nbsp;
<a href="mailto:<?php echo $username?>@polytechnique.org"><?php echo $username; ?>@polytechnique.org</a><br />
</span>
<br />
<?php
//a t il bien des infos de referents :
if((isset($expertise) && !empty($expertise)) ||  ($nb_secteurs > 0)  || ($nb_pays > 0) ){
?>
<div class="detail">
        <div class="contenu">
                <div class="element">
                <div class="sselement">
                        <span class="soustitre">Informations de référent</span><br />
                        <hr align=left>
			<?php
$colonnes = 0;
if($nb_secteurs > 0)
  $colonnes ++;
if($nb_pays > 0)
  $colonnes ++;


    if(isset($expertise) && !empty($expertise)){
?>
                        <span class="titre">Expertise :<br /></span>
                        <span class="item"><?php echo nl2br(htmlentities($expertise)); ?><br /></span>
<?php
   }
   if($colonnes == 2){
?>
                        <table width="100%">
                        <tr><td width="50%" valign="TOP">
<?php
   }
   if($nb_secteurs > 0){
?>
                               <span class="titre">Secteurs :<br /></span>
<?php
     for($i = 1; $i <= $nb_secteurs ; $i++){
?>
                               <span class="item">
			       <?php echo $secteurs[$i].((!empty($ss_secteurs[$i]))?("( ".$ss_secteurs[$i]." )"):"")?><br />
			       </span>
<?php
     }
   }
   if($colonnes == 2){
?>
                        </td>
			<td width="50%" valign="TOP">
<?php
   }
   if($nb_pays > 0){
?>
                               <span class="titre">Pays :<br /></span>
<?php
     for($i = 1; $i <= $nb_pays ; $i++){
?>
                               <span class="item">
			       <?php echo $pays[$i];?>
			       <br/></span>
<?php
     }
   }
   if($colonnes == 2){
?>
                        </td></tr>
			</table>
<?php
   }
?>
</div></div></div></div>
<?php
}
?>
<div class="detail">
        <div class="contenu">

         <div class="element">
                 <div class="sselement"><?php
                 if((isset($entreprise)) || (!empty($cv))) { ?>
                        <span class="soustitre">Infos professionnelles</span><br />
                        <hr align=left><?php
                }
		if(isset($entreprise[0])){?>
		    <table width="100%">
		      <tr>
	  <?php
		for($i = 0; $i < 2; $i++){
                   if(isset($entreprise[$i])) {
                       ?>
		       <td width="<?php echo $taille_adr_pro?>%" valign="TOP">
		       <span class="titre">Entreprise/Organisme :</span>
                         <span class="item">
                        <?php 
                        echo htmlspecialchars($entreprise[$i])."<br/>";?>
			</span>
			<?php
			if(!empty($secteur[$i])){?>
			   <span class="titre">Secteur :</span>
			   <span class="item">
			   <?php 
			      echo htmlspecialchars($secteur[$i]).((!empty($ss_secteur[$i]))?" ( {$ss_secteur[$i]} )":"")."<br />";
			      ?>
			   </span>
			<?php
			}
			if(!empty($adr_pro_complete[$i])){?>
			<span class="titre">Adresse :</span><br />
			<span class="item">
			<?php
                        echo "\n$adr_pro_complete[$i]"; 
                        }
			else{
                          echo "<span class=\"item\">";
			}
			if (!empty($pays_pro[$i])) 
                        {
                                echo "<br />".(!empty($region_pro[$i])?" [$region_pro[$i], ":"[").$pays_pro[$i]."]";
                        }
                        ?></span><?php

                                if (!empty($fonction[$i])) { ?><br>
                                        <span class="titre">Fonction :</span>
                                        <span class="item"><?php echo htmlspecialchars($fonction[$i]); ?></span><?php
                                      } 
                                if (!empty($poste[$i])) { ?><br>
                                        <span class="titre">Poste :</span>
                                        <span class="item"><?php echo htmlspecialchars($poste[$i]); ?></span><?php
                                      } 
                                if (!empty($tel_pro[$i])) { ?><br>
                                        <span class="titre">Tél :</span>
                                        <span class="item"><?php echo $tel_pro[$i]; ?></span><?php
                                      }
                                if (!empty($fax_pro[$i])) { ?><br>
                                        <span class="titre">Fax :</span>
                                        <span class="item"><?php echo $fax_pro[$i]; ?></span><?php
                                      }
                              ?>
		        </td>	      
               <?php }
		}?>
		</tr></table>
		<?php
		}
                ?>
                </div>
        </div>
        </div>
</div>
    <?php
      if($cv)
        {
        ?>
      <div class="detail">
      <div class="contenu">
      <div class="element">
      <div class="sselement">
               <span class="titre">CV</span><br />
               <p class="cv"><?php echo $cv; ?></p>
      </div>
      </div>
      </div>
      </div>
<?php } ?>
  </body>
</html>
