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

class ProfileModule extends PLModule
{
    function handlers()
    {
        return array(
            'photo'        => $this->make_hook('photo',        AUTH_PUBLIC),
            'photo/change' => $this->make_hook('photo_change', AUTH_MDP),

            'fiche.php'        => $this->make_hook('fiche',      AUTH_PUBLIC),
            'profile'          => $this->make_hook('profile',    AUTH_PUBLIC),
            'profile/private'  => $this->make_hook('profile',    AUTH_COOKIE),
            'profile/edit'     => $this->make_hook('p_edit',     AUTH_MDP),
            'profile/orange'   => $this->make_hook('p_orange',   AUTH_MDP),
            'profile/usage'    => $this->make_hook('p_usage',    AUTH_MDP),

            'referent'         => $this->make_hook('referent',   AUTH_COOKIE),
            'referent/search'  => $this->make_hook('ref_search', AUTH_COOKIE),

            'trombi'  => $this->make_hook('trombi', AUTH_COOKIE),
            'groupes-x'        => $this->make_hook('xnet',      AUTH_COOKIE),

            'vcard'   => $this->make_hook('vcard',  AUTH_COOKIE),
            'admin/binets'     => $this->make_hook('admin_binets', AUTH_MDP, 'admin'),
            'admin/medals'     => $this->make_hook('admin_medals', AUTH_MDP, 'admin'),
            'admin/formations' => $this->make_hook('admin_formations', AUTH_MDP, 'admin'),
            'admin/groupes-x'  => $this->make_hook('admin_groupesx', AUTH_MDP, 'admin'),
            'admin/trombino'   => $this->make_hook('admin_trombino', AUTH_MDP, 'admin'),

        );
    }

    /* XXX COMPAT */
    function handler_fiche(&$page)
    {
        return $this->handler_profile($page, Env::v('user'));
    }


