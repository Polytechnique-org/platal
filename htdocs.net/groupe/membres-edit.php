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
                    "SELECT  user_id AS uid, u.promo, IF(u.nom_usage<>'', u.nom_usage, u.nom) as nom, u.prenom, b.alias,
                             CONCAT(b.alias, '@m4x.org') AS email,
                             CONCAT(b.alias, '@polytechnique.org') AS email2,
                             m.perms='admin' AS perms, m.origine
                       FROM  auth_user_md5   AS u
                 INNER JOIN  aliases         AS a ON ( u.user_id = a.id AND a.type != 'homonyme' )
                 INNER JOIN  aliases         AS b ON ( u.user_id = b.id AND b.type = 'a_vie' )
                 INNER JOIN  groupex.membres AS m ON ( m.uid = u.user_id AND asso_id={?})
                      WHERE  a.alias = {?} AND u.user_id < 50000", $globals->asso('id'), $mbox);
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

    if (Env::has('new'))
    {
        new_groupadmin_page('xnet/groupe/membres-add.tpl');
        $x = (Env::get('new') == 'x');

        if (Post::has('email')) {
            if ($x) {
                require_once 'user.func.inc.php';
                if ($forlife = get_user_forlife(Post::get('email'))) {
                    $globals->xdb->execute(
                                'INSERT INTO  groupex.membres (uid,asso_id,origine)
                                      SELECT  user_id,{?},"X"
                                        FROM  auth_user_md5 AS u
                                  INNER JOIN  aliases       AS a ON (u.user_id = a.id)
                                       WHERE  a.alias={?}', $globals->asso('id'), $forlife);
                    header('Location: ?edit='.$forlife);
                }
            } else {
                $email = Post::get('email');
                if (isvalid_email($email)) {
                    $res = $globals->xdb->query('SELECT MAX(uid)+1 FROM groupex.membres');
                    $uid = max(intval($res->fetchOneCell()), 50001);
                    $globals->xdb->execute('INSERT INTO  groupex.membres (uid,asso_id,origine,email) VALUES({?},{?},"ext",{?})',
                            $uid, $globals->asso('id'), $email);
                    header('Location: ?edit='.$email);
                } else {
                    $page->trig("« <strong>$email</strong> » n'est pas une adresse mail valide");
                }
            }
        }
    }
    elseif (Env::has('edit'))
    {
        new_groupadmin_page('xnet/groupe/membres-edit.tpl');

        $user = get_infos(Env::get('edit'));
        if (empty($user)) { header("Location: annuaire.php"); }

        require 'lists.inc.php';
        $client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'), $globals->asso('mail_domain'));

        if (Post::has('change')) {

            if ($user['origine'] != 'X')
            {
                $globals->xdb->query('UPDATE groupex.membres SET prenom={?}, nom={?}, email={?} WHERE uid={?} AND asso_id={?}',
                        Post::get('prenom'), Post::get('nom'), Post::get('email'), $user['uid'], $globals->asso('id'));
                $user['nom']    = Post::get('nom');
                $user['prenom'] = Post::get('prenom');
                $user['email']  = Post::get('email');
                $user['email2'] = Post::get('email');
            }

            $perms = Post::getInt('is_admin');
            if ($user['perms'] != $perms) {
                $globals->xdb->query('UPDATE groupex.membres SET perms={?} WHERE uid={?} AND asso_id={?}',
                    $perms ? 'admin' : 'membre', $user['uid'], $globals->asso('id'));
                $user['perms'] = $perms;
                $page->trig('permissions modifiées');
            }

            foreach (Env::getMixed('ml1',array()) as $ml => $state) {
                $ask = empty($_REQUEST['ml2'][$ml]) ? 0 : 2;
                if ($ask == $state) continue;
                if ($state == '1') {
                    $page->trig("{$user['prenom']} {$user['nom']} a actuellement une demande d'inscription en cours sur <strong>$ml@</strong> !!!");
                } elseif ($ask) {
                    $client->mass_subscribe($ml, Array($user['email2']));
                    $page->trig("{$user['prenom']} {$user['nom']} a été abonné à $ml@");
                } else {
                    $client->mass_unsubscribe($ml, Array($user['email2']));
                    $page->trig("{$user['prenom']} {$user['nom']} a été désabonné de $ml@");
                }
            }

            foreach (Env::getMixed('ml3', array()) as $ml => $state) {
                $ask = !empty($_REQUEST['ml4'][$ml]);
                if($state == $ask) continue;
                if($ask) {
                    $globals->xdb->query("INSERT INTO  virtual_redirect (vid,redirect)
                                               SELECT  vid,{?} FROM virtual WHERE alias={?}",
                                         $user['email'], $ml);
                    $page->trig("{$user['prenom']} {$user['nom']} a été abonné à $ml");
                } else {
                    $globals->xdb->query("DELETE FROM  virtual_redirect
                                                USING  virtual_redirect
                                           INNER JOIN  virtual USING(vid)
                                                WHERE  redirect={?} AND alias={?}", $user['email'], $ml);
                    $page->trig("{$user['prenom']} {$user['nom']} a été désabonné de $ml");
                }
            }
        }
        
        $page->assign('user', $user);
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
            if ($domain = $globals->asso('mail_domain')) {
            
                require 'lists.inc.php';
                $client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'), $domain);
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
                               WHERE  redirect={?} AND alias LIKE {?}", $user['email'], '%@'.$domain);
                if (mysql_affected_rows()) {
                    $page->trig("{$user['prenom']} {$user['nom']} a été désabonné des alias du groupe !");
                }
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
