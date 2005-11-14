<?php

require_once 'xnet.inc.php';
require_once 'lists.inc.php';
require_once 'xnet/mail.inc.php';

if (!Env::has('liste')) {
    redirect('annuaire-admin.php');
}
$liste = Env::get('liste');

new_groupadmin_page('xnet/groupe/listes-sync.tpl');

$client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'), $globals->asso('mail_domain'));

if (Env::has('add')) {
    $client->mass_subscribe($liste, array_keys(Env::getMixed('add')));
}

list(,$members) = $client->get_members($liste);
$mails = array_map(create_function('$arr', 'return $arr[1];'), $members);
$subscribers = array_unique(array_merge($subscribers, $mails));

$not_in_group_x = array();
$not_in_group_ext = array();

$ann = $globals->xdb->iterator(
          "SELECT  IF(m.origine='X',IF(u.nom_usage<>'', u.nom_usage, u.nom) ,m.nom) AS nom,
                   IF(m.origine='X',u.prenom,m.prenom) AS prenom,
                   IF(m.origine='X',u.promo,'extérieur') AS promo,
                   IF(m.origine='X',CONCAT(a.alias, '@polytechnique.org'),m.email) AS email,
                   IF(m.origine='X',FIND_IN_SET('femme', u.flags),0) AS femme,
                   m.perms='admin' AS admin,
                   m.origine='X' AS x
             FROM  groupex.membres AS m
        LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
        LEFT JOIN  aliases         AS a ON ( a.id = m.uid AND a.type='a_vie' )
            WHERE  m.asso_id = {?}", $globals->asso('id'));

$not_in_list = array();

while ($tmp = $ann->next()) {
    if (!in_array($tmp['email'], $subscribers)) {
        $not_in_list[] = $tmp;
    }
}

$page->assign('not_in_list', $not_in_list);
$page->run();

?>
