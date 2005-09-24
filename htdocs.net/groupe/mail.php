<?php
    require_once 'xnet.inc.php';
    require_once 'lists.inc.php';

    new_groupadmin_page('xnet/groupe/mail.tpl');
    $client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'), $globals->asso('mail_domain'));
    $page->assign('listes', $client->get_lists());

    if (Post::has('send')) {
        $from  = Post::get('from');
        $sujet = Post::get('sujet');
        $body  = Post::get('body');

        $mls = array_keys(Env::getMixed('ml', array()));

        require_once 'xnet/mail.inc.php';
        $tos = get_all_redirects(Post::has('membres'), $mls, $client);
        send_xnet_mails($from, $sujet, $body, $tos);
        $page->kill("Mail envoyé !");
        $page->assign('sent', true);
    }

    $page->run();
?>
