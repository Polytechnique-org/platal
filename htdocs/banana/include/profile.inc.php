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
    return ($_headers->xorgid == Session::get('forlife') or has_perms());
}

/** getprofile : sets profile variables
 * @return ARRAY associative array. Keys are 'name' (name), 'sig' (signature), 'org' 
 *   (organization), 'display' (display threads with new posts only or all threads),
 *   'lastnews' (timestamp for empasizing new posts)
 */

function getprofile() {
    if (logged()) {
        global $globals;
        
        $uid = Session::getInt('uid');
	$req = $globals->xdb->query(
                "SELECT  nom,mail,sig,if(FIND_IN_SET('threads',flags),'1','0'),
                         IF(FIND_IN_SET('automaj',flags),'1','0') 
                   FROM  {$globals->banana->table_prefix}profils
                  WHERE  uid={?}", $uid);
	if (!(list($nom,$mail,$sig,$disp,$maj) = $req->fetchOneRow())) {
	    $nom  = Session::get('prenom')." ".Session::get('nom');
	    $mail = Session::get('forlife')."@polytechnique.org";
	    $sig  = $nom." (".Session::getInt('promo').")";
	    $disp = 0;
	    $maj  = 1;
	}
	$array['name']      = "$nom <$mail>";
	$array['sig']       = $sig;
	$array['org']       = "Utilisateur de Polytechnique.org";
	$array['customhdr'] = "";
	$array['display']   = $disp;
	$array['autoup']    = $maj;
	$array['lastnews']  = Session::get('banana_last');
	$array['dropsig']   = true;
	if ($maj) {
            $globals->xdb->execute("UPDATE auth_user_quick SET banana_last={?} WHERE user_id={?}", gmdate("YmdHis"), $uid);
	}
	$req = $globals->xdb->query(
                "SELECT  nom
                   FROM  {$globals->banana->table_prefix}abos
              LEFT JOIN  {$globals->banana->table_prefix}list ON list.fid=abos.fid
                  WHERE  uid={?}", $uid);
	$array['subscribe'] = $req->fetchColumn();
    } else {
	$array = array();
    }
    $array['locale'] = "locales/fr.inc.php";
    return $array;
}
?>
