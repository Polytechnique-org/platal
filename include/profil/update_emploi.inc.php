<?php
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