    function _trombi_getlist($offset, $limit)
    {
        $where  = ( $this->promo > 0 ? "WHERE promo='{$this->promo}'" : "" );

        $res = XDB::query(
                "SELECT  COUNT(*)
                   FROM  auth_user_md5 AS u
             RIGHT JOIN  photo         AS p ON u.user_id=p.uid
             $where");
        $pnb = $res->fetchOneCell();

        $res = XDB::query(
                "SELECT  promo, user_id, a.alias AS forlife,
                         IF (nom_usage='', nom, nom_usage) AS nom, prenom
                   FROM  photo         AS p
             INNER JOIN  auth_user_md5 AS u ON u.user_id=p.uid
             INNER JOIN  aliases       AS a ON ( u.user_id=a.id AND a.type='a_vie' )
                  $where
               ORDER BY  promo, nom, prenom LIMIT {?}, {?}", $offset*$limit, $limit);

        return array($pnb, $res->fetchAllAssoc());
    }

    function handler_photo(&$page, $x = null, $req = null)
    {
        if (is_null($x)) {
            return PL_NOT_FOUND;
        }

        $res = XDB::query("SELECT id, pub FROM aliases
                                  LEFT JOIN photo ON(id = uid)
                                      WHERE alias = {?}", $x);
        list($uid, $photo_pub) = $res->fetchOneRow();

        if ($req && S::logged()) {
            include 'validations.inc.php';
            $myphoto = PhotoReq::get_request($uid);
            Header('Content-type: image/'.$myphoto->mimetype);
            echo $myphoto->data;
        } else {
            $res = XDB::query(
                    "SELECT  attachmime, attach
                       FROM  photo
                      WHERE  uid={?}", $uid);

            if ((list($type, $data) = $res->fetchOneRow())
            &&  ($photo_pub == 'public' || S::logged())) {
                Header("Content-type: image/$type");
                echo $data;
            } else {
                Header('Content-type: image/png');
                echo file_get_contents(dirname(__FILE__).'/../htdocs/images/none.png');
            }
        }
        exit;
    }

    function handler_photo_change(&$page)
    {
        $page->changeTpl('profile/trombino.tpl');

        require_once('validations.inc.php');

        $trombi_x = '/home/web/trombino/photos'.S::v('promo')
                    .'/'.S::v('forlife').'.jpg';

        if (Env::has('upload')) {
            $file = isset($_FILES['userfile']['tmp_name'])
                    ? $_FILES['userfile']['tmp_name']
                    : Env::v('photo');
            if ($data = file_get_contents($file)) {
                if ($myphoto = new PhotoReq(S::v('uid'), $data)) {
                    $myphoto->submit();
                }
            } else {
                $page->trig('Fichier inexistant ou vide');
            }
        } elseif (Env::has('trombi')) {
            $myphoto = new PhotoReq(S::v('uid'),
                                    file_get_contents($trombi_x));
            if ($myphoto) {
                $myphoto->commit();
                $myphoto->clean();
            }
        } elseif (Env::v('suppr')) {
            XDB::execute('DELETE FROM photo WHERE uid = {?}',
                                   S::v('uid'));
            XDB::execute('DELETE FROM requests
                                     WHERE user_id = {?} AND type="photo"',
                                   S::v('uid'));
        } elseif (Env::v('cancel')) {
            $sql = XDB::query('DELETE FROM requests 
                                        WHERE user_id={?} AND type="photo"',
                                        S::v('uid'));
        }

        $sql = XDB::query('SELECT COUNT(*) FROM requests
                                      WHERE user_id={?} AND type="photo"',
                                    S::v('uid'));
        $page->assign('submited', $sql->fetchOneCell());
        $page->assign('has_trombi_x', file_exists($trombi_x));
    }

    function handler_profile(&$page, $x = null)
    {
        if (is_null($x)) {
            return PL_NOT_FOUND;
        }

        global $globals;
        require_once 'user.func.inc.php';

        $page->changeTpl('profile/fiche.tpl', SIMPLE);

        $view = 'private';
        if (!S::logged() || Env::v('view') == 'public') $view = 'public';
        if (S::logged() && Env::v('view') == 'ax')      $view = 'ax';

        if (is_numeric($x)) {
            $res = XDB::query(
                    "SELECT  alias 
                       FROM  aliases       AS a
                 INNER JOIN  auth_user_md5 AS u ON (a.id=u.user_id AND a.type='a_vie')
                      WHERE  matricule={?}", $x);
            $login = $res->fetchOneCell();
        } else {
            $login = get_user_forlife($x);
        }

        if (empty($login)) {
            if (preg_match('/([-a-z]+)\.([-a-z]+)\.([0-9]{4})/i', $x, $matches)) {
                $matches = str_replace('-', '_', $matches);
                $res = XDB::query("SELECT user_id
                                     FROM auth_user_md5
                                    WHERE prenom LIKE {?} AND nom LIKE {?} AND promo = {?}
                                          AND perms = 'pending'",
                                  $matches[1], $matches[2], $matches[3]);
                if ($res->numRows() == 1) {
                    $uid = $res->fetchOneCell();
                    pl_redirect('marketing/public/' . $uid);
                }
            }
            return PL_NOT_FOUND;
        }

        $new   = Env::v('modif') == 'new';
        $user  = get_user_details($login, S::v('uid'), $view);
        require_once('url_catcher.inc.php');
        $user['freetext'] = url_catcher($user['freetext'], false);
        $title = $user['prenom'] . ' ' . empty($user['nom_usage']) ? $user['nom'] : $user['nom_usage'];
        $page->assign('xorg_title', $title);

        // photo

        $photo = 'photo/'.$user['forlife'].($new ? '/req' : '');

        if (!isset($user['y']) and !isset($user['x'])) {
            list($user['x'], $user['y']) = getimagesize("images/none.png");
        }
        if (!isset($user['y']) or $user['y'] < 1) $user['y']=1;
        if (!isset($user['x']) or $user['x'] < 1) $user['x']=1;
        if ($user['x'] > 240) {
            $user['y'] = (integer)($user['y']*240/$user['x']);
            $user['x'] = 240;
        }
        if ($user['y'] > 300) {
            $user['x'] = (integer)($user['x']*300/$user['y']);
            $user['y'] = 300;
        }
        if ($user['x'] < 160) {
            $user['y'] = (integer)($user['y']*160/$user['x']);
            $user['x'] = 160;
        }

        $page->assign('logged', has_user_right('private', $view));
        if (!has_user_right($user['photo_pub'], $view)) {
            $photo = "";
        }

        $page->assign_by_ref('x', $user);
        $page->assign('photo_url', $photo);
        // alias virtual
        $res = XDB::query(
                "SELECT alias
                   FROM virtual
             INNER JOIN virtual_redirect USING(vid)
             INNER JOIN auth_user_quick  ON ( user_id = {?} AND emails_alias_pub = 'public' )
                  WHERE ( redirect={?} OR redirect={?} )
                        AND alias LIKE '%@{$globals->mail->alias_dom}'",
                S::v('uid'),
                $user['forlife'].'@'.$globals->mail->domain,
                $user['forlife'].'@'.$globals->mail->domain2);
        $page->assign('virtualalias', $res->fetchOneCell());

        $page->addJsLink('close_on_esc.js');
    }

    function handler_p_edit(&$page, $opened_tab = 'general')
    {
        global $globals;

        $page->changeTpl('profile/edit.tpl');

        $page->addCssLink('profil.css');
        $page->assign('xorg_title', 'Polytechnique.org - Mon Profil');

        require_once 'tabs.inc.php';
        require_once 'profil.func.inc.php';
        require_once 'synchro_ax.inc.php';

        if (Post::v('register_from_ax_question')) {
            XDB::execute('UPDATE auth_user_quick
                                     SET profile_from_ax = 1
                                   WHERE user_id = {?}',
                                 S::v('uid'));
        }
        if (Post::v('add_to_nl')) {
            require_once 'newsletter.inc.php';
            subscribe_nl();
        }
        if (Post::v('add_to_promo')) {
            $r = XDB::query('SELECT id FROM groupex.asso WHERE diminutif = {?}',
                S::v('promo'));
            $asso_id = $r->fetchOneCell();
            XDB::execute('REPLACE INTO groupex.membres (uid,asso_id)
                                     VALUES ({?}, {?})',
                                 S::v('uid'), $asso_id);
            $mmlist = new MMList(S::v('uid'), S::v('password'));
            $mmlist->subscribe("promo".S::v('promo'));
        }

        if (is_ax_key_missing()) {
            $page->assign('no_private_key', true);
        }

        if (Env::v('synchro_ax') == 'confirm' && !is_ax_key_missing()) {
            ax_synchronize(S::v('bestalias'), S::v('uid'));
            $page->trig('Ton profil a été synchronisé avec celui du site polytechniciens.com');
        }

        // pour tous les tabs, la date de naissance pour verifier
        // quelle est bien rentree et la date.
        $res = XDB::query(
                "SELECT  naissance, DATE_FORMAT(date, '%d.%m.%Y')
                   FROM  auth_user_md5
                  WHERE  user_id={?}", S::v('uid'));
        list($naissance, $date_modif_profil) = $res->fetchOneRow();

        // lorsqu'on n'a pas la date de naissance en base de données
        if (!$naissance)  {
            // la date de naissance n'existait pas et vient d'être soumise dans la variable
            if (Env::has('birth')) {
                //en cas d'erreur :
                if (!ereg('[0-3][0-9][0-1][0-9][1][9]([0-9]{2})', Env::v('birth'))) {
                    $page->assign('etat_naissance', 'query');
                    $page->trig('Date de naissance incorrecte ou incohérente.');
                    return;
                }

                //sinon
                $birth = sprintf("%s-%s-%s", substr(Env::v('birth'), 4, 4),
                                 substr(Env::v('birth'), 2, 2),
                                 substr(Env::v('birth'), 0, 2));
                XDB::execute("UPDATE auth_user_md5
                                           SET naissance={?}
                                         WHERE user_id={?}", $birth,
                                       S::v('uid'));
                $page->assign('etat_naissance', 'ok');
                return;
            }

            $page->assign('etat_naissance', 'query');
            return; // on affiche le formulaire pour naissance
        }

        //doit-on faire un update ?
        if (Env::has('modifier') || Env::has('suivant')) {
            require_once "profil/get_{$opened_tab}.inc.php";
            require_once "profil/verif_{$opened_tab}.inc.php";

            if($page->nb_errs()) {
                require_once "profil/assign_{$opened_tab}.inc.php";
                $page->assign('onglet', $opened_tab);
                $page->assign('onglet_tpl', "profile/$opened_tab.tpl");
                return;
            }

            $date=date("Y-m-j");//nouvelle date de mise a jour

            //On sauvegarde l'uid pour l'AX
            /* on sauvegarde les changements dans user_changes :
            * on a juste besoin d'insérer le user_id de la personne dans la table
            */
            XDB::execute('REPLACE INTO user_changes SET user_id={?}',
                                   S::v('uid'));

            if (!S::has('suid')) {
                require_once 'notifs.inc.php';
                register_watch_op(S::v('uid'), WATCH_FICHE);
            }

            // mise a jour des champs relatifs au tab ouvert
            require_once "profil/update_{$opened_tab}.inc.php";

            $log =& $_SESSION['log'];
            $log->log('profil', $opened_tab);
            $page->assign('etat_update', 'ok');
        }

        if (Env::has('suivant')) {
            pl_redirect('profile/edit/' . get_next_tab($opened_tab));
        }

        require_once "profil/get_{$opened_tab}.inc.php";
        require_once "profil/verif_{$opened_tab}.inc.php";
        require_once "profil/assign_{$opened_tab}.inc.php";

        $page->assign('onglet', $opened_tab);
        $page->assign('onglet_tpl', "profile/$opened_tab.tpl");

        return;
    }

    function handler_p_orange(&$page)
    {
        $page->changeTpl('profile/orange.tpl');

        require_once 'validations.inc.php';
        require_once 'xorg.misc.inc.php';

        $res = XDB::query(
                "SELECT  u.promo, u.promo_sortie
                   FROM  auth_user_md5  AS u
                  WHERE  user_id={?}", S::v('uid'));

        list($promo, $promo_sortie_old) = $res->fetchOneRow();
        $page->assign('promo_sortie_old', $promo_sortie_old);
        $page->assign('promo',  $promo);

        if (!Env::has('promo_sortie')) {
            return;
        }

        $promo_sortie = Env::i('promo_sortie');

        if ($promo_sortie < 1000 || $promo_sortie > 9999) {
            $page->trig('L\'année de sortie doit être un nombre de quatre chiffres');
        }
        elseif ($promo_sortie < $promo + 3) {
            $page->trig('Trop tôt');
        }
        elseif ($promo_sortie == $promo_sortie_old) {
            $page->trig('Tu appartiens déjà à la promotion correspondante à cette année de sortie.');
        }
        elseif ($promo_sortie == $promo + 3) {
            XDB::execute(
                "UPDATE  auth_user_md5 set promo_sortie={?} 
                  WHERE  user_id={?}", $promo_sortie, S::v('uid'));
                $page->trig('Ton statut "orange" a été supprimé.');
                $page->assign('promo_sortie_old', $promo_sortie);
        }
        else {
            $page->assign('promo_sortie', $promo_sortie);

            if (Env::has('submit')) {
                $myorange = new OrangeReq(S::v('uid'),
                                          $promo_sortie);
                $myorange->submit();
                $page->assign('myorange', $myorange);
            }
        }
    }

    function handler_referent(&$page, $x = null)
    {
        require_once 'user.func.inc.php';

        if (is_null($x)) {
            return PL_NOT_FOUND;
        }

        $page->changeTpl('profile/fiche_referent.tpl', SIMPLE);

        $res = XDB::query(
                "SELECT  prenom, nom, user_id, promo, cv, a.alias AS bestalias
                  FROM  auth_user_md5 AS u
            INNER JOIN  aliases       AS a ON (u.user_id=a.id
                                               AND FIND_IN_SET('bestalias', a.flags))
            INNER JOIN  aliases       AS a1 ON (u.user_id=a1.id
                                                AND a1.alias = {?}
                                                AND a1.type!='homonyme')", $x);

        if ($res->numRows() != 1) {
            return PL_NOT_FOUND;
        }

        list($prenom, $nom, $user_id, $promo, $cv, $bestalias) = $res->fetchOneRow();

        $page->assign('prenom', $prenom);
        $page->assign('nom',    $nom);
        $page->assign('promo',  $promo);
        $page->assign('cv',     $cv);
        $page->assign('bestalias', $bestalias);
        $page->assign('adr_pro', get_user_details_pro($user_id));

        /////  recuperations infos referent

        //expertise
        $res = XDB::query("SELECT expertise FROM mentor WHERE uid = {?}", $user_id);
        $page->assign('expertise', $res->fetchOneCell());

        //secteurs
        $secteurs = $ss_secteurs = Array();
        $res = XDB::iterRow(
                "SELECT  s.label, ss.label
                   FROM  mentor_secteurs AS m
              LEFT JOIN  emploi_secteur AS s ON(m.secteur = s.id)
              LEFT JOIN  emploi_ss_secteur AS ss ON(m.secteur = ss.secteur AND m.ss_secteur = ss.id)
                  WHERE  uid = {?}", $user_id);
        while (list($sec, $ssec) = $res->next()) {
            $secteurs[]    = $sec;
            $ss_secteurs[] = $ssec;
        }
        $page->assign_by_ref('secteurs', $secteurs);
        $page->assign_by_ref('ss_secteurs', $ss_secteurs);

        //pays
        $res = XDB::query(
                "SELECT  gp.pays
                   FROM  mentor_pays AS m
              LEFT JOIN  geoloc_pays AS gp ON(m.pid = gp.a2)
                  WHERE  uid = {?}", $user_id);
        $page->assign('pays', $res->fetchColumn());

        $page->addJsLink('close_on_esc.js');
    }

    function handler_ref_search(&$page)
    {
        $page->changeTpl('profile/referent.tpl');

        $page->assign('xorg_title', 'Polytechnique.org - Conseil Pro');

        $secteur_sel     = Post::v('secteur');
        $ss_secteur_sel  = Post::v('ss_secteur');
        $pays_sel        = Post::v('pays', '00');
        $expertise_champ = Post::v('expertise');

        $page->assign('pays_sel', $pays_sel);
        $page->assign('expertise_champ', $expertise_champ);
        $page->assign('secteur_sel', $secteur_sel);
        $page->assign('ss_secteur_sel', $ss_secteur_sel);

        //recuperation des noms de secteurs
        $res = XDB::iterRow("SELECT id, label FROM emploi_secteur");
        $secteurs[''] = '';
        while (list($tmp_id, $tmp_label) = $res->next()) {
            $secteurs[$tmp_id] = $tmp_label;
        }
        $page->assign_by_ref('secteurs', $secteurs);

        //on recupere les sous-secteurs si necessaire
        $ss_secteurs[''] = '';
        if (!empty($secteur_sel)) {
            $res = XDB::iterRow("SELECT id, label FROM emploi_ss_secteur
                                          WHERE secteur = {?}", $secteur_sel);
            while (list($tmp_id, $tmp_label) = $res->next()) {
                $ss_secteurs[$tmp_id] = $tmp_label;
            }
        }
        $page->assign_by_ref('ss_secteurs', $ss_secteurs);

        //recuperation des noms de pays
        $res = XDB::iterRow("SELECT a2, pays FROM geoloc_pays
                                      WHERE pays <> '' ORDER BY pays");
        $pays['00'] = '';
        while (list($tmp_id, $tmp_label) = $res->next()) {
            $pays[$tmp_id] = $tmp_label;
        }
        $page->assign_by_ref('pays', $pays);

        // nb de mentors
        $res = XDB::query("SELECT count(*) FROM mentor");
        $page->assign('mentors_number', $res->fetchOneCell());

        if (!Env::has('Chercher')) {
            return;
        }

        // On vient d'un formulaire
        $where = array();

        if ($pays_sel != '00') {
            $where[] = "mp.pid = '".addslashes($pays_sel)."'";
        }
        if ($secteur_sel) {
            $where[] = "ms.secteur = '".addslashes($secteur_sel)."'";
            if ($ss_secteur_sel) {
                $where[] = "ms.ss_secteur = '".addslashes($ss_secteur_sel)."'";
            }
        }
        if ($expertise_champ) {
            $where[] = "MATCH(m.expertise) AGAINST('".addslashes($expertise_champ)."')";
        }

        if ($where) {
            $where = join(' AND ', $where);

            $sql = "SELECT  m.uid, a.prenom, a.nom, a.promo,
                            l.alias AS bestalias, m.expertise, mp.pid,
                            ms.secteur, ms.ss_secteur
                      FROM  mentor        AS m
                 LEFT JOIN  auth_user_md5 AS a ON(m.uid = a.user_id)
                INNER JOIN  aliases       AS l ON (a.user_id=l.id AND
                                                   FIND_IN_SET('bestalias', l.flags))
                 LEFT JOIN  mentor_pays   AS mp ON(m.uid = mp.uid)
                 LEFT JOIN  mentor_secteurs AS ms ON(m.uid = ms.uid)
                     WHERE  $where
                  GROUP BY  uid
                  ORDER BY  RAND({?})";
            $res = XDB::iterator($sql, S::v('uid'));

            if ($res->total() == 0) {
                $page->assign('recherche_trop_large', true);
                return;
            }

            $nb_max_res_total = 100;
            $nb_max_res_ppage = 10;

            $curpage   = Env::i('curpage', 1);
            $personnes = array();
            $i         = 0;

            while (($pers = $res->next()) && count($personnes) < $nb_max_res_total) {
                $the_page = intval($i / $nb_max_res_ppage) + 1;
                if ($the_page == $curpage) {
                    $personnes[] = $pers;
                }
                $i ++;
            }

            $page->assign('personnes', $personnes);
            $page->assign('curpage',   $curpage);
            $page->assign('nb_pages_total',
                          intval($res->total() / $nb_max_res_ppage) + 1);
        }
    }

    function handler_p_usage(&$page)
    {
        $page->changeTpl('profile/nomusage.tpl');

        require_once 'validations.inc.php';
        require_once 'xorg.misc.inc.php';

        $res = XDB::query(
                "SELECT  u.nom, u.nom_usage, u.flags, e.alias
                   FROM  auth_user_md5  AS u
              LEFT JOIN  aliases        AS e ON(u.user_id = e.id
                                                AND FIND_IN_SET('usage', e.flags))
                  WHERE  user_id={?}", S::v('uid'));

        list($nom, $usage_old, $flags, $alias_old) = $res->fetchOneRow();
        $flags = new flagset($flags);
        $page->assign('usage_old', $usage_old);
        $page->assign('alias_old',  $alias_old);

        $nom_usage = replace_accent(trim(Env::v('nom_usage'))); 
        $nom_usage = strtoupper($nom_usage);
        $page->assign('usage_req', $nom_usage);

        if (Env::has('submit') && ($nom_usage != $usage_old)) {
            // on vient de recevoir une requete, differente de l'ancien nom d'usage
            if ($nom_usage == $nom) {
                $page->assign('same', true);
            } else { // le nom de mariage est distinct du nom à l'X
                // on calcule l'alias pour l'afficher
                $reason = Env::v('reason');
                if ($reason == 'other') {
                    $reason = Env::v('other_reason');
                }
                $myusage = new UsageReq(S::v('uid'), $nom_usage, $reason);
                $myusage->submit();
                $page->assign('myusage', $myusage);
            }
        }
    }

    function handler_trombi(&$page, $promo = null)
    {
        $page->changeTpl('profile/trombipromo.tpl');
        $page->assign('xorg_title', 'Polytechnique.org - Trombi Promo');

        if (is_null($promo)) {
            return;
        }

        $this->promo = $promo = intval($promo);

        if ($promo >= 1900 && ($promo < intval(date('Y')) || ($promo == intval(date('Y')) && intval(date('m')) >= 9))
        || ($promo == -1 && S::has_perms()))
        {
            $trombi = new Trombi(array($this, '_trombi_getlist'));
            $trombi->hidePromo();
            $trombi->setAdmin();
            $page->assign_by_ref('trombi', $trombi);
        } else {
            $page->trig('Promotion incorrecte (saisir au format YYYY). Recommence.');
        }
    }

    function handler_xnet(&$page)
    {
        $page->changeTpl('profile/groupesx.tpl');
        $page->assign('xorg_title', 'Polytechnique.org - Promo, Groupes X, Binets');
        
        $req = XDB::query('
            SELECT m.asso_id, a.nom, diminutif, a.logo IS NOT NULL AS has_logo, 
                   COUNT(e.eid) AS events, mail_domain AS lists
              FROM groupex.membres AS m 
        INNER JOIN groupex.asso AS a ON(m.asso_id = a.id)
         LEFT JOIN groupex.evenements AS e ON(e.asso_id = m.asso_id AND e.archive = 0)
             WHERE uid = {?} GROUP BY m.asso_id ORDER BY a.nom', S::i('uid'));
        $page->assign('assos', $req->fetchAllAssoc());
    }
    
    function handler_vcard(&$page, $x = null)
    {
        if (is_null($x)) {
            return PL_NOT_FOUND;
        }

        global $globals;

        if (substr($x, -4) == '.vcf') {
            $x = substr($x, 0, strlen($x) - 4);
        }

        require_once('vcard.inc.php');
        $vcard = new VCard($x);
        $vcard->do_page($page);
    }

    function handler_admin_trombino(&$page, $uid = null, $action = null) {
        $page->changeTpl('profile/admin_trombino.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Trombino');
        $page->assign('uid', $uid);
        
        $q   = XDB::query(
                "SELECT  a.alias,promo
                  FROM  auth_user_md5 AS u
            INNER JOIN  aliases       AS a ON ( u.user_id = a.id AND type='a_vie' )
                 WHERE  user_id = {?}", $uid);
        list($forlife, $promo) = $q->fetchOneRow();
        
        switch ($action) {
        
            case "original":
                header("Content-type: image/jpeg");
        	readfile("/home/web/trombino/photos".$promo."/".$forlife.".jpg");
                exit;
        	break;
        
            case "new":
                $data = file_get_contents($_FILES['userfile']['tmp_name']);
            	list($x, $y) = getimagesize($_FILES['userfile']['tmp_name']);
            	$mimetype = substr($_FILES['userfile']['type'], 6);
            	unlink($_FILES['userfile']['tmp_name']);
                XDB::execute(
                        "REPLACE INTO photo SET uid={?}, attachmime = {?}, attach={?}, x={?}, y={?}",
                        $uid, $mimetype, $data, $x, $y);
            	break;
        
            case "delete":
                XDB::execute('DELETE FROM photo WHERE uid = {?}', $uid);
                break;
        }
        
        $page->assign('forlife', $forlife);
    }
    function handler_admin_binets(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title','Polytechnique.org - Administration - Binets');
        $page->assign('title', 'Gestion des binets');
        $table_editor = new PLTableEditor('admin/binets', 'binets_def', 'id');
        $table_editor->add_join_table('binets_ins','binet_id',true);
        $table_editor->describe('text','intitulé',true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_formations(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title','Polytechnique.org - Administration - Formations');
        $page->assign('title', 'Gestion des formations');
        $table_editor = new PLTableEditor('admin/formations','applis_def','id');
        $table_editor->add_join_table('applis_ins','aid',true); 
        $table_editor->describe('text','intitulé',true);
        $table_editor->describe('url','site web',false);
        $table_editor->apply($page, $action, $id);
    } 
    function handler_admin_groupesx(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title','Polytechnique.org - Administration - Groupes X');
        $page->assign('title', 'Gestion des Groupes X');
        $table_editor = new PLTableEditor('admin/groupes-x','groupesx_def','id');
        $table_editor->add_join_table('groupesx_ins','gid',true); 
        $table_editor->describe('text','intitulé',true);
        $table_editor->describe('url','site web',false);
        $table_editor->apply($page, $action, $id);
    }  
    function handler_admin_medals(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title','Polytechnique.org - Administration - Distinctions');
        $page->assign('title', 'Gestion des Distinctions');
        $table_editor = new PLTableEditor('admin/medals','profile_medals','id');
        $table_editor->describe('text', 'intitulé',  true);
        $table_editor->describe('img',  'nom de l\'image', false);
        $table_editor->apply($page, $action, $id);
        if ($id && $action == 'edit') {
            $page->changeTpl('profile/admin_decos.tpl');
        
            $mid = $id;
        
            if (Post::v('act') == 'del') {
                XDB::execute('DELETE FROM profile_medals_grades WHERE mid={?} AND gid={?}', $mid, Post::i('gid'));
            } elseif (Post::v('act') == 'new') {
                XDB::execute('INSERT INTO profile_medals_grades (mid,gid) VALUES({?},{?})',
                        $mid, max(array_keys(Post::v('grades', array(0))))+1);
            } else {
                foreach (Post::v('grades', array()) as $gid=>$text) {
                    XDB::execute('UPDATE profile_medals_grades SET pos={?}, text={?} WHERE gid={?}', $_POST['pos'][$gid], $text, $gid);
                }
            }
            $res = XDB::iterator('SELECT gid, text, pos FROM profile_medals_grades WHERE mid={?} ORDER BY pos', $mid);
            $page->assign('grades', $res);
        }
    }   
}

?>
