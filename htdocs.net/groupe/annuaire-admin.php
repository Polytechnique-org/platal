<?php

require_once 'xnet.inc.php';
require_once 'lists.inc.php';
require_once 'xnet/mail.inc.php';

new_groupadmin_page('xnet/groupe/annuaire-admin.tpl');
$client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'), $globals->asso('mail_domain'));
$lists  = $client->get_lists();
if (!$lists) $lists = array();
$listes = array_map(create_function('$arr', 'return $arr["list"];'), $lists);

$subscribers = array();

foreach ($listes as $list) {
    list(,$members) = $client->get_members($list);
    $mails = array_map(create_function('$arr', 'return $arr[1];'), $members);
    $subscribers = array_unique(array_merge($subscribers, $mails));
}

$not_in_group_x = array();
$not_in_group_ext = array();

foreach ($subscribers as $mail) {
    $res = $globals->xdb->query(
               'SELECT  COUNT(*)
                  FROM  groupex.membres AS m
             LEFT JOIN  auth_user_md5   AS u ON (m.uid=u.user_id AND m.uid<50000)
             LEFT JOIN  aliases         AS a ON (a.id=u.user_id and a.type="a_vie")
                 WHERE  asso_id = {?} AND
                        (m.email = {?} OR CONCAT(a.alias, "@polytechnique.org") = {?})',
                $globals->asso('id'), $mail, $mail);
    if ($res->fetchOneCell() == 0) {
        if (strstr($mail, '@polytechnique.org') === false) {
            $not_in_group_ext[] = $mail;
        } else {
            $not_in_group_x = $mail;
        }
    }
}

$page->assign('not_in_group_ext', $not_in_group_ext);
$page->assign('not_in_group_x', $not_in_group_x);
$page->assign('lists', $lists);
$page->run();

?>
