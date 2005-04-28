<?php
    require 'xnet.inc.php';
    new_admin_page('xnet/admin.tpl');
    $page->useMenu();

    if (Get::has('del')) {
        $res = $globals->xdb->query('SELECT id, nom, mail_domain FROM groupex.asso WHERE diminutif={?}', Get::get('del'));
        list($id, $nom, $domain) = $res->fetchOneRow();
        $page->assign('nom', $nom);
        if ($id && Post::has('del')) {
            $globals->xdb->query('DELETE FROM groupex.membres WHERE asso_id={?}', $id);
            $page->trig('membres supprimés');

            if ($domain) {
                $globals->xdb->query('DELETE FROM  virtual_domains WHERE domain={?}', $domain);
                $globals->xdb->query('DELETE FROM  virtual, virtual_redirect
                                            USING  virtual INNER JOIN virtual_redirect USING (vid)
                                            WHERE  alias LIKE {?}', '%@'.$domain);
                $page->trig('suppression des alias mails');

                require_once('lists.inc.php');
                $client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'), $domain);
                if ($listes = $client->get_lists()) {
                    foreach ($listes as $l) {
                        $client->delete_list($l['list'], true);
                    }
                    $page->trig('mail lists surpprimées');
                }
            }
            
            $globals->xdb->query('DELETE FROM groupex.asso WHERE id={?}', $id);
            $page->trig("Groupe $nom supprimé");
            Get::kill('del');
        }
        if (!$id) {
            Get::kill('del');
        }
    }

    if (Post::has('diminutif')) {
        $globals->xdb->query('INSERT INTO groupex.asso (id,diminutif) VALUES(NULL,{?})', Post::get('diminutif'));
        header('Location: '.Post::get('diminutif').'/edit.php');
    }

    $res = $globals->xdb->query('SELECT nom,diminutif FROM groupex.asso ORDER by NOM');
    $page->assign('assos', $res->fetchAllAssoc());
    
    $page->run();
?>
