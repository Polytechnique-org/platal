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
        $Id: update_emploi.inc.php,v 1.2 2004-08-31 11:16:48 x2000habouzit Exp $
 ***************************************************************************/

for($i = 0; $i < 2; $i++){
  
 $visibilite = "";
 if (! empty($_REQUEST["entreprise_public"][$i])) $visibilite .= 'entreprise_public,';
 if (! empty($_REQUEST["entreprise_ax"][$i])) $visibilite .= 'entreprise_ax,';
 if (! empty($_REQUEST["adrpro_public"][$i])) $visibilite .= 'adr_public,';
 if (! empty($_REQUEST["adrpro_ax"][$i]))     $visibilite .= 'adr_ax,';
 if (! empty($_REQUEST["telpro_public"][$i])) $visibilite .= 'tel_public,';
 if (! empty($_REQUEST["telpro_ax"][$i]))     $visibilite .= 'tel_ax,';
 if (! empty($visibilite)) $visibilite = substr($visibilite, 0, -1);

mysql_query("REPLACE INTO entreprises(uid,entrid,entreprise,secteur,ss_secteur,poste,fonction,adr1,adr2,adr3,cp,ville,pays,region,tel,fax,visibilite) ".
              "VALUES ('{$_SESSION['uid']}','$i','".put_in_db($entreprise[$i])."',".
	      ( ($secteur[$i] == "") ? "NULL ," : "'{$secteur[$i]}',") . //sinon un faux 0 est rentre dans la base
	      ( ($ss_secteur[$i] == "") ? "NULL " : "'{$ss_secteur[$i]}'") .
	      ",'".put_in_db($poste[$i])."','{$fonction[$i]}',".
              "'".put_in_db($adrpro1[$i])."','".put_in_db($adrpro2[$i])."', '".put_in_db($adrpro3[$i])."','".put_in_db($cppro[$i])."',".
              "'".put_in_db($villepro[$i])."','".put_in_db($payspro[$i])."','".put_in_db($regionpro[$i])."','".put_in_db($telpro[$i])."','".put_in_db($faxpro[$i])."', '$visibilite')");
  echo mysql_error();
}
mysql_query("UPDATE auth_user_md5 set cv='".put_in_db($cv)."' where user_id='{$_SESSION['uid']}'");
?>
