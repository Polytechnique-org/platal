<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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
            'profile/ax'       => $this->make_hook('ax',         AUTH_COOKIE, 'admin'),
            'profile/edit'     => $this->make_hook('p_edit',     AUTH_MDP),
            'profile/ajax/address' => $this->make_hook('ajax_address', AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/tel'     => $this->make_hook('ajax_tel',     AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/medal'   => $this->make_hook('ajax_medal',   AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/job'     => $this->make_hook('ajax_job',     AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/secteur' => $this->make_hook('ajax_secteur', AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/skill'   => $this->make_hook('ajax_skill',   AUTH_COOKIE, 'user', NO_AUTH),
            'javascript/applis.js' => $this->make_hook('applis_js', AUTH_COOKIE),
            'javascript/grades.js' => $this->make_hook('grades_js', AUTH_COOKIE),
            'profile/medal'    => $this->make_hook('medal', AUTH_PUBLIC),
            'profile/orange'   => $this->make_hook('p_orange',   AUTH_MDP),
            'profile/usage'    => $this->make_hook('p_usage',    AUTH_MDP),

            'referent'         => $this->make_hook('referent',   AUTH_COOKIE),
            'emploi'           => $this->make_hook('ref_search', AUTH_COOKIE),
            'referent/search'  => $this->make_hook('ref_search', AUTH_COOKIE),
            'referent/ssect'   => $this->make_hook('ref_sect',   AUTH_COOKIE, 'user', NO_AUTH),
            'referent/country' => $this->make_hook('ref_country', AUTH_COOKIE, 'user', NO_AUTH),

            'groupes-x'        => $this->make_hook('xnet',      AUTH_COOKIE),

            'vcard'   => $this->make_hook('vcard',  AUTH_COOKIE, 'user', NO_HTTPS),
            'admin/binets'     => $this->make_hook('admin_binets', AUTH_MDP, 'admin'),
            'admin/medals'     => $this->make_hook('admin_medals', AUTH_MDP, 'admin'),
            'admin/formations' => $this->make_hook('admin_formations', AUTH_MDP, 'admin'),
            'admin/sections'  => $this->make_hook('admin_sections', AUTH_MDP, 'admin'),
            'admin/secteurs'  => $this->make_hook('admin_secteurs', AUTH_MDP, 'admin'),
            'admin/trombino'   => $this->make_hook('admin_trombino', AUTH_MDP, 'admin'),
            'admin/ss_secteurs'  => $this->make_hook('admin_ss_secteurs', AUTH_MDP, 'admin'),
            'admin/fonctions'  => $this->make_hook('admin_fonctions', AUTH_MDP, 'admin'),

        );
    }

    /* XXX COMPAT */
    function handler_fiche(&$page)
    {
        return $this->handler_profile($page, Env::v('user'));
    }

    function handler_photo(&$page, $x = null, $req = null)
    {
        if (!$x || !($user = User::getSilent($x))) {
            return PL_NOT_FOUND;
        }

        // Retrieve the photo and its mime type.
        $photo_data = null;
        $photo_type = null;

        if ($req && S::logged()) {
            include 'validations.inc.php';
            $myphoto = PhotoReq::get_request($user->id());
            if ($myphoto) {
                $photo_data = $myphoto->data;
                $photo_type = $myphoto->mimetype;
            }
        } else {
            $res = XDB::query(
                    "SELECT  attachmime, attach, pub
                       FROM  photo
                      WHERE  uid = {?}", $user->id());
            list($photo_type, $photo_data, $photo_pub) = $res->fetchOneRow();
            if ($photo_pub != 'public' && !S::logged()) {
                $photo_type = $photo_data = null;
            }
        }

        // Display the photo, or a default one when not available.
        if ($photo_type && $photo_data != null) {
            header('Content-type: image/' . $photo_type);
            echo $photo_data;
        } else {
            header('Content-type: image/png');
            echo file_get_contents(dirname(__FILE__).'/../htdocs/images/none.png');
        }
        exit;
    }

    function handler_medal(&$page, $mid)
    {
        $thumb = ($mid == 'thumb');
        $mid = $thumb ? @func_get_arg(2) : $mid;

        $res = XDB::query("SELECT  img
                             FROM  profile_medals
                            WHERE  id = {?}",
                          $mid);
        $img  = $thumb ?
            dirname(__FILE__).'/../htdocs/images/medals/thumb/' . $res->fetchOneCell() :
            dirname(__FILE__).'/../htdocs/images/medals/' . $res->fetchOneCell();
        $type = mime_content_type($img);
        header("Content-Type: $type");
        echo file_get_contents($img);
        exit;
    }

    function handler_photo_change(&$page)
    {
        global $globals;
        $page->changeTpl('profile/trombino.tpl');

        require_once('validations.inc.php');

        $trombi_x = '/home/web/trombino/photos'.S::v('promo')
                    .'/'.S::v('forlife').'.jpg';

        if (Env::has('upload')) {
            S::assert_xsrf_token();

            $upload = new PlUpload(S::user()->login(), 'photo');
            if (!$upload->upload($_FILES['userfile']) && !$upload->download(Env::v('photo'))) {
                $page->trigError('Une erreur est survenue lors du téléchargement du fichier');
            } else {
                $myphoto = new PhotoReq(S::v('uid'), $upload);
                if ($myphoto->isValid()) {
                    $myphoto->submit();
                }
            }
        } elseif (Env::has('trombi')) {
            S::assert_xsrf_token();

            $upload = new PlUpload(S::user()->login(), 'photo');
            if ($upload->copyFrom($trombi_x)) {
                $myphoto = new PhotoReq(S::v('uid'), $upload);
                if ($myphoto->isValid()) {
                    $myphoto->commit();
                    $myphoto->clean();
                }
            }
        } elseif (Env::v('suppr')) {
            S::assert_xsrf_token();

            XDB::execute('DELETE FROM  photo
                                WHERE  uid = {?}',
                         S::v('uid'));
            XDB::execute('DELETE FROM  requests
                                WHERE  user_id = {?} AND type="photo"',
                         S::v('uid'));
            $globals->updateNbValid();
        } elseif (Env::v('cancel')) {
            S::assert_xsrf_token();

            $sql = XDB::query('DELETE FROM  requests
                                     WHERE  user_id={?} AND type="photo"',
                              S::v('uid'));
            $globals->updateNbValid();
        }

        $sql = XDB::query('SELECT  COUNT(*)
                             FROM  requests
                            WHERE  user_id={?} AND type="photo"',
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

        $page->changeTpl('profile/profile.tpl', SIMPLE);

        $view = 'private';
        if (!S::logged() || Env::v('view') == 'public') $view = 'public';
        if (S::logged() && Env::v('view') == 'ax')      $view = 'ax';

        $login = S::logged() ? User::get($x) : User::getSilent($x);
        if (!$login) {
            return PL_NOT_FOUND;
        }

        $res = XDB::query("SELECT  perms IN ('admin','user','disabled')
                             FROM  auth_user_md5
                            WHERE  user_id = {?}", $login->id());
        if (!$res->fetchOneCell()) {
            $user = get_not_registered_user($x, true);
            if ($user->total() != 1) {
                return PL_NOT_FOUND;
            }
            $user = $user->next();
            if (S::logged()) {
                pl_redirect('marketing/public/' . $user['user_id']);
            }
            $user['forlife'] = $x;
        } else {
            $new   = Env::v('modif') == 'new';
            $user  = get_user_details($login->login(), S::v('uid'), $view);
        }

        if (S::logged()) {
            S::logger()->log('view_profile', $login->login());
        }

        $title = $user['prenom'] . ' ' . ( empty($user['nom_usage']) ? $user['nom'] : $user['nom_usage'] );
        $page->setTitle($title);

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
                $user['user_id'],
                $user['forlife'].'@'.$globals->mail->domain,
                $user['forlife'].'@'.$globals->mail->domain2);
        $page->assign('virtualalias', $res->fetchOneCell());
        $page->assign('view', $view);

        $page->addJsLink('close_on_esc.js');
        header('Last-Modified: ' . date('r', strtotime($user['date'])));
    }

    function handler_ax(&$page, $user = null)
    {
        $user = User::get($user);
        if (!$user) {
            return PL_NOT_FOUND;
        }

        $res = XDB::query("SELECT  matricule_ax
                             FROM  auth_user_md5
                            WHERE  user_id = {?}", $user->id());
        $mat = $res->fetchOneCell();
        if (!intval($mat)) {
            $page->kill("Le matricule AX de {$user->login()} est inconnu");
        }
        http_redirect("http://www.polytechniciens.com/?page=AX_FICHE_ANCIEN&anc_id=$mat");
    }

    function handler_p_edit(&$page, $opened_tab = null, $mode = null)
    {
        global $globals;

        // AX Synchronization
        require_once 'synchro_ax.inc.php';
        if (is_ax_key_missing()) {
            $page->assign('no_private_key', true);
        }
        if (Env::v('synchro_ax') == 'confirm' && !is_ax_key_missing()) {
            ax_synchronize(S::v('bestalias'), S::v('uid'));
            $page->trigSuccess('Ton profil a été synchronisé avec celui du site polytechniciens.com');
        }

        // Build the page
        $page->addJsLink('ajax.js');
        $page->addJsLink('applis.js');
        $page->addJsLink('grades.js');
        $page->addJsLink('profile.js');
        $page->addJsLink('jquery.autocomplete.js');
        $wiz = new PlWizard('Profil', PlPage::getCoreTpl('plwizard.tpl'), true, true);
        $this->load('page.inc.php');
        $wiz->addPage('ProfileGeneral', 'Général', 'general');
        $wiz->addPage('ProfileAddresses', 'Adresses personnelles', 'adresses');
        $wiz->addPage('ProfileGroups', 'Groupes X - Binets', 'poly');
        $wiz->addPage('ProfileDecos', 'Décorations - Medailles', 'deco');
        $wiz->addPage('ProfileJobs', 'Informations professionnelles', 'emploi');
        $wiz->addPage('ProfileSkills', 'Compétences diverses', 'skill');
        $wiz->addPage('ProfileMentor', 'Mentoring', 'mentor');
        $wiz->apply($page, 'profile/edit', $opened_tab, $mode);

         // Misc checks
        $res = XDB::query("SELECT  user_id
                             FROM  auth_user_md5
                            WHERE  user_id = {?} AND naissance = '0000-00-00'", S::i('uid'));
        if ($res->numRows()) {
            $page->trigWarning("Ta date de naissance n'est pas renseignée, ce qui t'empêcheras de réaliser"
                      . " la procédure de récupération de mot de passe si un jour tu le perdais");
        }

       $page->setTitle('Mon Profil');
    }

    function handler_applis_js(&$page)
    {
        header('Content-Type: text/javascript; charset=utf-8');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified:' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        $page->changeTpl('profile/applis.js.tpl', NO_SKIN);
        require_once "applis.func.inc.php";
    }

    function handler_grades_js(&$page)
    {
        header('Content-Type: text/javascript; charset=utf-8');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified:' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        $page->changeTpl('profile/grades.js.tpl', NO_SKIN);
        $res    = XDB::iterator("SELECT  *
                                   FROM  profile_medals_grades
                               ORDER BY  mid, pos");
        $grades = array();
        while ($tmp = $res->next()) {
            $grades[$tmp['mid']][] = $tmp;
        }
        $page->assign('grades', $grades);

        $res    = XDB::iterator("SELECT  *, FIND_IN_SET('validation', flags) AS validate
                                   FROM  profile_medals
                               ORDER BY  type, text");
        $mlist  = array();
        while ($tmp = $res->next()) {
            $mlist[$tmp['type']][] = $tmp;
        }
        $page->assign('medal_list', $mlist);
    }

    function handler_ajax_address(&$page, $adid)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('profile/adresses.address.tpl', NO_SKIN);
        $page->assign('i', $adid);
        $page->assign('adr', array());
    }

    function handler_ajax_tel(&$page, $adid, $telid)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('profile/adresses.tel.tpl', NO_SKIN);
        $page->assign('i', $adid);
        $page->assign('adid', "addresses_$adid");
        $page->assign('adpref', "addresses[$adid]");
        $page->assign('t', $telid);
        $page->assign('tel', array());
    }

    function handler_ajax_medal(&$page, $id)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('profile/deco.medal.tpl', NO_SKIN);
        $page->assign('id', $id);
        $page->assign('medal', array('valid' => 0, 'grade' => 0));
    }

    function handler_ajax_job(&$page, $id)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('profile/jobs.job.tpl', NO_SKIN);
        $page->assign('i', $id);
        $page->assign('job', array());
        $page->assign('new', true);
        $page->assign('secteurs', XDB::iterator("SELECT  id, label
                                                   FROM  emploi_secteur"));
        $page->assign('fonctions', XDB::iterator("SELECT  id, fonction_fr, FIND_IN_SET('titre', flags) AS title
                                                    FROM  fonctions_def
                                                ORDER BY  id"));
    }

    function handler_ajax_secteur(&$page, $id, $sect, $ssect = -1)
    {
        header('Content-Type: text/html; charset=utf-8');
        $res = XDB::iterator("SELECT  id, label
                                FROM  emploi_ss_secteur
                               WHERE  secteur = {?}", $sect);
        $page->changeTpl('profile/jobs.secteur.tpl', NO_SKIN);
        $page->assign('id', $id);
        $page->assign('ssecteurs', $res);
        $page->assign('sel', $ssect);
    }

    function handler_ajax_skill(&$page, $cat, $id)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('profile/skill.skill.tpl', NO_SKIN);
        $page->assign('cat', $cat);
        $page->assign('id', $id);
        if ($cat == 'competences') {
          $page->assign('levels', array('initié' => 'initié',
                                        'bonne connaissance' => 'bonne connaissance',
                                        'expert' => 'expert'));
        } else {
          $page->assign('levels', array(1 => 'connaissance basique',
                                        2 => 'maîtrise des bases',
                                        3 => 'maîtrise limitée',
                                        4 => 'maîtrise générale',
                                        5 => 'bonne maîtrise',
                                        6 => 'maîtrise complète'));
        }
    }

    function handler_p_orange(&$page)
    {
        $page->changeTpl('profile/orange.tpl');

        require_once 'validations.inc.php';

        $res = XDB::query(
                "SELECT  u.promo, u.promo_sortie
                   FROM  auth_user_md5  AS u
                  WHERE  user_id={?}", S::v('uid'));

        list($promo, $promo_sortie_old) = $res->fetchOneRow();
        $page->assign('promo_sortie_old', $promo_sortie_old);
        $page->assign('promo',  $promo);

        if (!Env::has('promo_sortie')) {
            return;
        } else {
            S::assert_xsrf_token();
        }

        $promo_sortie = Env::i('promo_sortie');

        if ($promo_sortie < 1000 || $promo_sortie > 9999) {
            $page->trigError('L\'année de sortie doit être un nombre de quatre chiffres');
        }
        elseif ($promo_sortie < $promo + 3) {
            $page->trigError('Trop tôt');
        }
        elseif ($promo_sortie == $promo_sortie_old) {
            $page->trigWarning('Tu appartiens déjà à la promotion correspondante à cette année de sortie.');
        }
        elseif ($promo_sortie == $promo + 3) {
            XDB::execute(
                "UPDATE  auth_user_md5 set promo_sortie={?}
                  WHERE  user_id={?}", $promo_sortie, S::v('uid'));
                $page->trigSuccess('Ton statut "orange" a été supprimé.');
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
        $page->assign('cv',     MiniWiki::WikiToHTML($cv, true));
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

    function handler_ref_search(&$page, $action = null, $subaction = null)
    {
        $wp = new PlWikiPage('Docs.Emploi');
        $wp->buildCache();

        $page->setTitle('Conseil Pro');

        //recuperation des noms de secteurs
        $res = XDB::iterRow("SELECT id, label FROM emploi_secteur");
        $secteurs[''] = '';
        while (list($tmp_id, $tmp_label) = $res->next()) {
            $secteurs[$tmp_id] = $tmp_label;
        }
        $page->assign_by_ref('secteurs', $secteurs);

        // nb de mentors
        $res = XDB::query("SELECT count(*) FROM mentor");
        $page->assign('mentors_number', $res->fetchOneCell());

        // On vient d'un formulaire
        $where           = array();
        $pays_sel        = XDB::escape(Env::v('pays_sel'));
        $secteur_sel     = XDB::escape(Env::v('secteur'));
        $ss_secteur_sel  = XDB::escape(Env::v('ss_secteur'));
        $expertise_champ = XDB::escape(Env::v('expertise'));

        if ($pays_sel != "''") {
            $where[] = "mp.pid = $pays_sel";
        }
        if ($secteur_sel != "''") {
            $where[] = "ms.secteur = $secteur_sel";
            if ($ss_secteur_sel != "''") {
                $where[] = "ms.ss_secteur = $ss_secteur_sel";
            }
        }
        if ($expertise_champ != "''") {
            $where[] = "MATCH(m.expertise) AGAINST($expertise_champ)";
        }

        if ($where) {
            $where = join(' AND ', $where);

            $set = new UserSet("INNER JOIN  mentor          AS m ON (m.uid = u.user_id)
                                 LEFT JOIN  mentor_pays     AS mp ON (mp.uid = m.uid)
                                 LEFT JOIN  mentor_secteurs AS ms ON (ms.uid = m.uid)",
                               $where);
            $set->addMod('mentor', 'Référents');
            $set->apply('referent/search', $page, $action, $subaction);
            if ($set->count() > 100) {
                $page->assign('recherche_trop_large', true);
            }
        }
        $page->changeTpl('profile/referent.tpl');
    }

    function handler_ref_sect(&$page, $sect)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('include/field.select.tpl', NO_SKIN);
        $page->assign('onchange', 'setSSecteurs()');
        $page->assign('id', 'ssect_field');
        $page->assign('name', 'ss_secteur');
        $it = XDB::iterator("SELECT  id,label AS field
                               FROM  emploi_ss_secteur
                              WHERE  secteur = {?}", $sect);
        $page->assign('list', $it);
    }

    function handler_ref_country(&$page, $sect, $ssect = '')
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('include/field.select.tpl', NO_SKIN);
        $page->assign('name', 'pays_sel');
        $where = ($ssect ? ' AND ms.ss_secteur = {?}' : '');
        $it = XDB::iterator("SELECT  a2 AS id, pays AS field
                              FROM  geoloc_pays AS g
                        INNER JOIN  mentor_pays AS mp ON (mp.pid = g.a2)
                        INNER JOIN  mentor_secteurs AS ms ON (ms.uid = mp.uid)
                             WHERE  ms.secteur = {?} $where
                          GROUP BY  a2
                          ORDER BY  pays", $sect, $ssect);
        $page->assign('list', $it);
    }

    function handler_p_usage(&$page)
    {
        $page->changeTpl('profile/nomusage.tpl');

        require_once 'validations.inc.php';

        $res = XDB::query(
                "SELECT  u.nom, u.nom_usage, u.flags, e.alias
                   FROM  auth_user_md5  AS u
              LEFT JOIN  aliases        AS e ON(u.user_id = e.id
                                                AND FIND_IN_SET('usage', e.flags))
                  WHERE  user_id={?}", S::v('uid'));

        list($nom, $usage_old, $flags, $alias_old) = $res->fetchOneRow();
        $flags = new PlFlagSet($flags);
        $page->assign('usage_old', $usage_old);
        $page->assign('alias_old',  $alias_old);

        $nom_usage = replace_accent(trim(Env::v('nom_usage')));
        $nom_usage = strtoupper($nom_usage);
        $page->assign('usage_req', $nom_usage);

        if (Env::has('submit') && ($nom_usage != $usage_old)) {
            S::assert_xsrf_token();

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

    function handler_xnet(&$page)
    {
        $page->changeTpl('profile/groupesx.tpl');
        $page->setTitle('Promo, Groupes X, Binets');

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

        $vcard = new VCard($x);
        $vcard->do_page($page);
    }

    function handler_admin_trombino(&$page, $uid = null, $action = null) {
        $page->changeTpl('profile/admin_trombino.tpl');
        $page->setTitle('Administration - Trombino');
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
                S::assert_xsrf_token();

                $data = file_get_contents($_FILES['userfile']['tmp_name']);
            	list($x, $y) = getimagesize($_FILES['userfile']['tmp_name']);
            	$mimetype = substr($_FILES['userfile']['type'], 6);
            	unlink($_FILES['userfile']['tmp_name']);
                XDB::execute(
                        "REPLACE INTO photo SET uid={?}, attachmime = {?}, attach={?}, x={?}, y={?}",
                        $uid, $mimetype, $data, $x, $y);
            	break;

            case "delete":
                S::assert_xsrf_token();

                XDB::execute('DELETE FROM photo WHERE uid = {?}', $uid);
                break;
        }

        $page->assign('forlife', $forlife);
    }
    function handler_admin_binets(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Binets');
        $page->assign('title', 'Gestion des binets');
        $table_editor = new PLTableEditor('admin/binets', 'binets_def', 'id');
        $table_editor->add_join_table('binets_ins','binet_id',true);
        $table_editor->describe('text','intitulé',true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_formations(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Formations');
        $page->assign('title', 'Gestion des formations');
        $table_editor = new PLTableEditor('admin/formations','applis_def','id');
        $table_editor->add_join_table('applis_ins','aid',true);
        $table_editor->describe('text','intitulé',true);
        $table_editor->describe('url','site web',false);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_sections(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Sections');
        $page->assign('title', 'Gestion des sections');
        $table_editor = new PLTableEditor('admin/sections','sections','id');
        $table_editor->describe('text','intitulé',true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_ss_secteurs(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Sous-secteurs');
        $page->assign('title', 'Gestion des sous-secteurs');
        $table_editor = new PLTableEditor('admin/ss_secteurs', 'emploi_ss_secteur', 'id', true);
        $table_editor->describe('label', 'intitulé', true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_fonctions(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Fonctions');
        $page->assign('title', 'Gestion des fonctions');
        $table_editor = new PLTableEditor('admin/fonctions', 'fonctions_def', 'id', true);
        $table_editor->describe('fonction_fr', 'intitulé', true);
        $table_editor->describe('fonction_en', 'intitulé (ang)', true);
        $table_editor->describe('flags', 'titre', true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_secteurs(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Secteurs');
        $page->assign('title', 'Gestion des secteurs');
        $table_editor = new PLTableEditor('admin/secteurs', 'emploi_secteur', 'id', true);
        $table_editor->describe('label', 'intitulé', true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_medals(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Distinctions');
        $page->assign('title', 'Gestion des Distinctions');
        $table_editor = new PLTableEditor('admin/medals','profile_medals','id');
        $table_editor->describe('text', 'intitulé',  true);
        $table_editor->describe('img',  'nom de l\'image', false);
        $table_editor->describe('flags', 'valider', true);
        $table_editor->apply($page, $action, $id);
        if ($id && $action == 'edit') {
            $page->changeTpl('profile/admin_decos.tpl');

            $mid = $id;

            if (Post::v('act') == 'del') {
                XDB::execute('DELETE FROM  profile_medals_grades
                                    WHERE  mid={?} AND gid={?}', $mid, Post::i('gid'));
            } else {
                foreach (Post::v('grades', array()) as $gid=>$text) {
                    if ($gid === 0) {
                        if (!empty($text)) {
                            $res = XDB::query('SELECT  MAX(gid)
                                                 FROM  profile_medals_grades
                                                WHERE  mid = {?}', $mid);
                            $gid = $res->fetchOneCell() + 1;

                            XDB::execute('INSERT INTO  profile_medals_grades (mid, gid, text, pos)
                                               VALUES  ({?}, {?}, {?}, {?})',
                                $mid, $gid, $text, $_POST['pos']['0']);
                        }
                    } else {
                        XDB::execute('UPDATE  profile_medals_grades
                                         SET  pos={?}, text={?}
                                       WHERE  gid={?} AND mid={?}', $_POST['pos'][$gid], $text, $gid, $mid);
                    }
                }
            }
            $res = XDB::iterator('SELECT gid, text, pos FROM profile_medals_grades WHERE mid={?} ORDER BY pos', $mid);
            $page->assign('grades', $res);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
