<?php

    require 'xnet.inc.php';
    new_groupadmin_page('xnet/groupe/edit.tpl');

    if (Post::has('submit')) {
        if (has_perms()) {
            if (Post::get('mail_domain') != "" && (strstr(Post::get('mail_domain'), '.') === false)) {
                $page->trig_run("le domaine doit être un FQDN (aucune modif effectuée) !!!");
            }
            $globals->xdb->execute(
                "UPDATE  groupex.asso
                    SET  nom={?}, diminutif={?}, cat={?}, dom={?}, descr={?}, site={?}, mail={?}, resp={?}, forum={?}, mail_domain={?}, ax={?}, pub={?}, sub_url={?}, inscriptible={?}
                  WHERE  id={?}",
                  Post::get('nom'), Post::get('diminutif'), Post::get('cat'), Post::getInt('dom'),
                  Post::get('descr'), Post::get('site'), Post::get('mail'), Post::get('resp'),
                  Post::get('forum'), Post::get('mail_domain'), Post::has('ax'), Post::has('pub')?'private':'public', Post::get('sub_url'), Post::get('inscriptible'),
		  $globals->asso('id'));
            if (Post::get('mail_domain')) {
                $globals->xdb->execute('INSERT INTO virtual_domains (domain) VALUES({?})', Post::get('mail_domain'));
            }
        } else {
            $globals->xdb->execute(
                "UPDATE  groupex.asso
                    SET  descr={?}, site={?}, mail={?}, resp={?}, forum={?}, ax={?}, pub= {?}, sub_url={?}
                  WHERE  id={?}",
                  Post::get('descr'), Post::get('site'), Post::get('mail'), Post::get('resp'),
                  Post::get('forum'), Post::has('ax'), Post::has('pub')?'private':'public', Post::get('sub_url'), $globals->asso('id'));
        }

        if ($_FILES['logo']['name']) {
            $logo = file_get_contents($_FILES['logo']['tmp_name']);
            $mime = $_FILES['logo']['type'];
            $globals->xdb->execute('UPDATE groupex.asso SET logo={?}, logo_mime={?} WHERE id={?}', $logo, $mime, $globals->asso('id'));
        }

        redirect('../'.Post::get('diminutif', $globals->asso('diminutif')).'/edit.php');
    }

    if (has_perms()) {
        $dom = $globals->xdb->iterator('SELECT * FROM groupex.dom ORDER BY nom');
        $page->assign('dom', $dom);
        $page->assign('super', true);
    }
    $page->run();

?>
