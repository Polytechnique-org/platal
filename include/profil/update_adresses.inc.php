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
 ***************************************************************************
        $Id: update_adresses.inc.php,v 1.3 2004/08/31 13:59:43 x2000habouzit Exp $
 ***************************************************************************/


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
      $globals->db->query("INSERT INTO adresses SET
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
      $globals->db->query(
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
