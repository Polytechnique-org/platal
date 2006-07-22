<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

require_once dirname(__FILE__).'/lists.php';

class XnetListsModule extends ListsModule
{
    var $client;

    function handlers()
    {
        return array(
            'grp/lists'           => $this->make_hook('lists',     AUTH_MDP),
            'grp/lists/create'    => $this->make_hook('create',    AUTH_MDP),

            'grp/lists/members'   => $this->make_hook('members',   AUTH_COOKIE),
            'grp/lists/archives'  => $this->make_hook('archives',  AUTH_COOKIE),

            'grp/lists/moderate'  => $this->make_hook('moderate',  AUTH_MDP),
            'grp/lists/admin'     => $this->make_hook('admin',     AUTH_MDP),
            'grp/lists/options'   => $this->make_hook('options',   AUTH_MDP),
            'grp/lists/delete'    => $this->make_hook('delete',    AUTH_MDP),

            'grp/lists/soptions'  => $this->make_hook('soptions',  AUTH_MDP),
            'grp/lists/check'     => $this->make_hook('check',     AUTH_MDP),
            'grp/lists/sync'      => $this->make_hook('sync',      AUTH_MDP),

            'grp/alias/admin'     => $this->make_hook('aadmin',    AUTH_MDP),
            'grp/alias/create'    => $this->make_hook('acreate',   AUTH_MDP),

            /* hack: lists uses that */
            'profile' => $this->make_hook('profile', AUTH_PUBLIC),
        );
    }

    function prepare_client(&$page)
    {
        global $globals;

        require_once 'lists.inc.php';

        $this->client =& lists_xmlrpc(S::v('uid'), S::v('password'),
                                      $globals->asso('mail_domain'));

        $page->useMenu();
        $page->assign('asso', $globals->asso());
        $page->setType($globals->asso('cat'));
    }

