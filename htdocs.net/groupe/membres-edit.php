<?php

    require 'xnet.inc.php';

    function get_infos($email)
    {
        global $globals;

        $email = strtolower($email);
        if (strpos($email, '@') === false) {
            $email .= '@m4x.org';
        }
        list($mbox,$dom) = split('@', $email);

        if ($dom == 'polytechnique.org' || $dom == 'm4x.org') {
            $res = $globals->xdb->query(
                    "SELECT  user_id AS uid, u.promo, u.nom, u.prenom, b.alias,
                             CONCAT(b.alias, '@m4x.org') AS email,
                             CONCAT(b.alias, '@polytechnique.org') AS email2,
                             m.perms='admin', m.origine
                       FROM  auth_user_md5   AS u
                 INNER JOIN  aliases         AS a ON ( u.user_id = a.id AND a.type != 'homonyme' )
                 INNER JOIN  aliases         AS b ON ( u.user_id = b.id AND b.type = 'a_vie' )
                 INNER JOIN  groupex.membres AS m ON ( m.uid = u.user_id )
                      WHERE  a.alias = {?} AND u.user_id < 50000", $mbox);
            $user = $res->fetchOneAssoc();
        } else {
            $res = $globals->xdb->query(
                    "SELECT  uid, nom, prenom, email, email AS email2, perms='admin', origine
                       FROM  groupex.membres
                      WHERE  email = {?} AND asso_id = {?}", $email, $globals->asso('id'));
            $user = $res->fetchOneAssoc();
        }

        return $user;
    }

    if (Env::has('edit'))
    {
        new_groupadmin_page('xnet/groupe/membres-edit.tpl');

        $user = get_infos(Env::get('edit'));
        if (empty($user)) { header("Location: annuaire.php"); }
        $page->assign('user', $user);

        require 'lists.inc.php';
        $client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'), $globals->asso('mail_domain'));

        if (false) {
            // TODO : deal with form
        }
        
        $listes = $client->get_lists($user['email2']);
        $page->assign('listes', $listes);

        $res = $globals->xdb->query(
                'SELECT  alias, redirect IS NOT NULL as sub
                   FROM  virtual          AS v
              LEFT JOIN  virtual_redirect AS vr ON(v.vid=vr.vid AND redirect={?})
                  WHERE  alias LIKE {?} AND type="user"', $user['email'], '%@'.$globals->asso('mail_domain'));
        $page->assign('alias', $res->fetchAllAssoc());
    }
    elseif (Env::has('del'))
    {
        new_groupadmin_page('xnet/groupe/membres-del.tpl');
        $user = get_infos(Env::get('del'));
        if (empty($user)) { header("Location: annuaire.php"); }
        $page->assign('user', $user);

        if (Post::has('confirm')) {
            require 'lists.inc.php';
            $client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'), $globals->asso('mail_domain'));
            $listes = $client->get_lists($user['email2']);

            foreach ($listes as $liste) {
                if ($liste['sub'] == 2) {
                    $client->mass_unsubscribe($liste['list'], Array($user['email2']));
                    $page->trig("{$user['prenom']} {$user['nom']} a été désinscrit de {$liste['list']}");
                } elseif ($liste['sub']) {
                    $page->trig("{$user['prenom']} {$user['nom']} a une demande d'inscription en cours sur la liste {$liste['list']}@ !");
                }
            }

            $globals->xdb->execute(
                    "DELETE FROM  virtual_redirect
                           USING  virtual_redirect
                      INNER JOIN  virtual USING(vid)
                           WHERE  redirect={?} AND alias LIKE {?}", $user['email'], '%@'.$globals->asso('mail_domain'));
            if (mysql_affected_rows()) {
                $page->trig("{$user['prenom']} {$user['nom']} a été désabonné des alias du groupe !");
            }

            $globals->xdb->execute(
                    "DELETE FROM  groupex.membres WHERE uid={?} AND asso_id={?}",
                    $user['uid'], $globals->asso('id'));
            $page->trig("{$user['prenom']} {$user['nom']} a été retiré du groupe !");
        }
    }
    else
    {
        header("Location: annuaire.php");
    }

    $page->run();

?>
