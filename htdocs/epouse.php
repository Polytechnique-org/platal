<?php
require("auto.prepend.inc.php");
require("validations.inc.php");

new_skinned_page('epouse.tpl', AUTH_MDP);

$res = mysql_query("select u.nom,u.epouse,i.flags from auth_user_md5 as u
                    left join identification as i using(matricule)
                    where user_id=".$_SESSION['uid']);
list($nom,$epouse_old,$flags) = mysql_fetch_row($res);
$flags=new flagset($flags);
$page->assign('not_femme',!$flags->hasflag("femme"));

$epouse = replace_accent(trim(clean_request('epouse'))); 
$epouse = strtoupper($epouse);
$page->assign('epouse_req',$epouse);

if (!empty($_REQUEST['submit']) && ($epouse != $epouse_old)) {
    // on vient de recevoir une requete, differente de l'ancien nom de mariage
    if ($epouse == $nom) {
        $page->assign('same',true);
    } else { // le nom de mariage est distinct du nom à l'X
        // on calcule l'alias pour l'afficher
        $myepouse = new EpouseReq($_SESSION['uid'], $_SESSION['username'], $epouse);
        list($prenom_username,) = explode('.',$_SESSION['username']);
        $alias_old=make_username($prenom_username,$epouse_old);
        $myepouse->submit();

        $page->assign('epouse_old',$epouse_old);
        $page->assign('alias_old',$alias_old);
        $page->assign('myepouse',$myepouse);
    }
}

$page->run($flags->hasflag("femme") ? '' : 'not_femme');
?>