    function handler_lists(&$page)
    {
        global $globals;

        $this->prepare_client($page);

        $page->changeTpl('xnetlists/index.tpl');

        if (Get::has('del')) {
            $this->client->unsubscribe(Get::get('del'));
            pl_redirect('lists');
        }
        if (Get::has('add')) {
            $this->client->subscribe(Get::get('add'));
            pl_redirect('lists');
        }

        if (Post::has('del_alias') && may_update()) {
            $alias = Post::get('del_alias');
            // prevent group admin from erasing aliases from other groups
            $alias = substr($alias, 0, strpos($alias, '@')).'@'.$globals->asso('mail_domain');
            XDB::query(
                    'DELETE FROM  x4dat.virtual_redirect, x4dat.virtual
                           USING  x4dat.virtual AS v
                       LEFT JOIN  x4dat.virtual_redirect USING(vid)
                           WHERE  v.alias={?}', $alias);
            $page->trig(Post::get('del_alias')." supprimé !");
        }

        $listes = $this->client->get_lists();
        $page->assign('listes',$listes);

        $alias  = XDB::iterator(
                'SELECT  alias,type
                   FROM  x4dat.virtual
                  WHERE  alias
                   LIKE  {?} AND type="user"
               ORDER BY  alias', '%@'.$globals->asso('mail_domain'));
        $page->assign('alias', $alias);

        $page->assign('may_update', may_update());
    }

    function handler_create(&$page)
    {
        global $globals;

        $this->prepare_client($page);

        $page->changeTpl('xnetlists/create.tpl');
        $page->assign('force_list_super', may_update());

        if (!Post::has('submit')) {
            return;
        }

        if (!Post::has('liste')) {
            $page->trig_run('champs «addresse souhaitée» vide');
        }

        $liste = Post::get('liste');

        if (!preg_match("/^[a-zA-Z0-9\-]*$/", $liste)) {
            $page->trig_run('le nom de la liste ne doit contenir que des lettres, chiffres et tirets');
        }

        $new = $liste.'@'.$globals->asso('mail_domain');
        $res = XDB::query('SELECT COUNT(*) FROM x4dat.virtual WHERE alias={?}', $new);
        $n   = $res->fetchOneCell();

        if($n) {
            $page->trig_run('cet alias est déjà pris');
        }
        if(!Post::get('desc')) {
            $page->trig_run('le sujet est vide');
        }

        require_once('platal/xmlrpc-client.inc.php');
        require_once('lists.inc.php');
        $ret = $this->client->create_list(
                    $liste, Post::get('desc'), Post::get('advertise'),
                    Post::get('modlevel'), Post::get('inslevel'),
                    array(S::v('forlife')), array());

        $dom = strtolower($globals->asso("mail_domain"));
        $red = $dom.'_'.$liste;

        if (!$ret) {
            $page->kill("Un problème est survenu, contacter "
                        ."<a href='mailto:support@m4x.org'>support@m4x.org</a>");
            return;
        }
        XDB::execute('INSERT INTO x4dat.virtual (alias,type)
                                VALUES({?},{?})', $liste.'@'.$dom, 'list');
        XDB::execute('INSERT INTO x4dat.virtual_redirect (vid,redirect)
                                VALUES ({?}, {?})', mysql_insert_id(),
                               "$red+post@listes.polytechnique.org");
        XDB::execute('INSERT INTO x4dat.virtual (alias,type)
                                VALUES({?},{?})', $liste.'-owner@'.$dom, 'list');
        XDB::execute('INSERT INTO x4dat.virtual_redirect (vid,redirect)
                                VALUES ({?}, {?})', mysql_insert_id(),
                               "$red+owner@listes.polytechnique.org");
        XDB::execute('INSERT INTO x4dat.virtual (alias,type)
                                VALUES({?},{?})', $liste.'-admin@'.$dom, 'list');
        XDB::execute('INSERT INTO x4dat.virtual_redirect (vid,redirect)
                                VALUES ({?}, {?})', mysql_insert_id(),
                               "$red+admin@listes.polytechnique.org");
        XDB::execute('INSERT INTO x4dat.virtual (alias,type)
                                VALUES({?},{?})', $liste.'-bounces@'.$dom, 'list');
        XDB::execute('INSERT INTO x4dat.virtual_redirect (vid,redirect)
                                VALUES ({?}, {?})', mysql_insert_id(),
                                "$red+bounces@listes.polytechnique.org");

        pl_redirect('lists/admin/'.$liste);
    }

    function handler_sync(&$page, $liste = null)
    {
        global $globals;

        $this->prepare_client($page);

        $page->changeTpl('xnetlists/sync.tpl');

        if (Env::has('add')) {
            $this->client->mass_subscribe($liste, array_keys(Env::getMixed('add')));
        }

        list(,$members) = $this->client->get_members($liste);
        $mails = array_map(create_function('$arr', 'return $arr[1];'), $members);
        $subscribers = array_unique(array_merge($subscribers, $mails));

        $not_in_group_x = array();
        $not_in_group_ext = array();

        $ann = XDB::iterator(
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
    }

    function handler_aadmin(&$page, $lfull = null)
    {
        if (is_null($lfull)) {
            return PL_NOT_FOUND;
        }

        new_groupadmin_page('xnet/groupe/alias-admin.tpl');

        if (Env::has('add_member')) {
            $add = Env::get('add_member');
            if (strstr($add, '@')) {
                list($mbox,$dom) = explode('@', strtolower($add));
            } else {
                $mbox = $add;
                $dom = 'm4x.org';
            }
            if($dom == 'polytechnique.org' || $dom == 'm4x.org') {
                $res = XDB::query(
                        "SELECT  a.alias, b.alias
                           FROM  x4dat.aliases AS a
                      LEFT JOIN  x4dat.aliases AS b ON (a.id=b.id AND b.type = 'a_vie')
                          WHERE  a.alias={?} AND a.type!='homonyme'", $mbox);
                if (list($alias, $blias) = $res->fetchOneRow()) {
                    $alias = empty($blias) ? $alias : $blias;
                    XDB::query(
                        "INSERT INTO  x4dat.virtual_redirect (vid,redirect)
                              SELECT  vid, {?}
                                FROM  x4dat.virtual
                               WHERE  alias={?}", "$alias@m4x.org", $lfull);
                   $page->trig("$alias@m4x.org ajouté");
                } else {
                    $page->trig("$mbox@polytechnique.org n'existe pas.");
                }
            } else {
                XDB::query(
                        "INSERT INTO  x4dat.virtual_redirect (vid,redirect)
                              SELECT  vid,{?}
                                FROM  x4dat.virtual
                               WHERE  alias={?}", "$mbox@$dom", $lfull);
                $page->trig("$mbox@$dom ajouté");
            }
        }

        if (Env::has('del_member')) {
            XDB::query(
                    "DELETE FROM  x4dat.virtual_redirect
                           USING  x4dat.virtual_redirect
                      INNER JOIN  x4dat.virtual USING(vid)
                           WHERE  redirect={?} AND alias={?}", Env::get('del_member'), $lfull);
            pl_redirect('alias/admin/'.$lfull);
        }

        $res = XDB::iterator(
                "SELECT  redirect
                   FROM  x4dat.virtual_redirect AS vr
             INNER JOIN  x4dat.virtual          AS v  USING(vid)
                  WHERE  v.alias={?}
               ORDER BY  redirect", $lfull);
        $page->assign('mem', $res);
    }

    function handler_acreate(&$page)
    {
        global $globals;

        new_groupadmin_page('xnet/groupe/alias-create.tpl');

        if (!Post::has('submit')) {
            return;
        }

        if (!Post::has('liste')) {
            $page->trig('champs «addresse souhaitée» vide');
            return;
        }
        $liste = Post::get('liste');
        if (!preg_match("/^[a-zA-Z0-9\-\.]*$/", $liste)) {
            $page->trig('le nom de l\'alias ne doit contenir que des lettres,'
                        .' chiffres, tirets et points');
            return;
        }

        $new = $liste.'@'.$globals->asso('mail_domain');
        $res = XDB::query('SELECT COUNT(*) FROM x4dat.virtual WHERE alias={?}', $new);
        $n   = $res->fetchOneCell();
        if($n) {
            $page->trig('cet alias est déjà pris');
            return;
        }

        XDB::query('INSERT INTO x4dat.virtual (alias,type) VALUES({?}, "user")', $new);

        pl_redirect("alias/admin/$new");
    }

    function handler_profile(&$page, $user = null)
    {
        http_redirect('https://www.polytechnique.org/profile/'.$user);
    }
}

?>
