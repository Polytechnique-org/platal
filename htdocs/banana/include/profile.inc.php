<?php
/********************************************************************************
* install.d/profile.inc.php : class for posts
* -----------------------
*
* This file is part of the banana distribution
* Copyright: See COPYING files that comes with this distribution
********************************************************************************/

/** checkcancel : sets cancel rights
 * @param $_headers OBJECT headers of message to cancel
 * @return BOOLEAN true if user has right to cancel message
 */

function checkcancel($_headers) {
    return ($_headers->xorgid == $_SESSION['forlife'] or has_perms());
}

/** getprofile : sets profile variables
 * @return ARRAY associative array. Keys are 'name' (name), 'sig' (signature), 'org' 
 *   (organization), 'display' (display threads with new posts only or all threads),
 *   'lastnews' (timestamp for empasizing new posts)
 */

function getprofile() {
    if (logged()) {
	$req = mysql_query("SELECT  nom,mail,sig,if(FIND_IN_SET('threads',flags),'1','0'),
				    IF(FIND_IN_SET('automaj',flags),'1','0') 
			      FROM  forums.profils
			     WHERE  uid='{$_SESSION['uid']}'");
	if (!(list($nom,$mail,$sig,$disp,$maj)=mysql_fetch_row($req))) {
	    $nom = $_SESSION['prenom']." ".$_SESSION['nom'];
	    $mail = $_SESSION['forlife']."@polytechnique.org";
	    $sig = $nom." (".$_SESSION['promo'].")";
	    $disp = 0;
	    $maj = 1;
	}
	$array['name'] = "$nom <$mail>";
	$array['sig'] = $sig;
	$array['org']  = "Utilisateur de Polytechnique.org";
	$array['customhdr'] = "";
	$array['display'] = $disp;
	$array['autoup'] = $maj;
	$array['lastnews'] = $_SESSION['banana_last'];
	$array['dropsig'] = true;
	if ($maj) {
	    mysql_query("UPDATE auth_user_md5 SET banana_last='"
		.gmdate("YmdHis")."' WHERE user_id='{$_SESSION['uid']}'");
	}
	$req=mysql_query("SELECT  nom
	                    FROM  forums.abos
		       LEFT JOIN  forums.list ON list.fid=abos.fid
		           WHERE  uid={$_SESSION['uid']};");
	$array['subscribe']=array();
	while (list($fnom)=mysql_fetch_array($req)) {
	    array_push($array['subscribe'],$fnom);
	}
    } else {
	$array = array();
    }
    $array['locale'] = "locales/fr.inc.php";
    return $array;
}
?>
