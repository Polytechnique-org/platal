<?php
    
switch (basename($_SERVER['SCRIPT_NAME'])) {
    case 'index.php':
        if (Get::get('banana') == 'updateall') {
            $globals->xdb->execute('UPDATE auth_user_quick SET banana_last={?} WHERE user_id={?}', gmdate('YmdHis'), Session::getInt('uid'));
            $_SESSION['banana_last'] = time();
        }
}

function hook_banana(&$banana) {
    global $globals;

    array_splice($banana->show_hdr,  count($banana->show_hdr)  - 2, 0, 'x-org-id');
    array_splice($banana->parse_hdr, count($banana->parse_hdr) - 2, 0, 'x-org-id');


    $serv  = "{$globals->banana->server}:{$globals->banana->port}/";
    $sname = basename($_SERVER['SCRIPT_NAME']);
    if ($sname == "spoolgen.php") {
        $banana->host = "news://{$globals->banana->web_user}:{$globals->banana->web_pass}@$serv";
    } elseif (Session::has('forlife')) {
        $banana->host = 'news://web_'.Session::get('forlife').":{$globals->banana->password}@$serv";
    }
}

function url($string)
{
    if(strpos($string, "http://")!==false)
	return $string;
    $chemins = Array('', '../', '../../');
    foreach ($chemins as $ch) {
        if (file_exists($ch.'../htdocs/')) {
            return $ch.$string;
        }
    }
    return '';
}

function hook_displayshortcuts($sname, $first = -1) {
    global $banana,$css;
    
    switch ($sname) {
        case 'subscribe.php' :
            return '[<a href="index.php">Liste des forums</a>] [<a href="'.url("confbanana.php").'">Profil</a>] ';
            break;

        case 'index.php' :
        case 'thread.php' :
        case 'article.php' :
        case 'post.php' :
            $res = '';
            if (!$banana->profile['autoup']) { 
                $res .= '[<a href="index.php?banana=updateall">Mettre à jour</a>] ';
            }
            return $res . '[<a href="'.url("confbanana.php").'">Profil</a>] ';
            break;
    }
}

function hook_formatDisplayHeader($_header,$_text) {
    global $banana;
    switch ($_header) {
        case "x-org-id":
            return "$_text".(preg_match("/[\w]+\.[\w\d]+/",$_text)?" [<a href=\"".url("fiche.php")."?user=$_text\" class='popup2'>fiche</a>]":"");

        default:
            return htmlentities($_text);
    }
}

function hook_header_translate($hdr) {
    switch ($hdr) {
        case 'x-org-id': return 'Identité';
            
        default:      
            return $hdr;
    }
}

function hook_checkcancel($_headers) {
    return ($_headers['x-org-id'] == Session::get('forlife') or has_perms());
}

function hook_getprofile() {
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
    $array['locale'] = 'fr';
    return $array;
}

$css = array(
 'bananashortcuts' => 'bananashortcuts',
 'bicol' => 'bicol',
 'bicoltitre' => 'bicoltitre',
 'bicolvpadd' => 'bicolvpadd',
 'pair' => 'pair',
 'impair' => 'impair',
 'bouton' => 'bouton',
 'error' => 'erreur',
 'normal' => 'normal',
 'total' => 'bananatotal',
 'unread' => 'bananaunread',
 'group' => 'bananagroup',
 'description' => 'bananadescription',
 'date' => 'bananadate',
 'subject' => 'bananasubject',
 'from' => 'bananafrom',
 'author' => 'author',
 'nopadd' => 'banananopadd',
 'overview' => 'bananaoverview',
 'tree' => 'bananatree'
);

function banana($params) {
    global $globals, $page;
    global $banana,$css;
    $sname = basename($_SERVER['SCRIPT_NAME']);
    require_once("../../../banana/$sname");
}

?>
