<?php
require("auto.prepend.inc.php");
new_skinned_page('stats/coupure.tpl',AUTH_PUBLIC);

function serv_to_str($params) {
    $flags = explode(',',$params);
    $trad = Array('web' => 'site web', 'mail'=> 'redirection mail',
                  'smtp' => 'serveur sécurisé d\'envoi de mails',
                  'nntp' => 'serveur des forums de discussion');
    $ret = Array();
    foreach($flags as $flag)
        $ret[] = $trad[$flag];
    return implode(', ',$ret);
}
if (isset($_REQUEST['cp_id'])) 
    $res=mysql_query("select UNIX_TIMESTAMP(debut) AS debut, TIME_FORMAT(duree,'%kh%i') AS duree, resume, description, services from coupures where id='{$_REQUEST['cp_id']}'");
else
    $res="";

if(($res)&&($cp = mysql_fetch_assoc($res))) {
    $cp['lg_services'] = serv_to_str($cp['services']);
    $page->assign_by_ref('cp',$cp);
} else {
    $beginning_date = date("Ymd", time() - 3600*24*21) . "000000";
    $sql = "select id, UNIX_TIMESTAMP(debut) as debut, resume, services from coupures where debut > '" . $beginning_date
        .  "' order by debut desc";
    $page->mysql_assign($sql, 'coupures');
}

$page->run();
?>
