<?php

reset($adresses);

foreach($adresses as $adrid => $adr){

  if($adr['nouvelle'] != 'new'){ // test si on vient de creer cette adresse dans verif_adresse.inc.php
  
    //construction des bits
    $visibilite = "";
    if ($adr['adr_public']) $visibilite .= 'adr_public,';
    if ($adr['adr_ax'])     $visibilite .= 'adr_ax,';
    if ($adr['tel_public']) $visibilite .= 'tel_public,';
    if ($adr['tel_ax'])     $visibilite .= 'tel_ax,';
    if (! empty($visibilite)) $visibilite = substr($visibilite, 0, -1);

    $statut = "";
    if ($adr["secondaire"])    $statut .= 'res-secondaire,';
    if ($adr["courrier"])      $statut .= 'courrier,';
    if ($adr["active"])        $statut .= 'active,';
    if ($adr["temporaire"])    $statut .= 'temporaire,';
    if (! empty($statut)) $statut = substr($statut, 0, -1);


    if ($adr["nouvelle"] == 'ajout') {
    //nouvelle adresse
      mysql_query("INSERT INTO adresses SET
			 adr1 = '".put_in_db($adr['adr1'])."',
			 adr2 = '".put_in_db($adr['adr2'])."',
			 adr3 = '".put_in_db($adr['adr3'])."',
			 cp = '".put_in_db($adr['cp'])."',
			 ville = '".put_in_db($adr['ville'])."',
			 pays = '".$adr['pays']."',
			 region = '".$adr['region']."',
			 tel = '".put_in_db($adr['tel'])."',
			 fax = '".put_in_db($adr['fax'])."',
			 visibilite = '$visibilite',
			 datemaj = NOW(),
			 statut = '$statut',
			 uid = '{$_SESSION['uid']}', adrid = '$adrid'");
    }
    
    else{ 
      //c'est une mise à jour
      mysql_query(
		    "UPDATE adresses SET
				 adr1 = '".put_in_db($adr['adr1'])."',
				 adr2 = '".put_in_db($adr['adr2'])."',
				 adr3 = '".put_in_db($adr['adr3'])."',
				 cp = '".put_in_db($adr['cp'])."',
				 ville = '".put_in_db($adr['ville'])."',
				 pays = '".$adr['pays']."',
				 region = '".$adr['region']."',
				 tel = '".put_in_db($adr['tel'])."',
				 fax = '".put_in_db($adr['fax'])."',
				 visibilite = '$visibilite',
				 datemaj = NOW(),
				 statut = '$statut'
				 WHERE uid = '".$_SESSION["uid"]."' AND adrid = '$adrid'"
		    );
    }// fin nouvelle / ancienne adresse
  }//fin if nouvellement crée
}//fin foreach
?>
