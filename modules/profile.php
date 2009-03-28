<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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
            'photo'                      => $this->make_hook('photo',                      AUTH_PUBLIC),
            'photo/change'               => $this->make_hook('photo_change',               AUTH_MDP),

            'fiche.php'                  => $this->make_hook('fiche',                      AUTH_PUBLIC),
            'profile'                    => $this->make_hook('profile',                    AUTH_PUBLIC),
            'profile/private'            => $this->make_hook('profile',                    AUTH_COOKIE),
            'profile/ax'                 => $this->make_hook('ax',                         AUTH_COOKIE, 'admin'),
            'profile/edit'               => $this->make_hook('p_edit',                     AUTH_MDP),
            'profile/ajax/address'       => $this->make_hook('ajax_address',               AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/tel'           => $this->make_hook('ajax_tel',                   AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/edu'           => $this->make_hook('ajax_edu',                   AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/medal'         => $this->make_hook('ajax_medal',                 AUTH_COOKIE, 'user', NO_AUTH),
            'profile/networking'         => $this->make_hook('networking',                 AUTH_PUBLIC),
            'profile/ajax/job'           => $this->make_hook('ajax_job',                   AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/sector'        => $this->make_hook('ajax_sector',                AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/sub_sector'    => $this->make_hook('ajax_sub_sector',            AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/skill'         => $this->make_hook('ajax_skill',                 AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/searchname'    => $this->make_hook('ajax_searchname',            AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/buildnames'    => $this->make_hook('ajax_buildnames',            AUTH_COOKIE, 'user', NO_AUTH),
            'javascript/education.js'    => $this->make_hook('education_js',               AUTH_COOKIE),
            'javascript/grades.js'       => $this->make_hook('grades_js',                  AUTH_COOKIE),
            'profile/medal'              => $this->make_hook('medal',                      AUTH_PUBLIC),
            'profile/name_info'          => $this->make_hook('name_info',                  AUTH_PUBLIC),
            'profile/orange'             => $this->make_hook('p_orange',                   AUTH_MDP),

            'referent'                   => $this->make_hook('referent',                   AUTH_COOKIE),
            'emploi'                     => $this->make_hook('ref_search',                 AUTH_COOKIE),
            'referent/search'            => $this->make_hook('ref_search',                 AUTH_COOKIE),
            'referent/ssect'             => $this->make_hook('ref_sect',                   AUTH_COOKIE, 'user', NO_AUTH),
            'referent/country'           => $this->make_hook('ref_country',                AUTH_COOKIE, 'user', NO_AUTH),

            'groupes-x'                  => $this->make_hook('xnet',                       AUTH_COOKIE),

            'vcard'                      => $this->make_hook('vcard',                      AUTH_COOKIE, 'user', NO_HTTPS),
            'admin/binets'               => $this->make_hook('admin_binets',               AUTH_MDP, 'admin'),
            'admin/medals'               => $this->make_hook('admin_medals',               AUTH_MDP, 'admin'),
            'admin/education'            => $this->make_hook('admin_education',            AUTH_MDP, 'admin'),
            'admin/education_field'      => $this->make_hook('admin_education_field',      AUTH_MDP, 'admin'),
            'admin/education_degree'     => $this->make_hook('admin_education_degree',     AUTH_MDP, 'admin'),
            'admin/education_degree_set' => $this->make_hook('admin_education_degree_set', AUTH_MDP, 'admin'),
            'admin/sections'             => $this->make_hook('admin_sections',             AUTH_MDP, 'admin'),
            'admin/networking'           => $this->make_hook('admin_networking',           AUTH_MDP, 'admin'),
            'admin/trombino'             => $this->make_hook('admin_trombino',             AUTH_MDP, 'admin'),
            'admin/fonctions'            => $this->make_hook('admin_fonctions',            AUTH_MDP, 'admin'),
            'admin/corps_enum'           => $this->make_hook('admin_corps_enum',           AUTH_MDP, 'admin'),
            'admin/corps_rank'           => $this->make_hook('admin_corps_rank',           AUTH_MDP, 'admin'),
            'admin/names'                => $this->make_hook('admin_names',                AUTH_MDP, 'admin'),

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

    function handler_name_info(&$page)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('profile/name_info.tpl', SIMPLE);
        $res = XDB::iterator("SELECT  name, explanations,
                                      FIND_IN_SET('public', flags) AS public,
                                      FIND_IN_SET('has_particle', flags) AS has_particle
                                FROM  profile_name_enum
                               WHERE  NOT FIND_IN_SET('not_displayed', flags)
                            ORDER BY  NOT FIND_IN_SET('public', flags)");
        $page->assign('types', $res);
    }

    function handler_networking(&$page, $mid)
    {
        $res = XDB::query("SELECT  icon
                             FROM  profile_networking_enum
                            WHERE  network_type = {?}",
                          $mid);
        $img  = dirname(__FILE__) . '/../htdocs/images/networking/' . $res->fetchOneCell();
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

        $trombi_x = '/home/web/trombino/photos' . S::v('promo') . '/' . S::user()->login() . '.jpg';
        if (Env::has('upload')) {
            S::assert_xsrf_token();

            $upload = new PlUpload(S::user()->login(), 'photo');
            if (!$upload->upload($_FILES['userfile']) && !$upload->download(Env::v('photo'))) {
                $page->trigError('Une erreur est survenue lors du téléchargement du fichier');
            } else {
                $myphoto = new PhotoReq(S::user(), $upload);
                if ($myphoto->isValid()) {
                    $myphoto->submit();
                }
            }
        } elseif (Env::has('trombi')) {
            S::assert_xsrf_token();

            $upload = new PlUpload(S::user()->login(), 'photo');
            if ($upload->copyFrom($trombi_x)) {
                $myphoto = new PhotoReq(S::user(), $upload);
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
        // TODO/note for upcoming developers:
        // We currently maintain both $user and $login; $user is the old way of
        // obtaining information, and eventually everything will be loaded
        // through $login. That is the reason why in the template $user is named
        // $x, and $login $user (sorry for the confusion).

        // Determines which user to display the profile of, and retrieves basic
        // information on this user.
        if (is_null($x)) {
            return PL_NOT_FOUND;
        }

        $login = S::logged() ? User::get($x) : User::getSilent($x);
        if (!$login) {
            return PL_NOT_FOUND;
        }

        // Now that we know this is the profile of an existing user, we can
        // switch to the appropriate template.
        $page->changeTpl('profile/profile.tpl', SIMPLE);
        require_once 'user.func.inc.php';

        // Determines the access level at which the profile will be displayed.
        if (!S::logged() || Env::v('view') == 'public') {
            $view = 'public';
        } else if (S::logged() && Env::v('view') == 'ax') {
            $view = 'ax';
        } else {
            $view = 'private';
        }

        // Determines is the user is registered, and fetches the user infos in
        // the appropriate way.
        $res = XDB::query("SELECT  perms IN ('admin','user','disabled')
                             FROM  auth_user_md5
                            WHERE  user_id = {?}", $login->id());
        if ($res->fetchOneCell()) {
            $new  = Env::v('modif') == 'new';
            $user = get_user_details($login->login(), S::v('uid'), $view);
        } else {
            $new  = false;
            $user = array();
            if (S::logged()) {
                pl_redirect('marketing/public/' . $login->login());
            }
        }

        // Profile view are logged.
        if (S::logged()) {
            S::logger()->log('view_profile', $login->login());
        }

        // Sets the title of the html page.
        $page->setTitle($login->fullName());

        // Prepares the display of the user's mugshot.
        $photo = 'photo/' . $login->login() . ($new ? '/req' : '');
        if (!isset($user['photo_pub']) || !has_user_right($user['photo_pub'], $view)) {
            $photo = "";
        }
        $page->assign('photo_url', $photo);

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

        // Determines and displays the virtual alias.
        global $globals;
        $res = XDB::query(
                "SELECT  alias
                   FROM  virtual
             INNER JOIN  virtual_redirect USING (vid)
             INNER JOIN  auth_user_quick ON (user_id = {?} AND emails_alias_pub = 'public')
                  WHERE  (redirect={?} OR redirect={?})
                         AND alias LIKE '%@{$globals->mail->alias_dom}'",
                $login->id(),
                $login->forlifeEmail(),
                // TODO(vzanotti): get ride of all @m4x.org addresses in the
                // virtual redirect base, and remove this über-ugly hack.
                $login->login() . '@' . $globals->mail->domain2);
        $page->assign('virtualalias', $res->fetchOneCell());

        // Adds miscellaneous properties to the display.
        // Adds the global user property array to the display.
        $page->assign_by_ref('x', $user);
        $page->assign_by_ref('user', $login);
        $page->assign('logged', has_user_right('private', $view));
        $page->assign('view', $view);

        $page->addJsLink('close_on_esc.js');
        if (isset($user['date'])) {
            header('Last-Modified: ' . date('r', strtotime($user['date'])));
        }
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
            ax_synchronize(S::user()->login(), S::v('uid'));
            $page->trigSuccess('Ton profil a été synchronisé avec celui du site polytechniciens.com');
        }

        // Build the page
        $page->addJsLink('ajax.js');
        $page->addJsLink('education.js');
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
                      . " la procédure de récupération de mot de passe si un jour tu le perdais.");
        }

       $page->setTitle('Mon Profil');
    }

    function handler_education_js(&$page)
    {
        header('Content-Type: text/javascript; charset=utf-8');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified:' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        $page->changeTpl('profile/education.js.tpl', NO_SKIN);
        require_once "education.func.inc.php";
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

    function handler_ajax_address(&$page, $id)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('profile/adresses.address.tpl', NO_SKIN);
        $page->assign('i', $id);
        $page->assign('address', array());
    }

    function handler_ajax_tel(&$page, $prefid, $prefname, $telid)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('profile/phone.tpl', NO_SKIN);
        $page->assign('prefid', $prefid);
        $page->assign('prefname', $prefname);
        $page->assign('telid', $telid);
        $page->assign('tel', array());
    }

    function handler_ajax_edu(&$page, $eduid, $class)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('profile/general.edu.tpl', NO_SKIN);
        $res = XDB::iterator("SELECT  id, field
                                FROM  profile_education_field_enum
                            ORDER BY  field");
        $page->assign('edu_fields', $res->fetchAllAssoc());
        $page->assign('eduid', $eduid);
        $page->assign('class', $class);
        require_once "education.func.inc.php";
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
        $res = XDB::query("SELECT  id, name AS label
                             FROM  profile_job_sector_enum");
        $page->assign('sectors', $res->fetchAllAssoc());
        $res = XDB::query("SELECT  id, fonction_fr, FIND_IN_SET('titre', flags) AS title
                             FROM  fonctions_def
                         ORDER BY  id");
        $page->assign('fonctions', $res->fetchAllAssoc());
        require_once "emails.combobox.inc.php";
        fill_email_combobox($page);
    }

    function handler_ajax_sector(&$page, $id, $jobid, $jobpref, $sect, $ssect = -1)
    {
        header('Content-Type: text/html; charset=utf-8');
        $res = XDB::iterator("SELECT  id, name, FIND_IN_SET('optgroup', flags) AS optgroup
                                FROM  profile_job_subsector_enum
                               WHERE  sectorid = {?}", $sect);
        $page->changeTpl('profile/jobs.sector.tpl', NO_SKIN);
        $page->assign('id', $id);
        $page->assign('subSectors', $res);
        $page->assign('sel', $ssect);
        if ($id != -1) {
            $page->assign('change', 1);
            $page->assign('jobid', $jobid);
            $page->assign('jobpref', $jobpref);
        }
    }

    function handler_ajax_sub_sector(&$page, $id, $ssect, $sssect = -1)
    {
        header('Content-Type: text/html; charset=utf-8');
        $res = XDB::iterator("SELECT  id, name
                                FROM  profile_job_subsubsector_enum
                               WHERE  subsectorid = {?}", $ssect);
        $page->changeTpl('profile/jobs.sub_sector.tpl', NO_SKIN);
        $page->assign('id', $id);
        $page->assign('subSubSectors', $res);
        $page->assign('sel', $sssect);
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

    function handler_ajax_searchname(&$page, $id)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('profile/general.searchname.tpl', NO_SKIN);
        $res = XDB::query("SELECT  id, name, FIND_IN_SET('public', flags) AS pub
                             FROM  profile_name_enum
                            WHERE  NOT FIND_IN_SET('not_displayed', flags)
                                   AND NOT FIND_IN_SET('always_displayed', flags)");
        $page->assign('sn_type_list', $res->fetchAllAssoc());
        $page->assign('i', $id);
    }

    function handler_ajax_buildnames(&$page, $data)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('profile/general.buildnames.tpl', NO_SKIN);
        require_once 'name.func.inc.php';
        $page->assign('names', build_javascript_names($data));
    }

    function handler_p_orange(&$page)
    {
        $page->changeTpl('profile/orange.tpl');

        require_once 'validations.inc.php';

        $res = XDB::query("SELECT  e.entry_year, e.grad_year, d.promo, FIND_IN_SET('femme', u.flags) AS sexe
                             FROM  auth_user_md5     AS u
                       INNER JOIN  profile_display   AS d ON (d.pid = u.user_id)
                       INNER JOIN  profile_education AS e ON (e.uid = u.user_id AND FIND_IN_SET('primary', e.flags))
                            WHERE  u.user_id = {?}", S::v('uid'));

        list($promo, $promo_sortie_old, $promo_display, $sexe) = $res->fetchOneRow();
        $page->assign('promo_sortie_old', $promo_sortie_old);
        $page->assign('promo', $promo);
        $page->assign('promo_display', $promo_display);
        $page->assign('sexe', $sexe);

        if (!Env::has('promo_sortie')) {
            return;
        } else {
            S::assert_xsrf_token();
        }

        $promo_sortie = Env::i('promo_sortie');

        if ($promo_sortie < 1000 || $promo_sortie > 9999) {
            $page->trigError('L\'année de sortie doit être un nombre de quatre chiffres.');
        }
        elseif ($promo_sortie < $promo + 3) {
            $page->trigError('Trop tôt !');
        }
        elseif ($promo_sortie == $promo_sortie_old) {
            $page->trigWarning('Tu appartiens déjà à la promotion correspondante à cette année de sortie.');
        }
        elseif ($promo_sortie == $promo + 3) {
            XDB::execute("UPDATE  profile_education
                             SET  grad_year = {?}
                           WHERE  uid = {?} AND FIND_IN_SET('primary', flags)", $promo_sortie, S::v('uid'));
                $page->trigSuccess('Ton statut "orange" a été supprimé.');
                $page->assign('promo_sortie_old', $promo_sortie);
        }
        else {
            $page->assign('promo_sortie', $promo_sortie);

            if (Env::has('submit')) {
                $myorange = new OrangeReq(S::user(), $promo_sortie);
                $myorange->submit();
                $page->assign('myorange', $myorange);
            }
        }
    }

    function handler_referent(&$page, $x = null)
    {
        require_once 'user.func.inc.php';
        $page->changeTpl('profile/fiche_referent.tpl', SIMPLE);

        $user = User::get($x);
        if ($user == null) {
            return PL_NOT_FOUND;
        }

        $res = XDB::query("SELECT cv FROM auth_user_md5 WHERE user_id = {?}", $user->id());
        $cv = $res->fetchOneCell();

        $page->assign_by_ref('user', $user);
        $page->assign('cv', MiniWiki::WikiToHTML($cv, true));
        $page->assign('adr_pro', get_user_details_pro($user->id()));

        /////  recuperations infos referent

        //expertise
        $res = XDB::query("SELECT expertise FROM profile_mentor WHERE uid = {?}", $user->id());
        $page->assign('expertise', $res->fetchOneCell());

        // Sectors
        $sectors = $subSectors = Array();
        $res = XDB::iterRow(
                "SELECT  s.name AS label, ss.name AS label
                   FROM  profile_mentor_sector      AS m
              LEFT JOIN  profile_job_sector_enum    AS s  ON(m.sectorid = s.id)
              LEFT JOIN  profile_job_subsector_enum AS ss ON(m.sectorid = ss.sectorid AND m.subsectorid = ss.id)
                  WHERE  uid = {?}", $user->id());
        while (list($sector, $subSector) = $res->next()) {
            $sectors[]    = $sector;
            $subSectors[] = $subSector;
        }
        $page->assign_by_ref('sectors', $sectors);
        $page->assign_by_ref('subSectors', $subSectors);

        // Countries.
        $res = XDB::query(
                "SELECT  gc.countryFR
                   FROM  profile_mentor_country AS m
              LEFT JOIN  geoloc_countries       AS gc ON (m.country = gc.iso_3166_1_a2)
                  WHERE  uid = {?}", $user->id());
        $page->assign('pays', $res->fetchColumn());

        $page->addJsLink('close_on_esc.js');
    }

    function handler_ref_search(&$page, $action = null, $subaction = null)
    {
        $wp = new PlWikiPage('Docs.Emploi');
        $wp->buildCache();

        $page->setTitle('Conseil Pro');

        // Retrieval of sector names
        $res = XDB::iterRow("SELECT  id, name AS label
                               FROM  profile_job_sector_enum");
        $sectors[''] = '';
        while (list($tmp_id, $tmp_label) = $res->next()) {
            $sectors[$tmp_id] = $tmp_label;
        }
        $page->assign_by_ref('sectors', $sectors);

        // nb de mentors
        $res = XDB::query("SELECT count(*) FROM profile_mentor");
        $page->assign('mentors_number', $res->fetchOneCell());

        // On vient d'un formulaire
        $where              = array();
        $pays_sel           = XDB::escape(Env::v('pays_sel'));
        $sectorSelection    = XDB::escape(Env::v('sector'));
        $subSectorSelection = XDB::escape(Env::v('subSector'));
        $expertise_champ    = XDB::escape(Env::v('expertise'));

        if ($pays_sel != "''") {
            $where[] = "mp.country = $pays_sel";
        }
        if ($sectorSelection != "''") {
            $where[] = "ms.sectorid = " . $sectorSelection;
            if ($selectedSubSector != "''") {
                $where[] = "ms.subsectorid = " . $subSectorSelection;
            }
        }
        if ($expertise_champ != "''") {
            $where[] = "MATCH(m.expertise) AGAINST($expertise_champ)";
        }

        if ($where) {
            $where = join(' AND ', $where);

            $set = new UserSet("INNER JOIN  profile_mentor          AS m  ON (m.uid = u.user_id)
                                 LEFT JOIN  profile_mentor_country  AS mp ON (mp.uid = m.uid)
                                 LEFT JOIN  profile_mentor_sector   AS ms ON (ms.uid = m.uid)",
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
        $page->assign('onchange', 'setSSectors()');
        $page->assign('id', 'ssect_field');
        $page->assign('name', 'subSector');
        $it = XDB::iterator("SELECT  id, name AS field
                               FROM  profile_job_subsector_enum
                              WHERE  sectorid = {?}", $sect);
        $page->assign('list', $it);
    }

    function handler_ref_country(&$page, $sect, $ssect = '')
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('include/field.select.tpl', NO_SKIN);
        $page->assign('name', 'pays_sel');
        $where = ($ssect ? ' AND ms.subsectorid = {?}' : '');
        $it = XDB::iterator("SELECT  gc.iso_3166_1_a2 AS id, gc.countryFR AS field
                               FROM  geoloc_countries       AS gc
                         INNER JOIN  profile_mentor_country AS mp ON (mp.country = gc.iso_3166_1_a2)
                         INNER JOIN  profile_mentor_sector  AS ms ON (ms.uid = mp.uid)
                              WHERE  ms.sectorid = {?} " . $where . "
                           GROUP BY  iso_3166_1_a2
                           ORDER BY  countryFR", $sect, $ssect);
        $page->assign('list', $it);
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

        $vcard = new VCard();
        $vcard->addUser($x);
        $vcard->show();
    }

    function handler_admin_trombino(&$page, $login = null, $action = null) {
        $page->changeTpl('profile/admin_trombino.tpl');
        $page->setTitle('Administration - Trombino');

        if (!$login || !($user = User::get($login))) {
            return PL_NOT_FOUND;
        } else {
            $page->assign_by_ref('user', $user);
        }

        switch ($action) {
            case "original":
                header("Content-type: image/jpeg");
        	readfile("/home/web/trombino/photos" . $user->promo() . "/" . $user->login() . ".jpg");
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
                        $user->id(), $mimetype, $data, $x, $y);
            	break;

            case "delete":
                S::assert_xsrf_token();

                XDB::execute('DELETE FROM photo WHERE uid = {?}', $user->id());
                break;
        }
    }
    function handler_admin_names(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Types de noms');
        $page->assign('title', 'Gestion des types de noms');
        $table_editor = new PLTableEditor('admin/names', 'profile_name_enum', 'id', true);
        $table_editor->describe('name', 'Nom', true);
        $table_editor->describe('explanations', 'Explications', true);
        $table_editor->describe('type', 'Type', true);
        $table_editor->describe('flags', 'Flags', true);
        $table_editor->describe('score', 'Score', true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_binets(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Binets');
        $page->assign('title', 'Gestion des binets');
        $table_editor = new PLTableEditor('admin/binets', 'binets_def', 'id');
        $table_editor->add_join_table('binets_ins','binet_id',true);
        $table_editor->describe('text','intitulé',true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_education(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Formations');
        $page->assign('title', 'Gestion des formations');
        $table_editor = new PLTableEditor('admin/education', 'profile_education_enum', 'id');
        $table_editor->add_join_table('profile_education', 'eduid', true);
        $table_editor->add_join_table('profile_education_degree', 'eduid', true);
        $table_editor->describe('name', 'intitulé', true);
        $table_editor->describe('url', 'site web', false);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_education_field(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Domaines de formation');
        $page->assign('title', 'Gestion des domaines de formation');
        $table_editor = new PLTableEditor('admin/education_field', 'profile_education_field_enum', 'id', true);
        $table_editor->add_join_table('profile_education', 'fieldid', true);
        $table_editor->describe('field', 'domaine', true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_education_degree(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Niveau de formation');
        $page->assign('title', 'Gestion des niveau de formation');
        $table_editor = new PLTableEditor('admin/education_degree', 'profile_education_degree_enum', 'id', true);
        $table_editor->add_join_table('profile_education_degree', 'degreeid', true);
        $table_editor->add_join_table('profile_education', 'degreeid', true);
        $table_editor->describe('degree', 'niveau', true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_education_degree_set(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Correspondances formations - niveau de formation');
        $page->assign('title', 'Gestion des correspondances formations - niveau de formation');
        $table_editor = new PLTableEditor('admin/education_degree_set', 'profile_education_degree', 'eduid', true);
        $table_editor->describe('eduid', 'formation', true);
        $table_editor->describe('degreeid', 'niveau', true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_sections(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Sections');
        $page->assign('title', 'Gestion des sections');
        $table_editor = new PLTableEditor('admin/sections','sections','id');
        $table_editor->describe('text','intitulé',true);
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
    function handler_admin_networking(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title', 'Polytechnique.org - Administration - Networking');
        $page->assign('title', 'Gestion des types de networking');
        $table_editor = new PLTableEditor('admin/networking', 'profile_networking_enum', 'network_type');
        $table_editor->describe('name', 'intitulé', true);
        $table_editor->describe('icon', 'nom de l\'icône', false);
        $table_editor->describe('filter', 'filtre', true);
        $table_editor->describe('link', 'lien web', true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_corps_enum(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Corps');
        $page->assign('title', 'Gestion des Corps');
        $table_editor = new PLTableEditor('admin/corps_enum', 'profile_corps_enum', 'id');
        $table_editor->describe('name', 'intitulé', true);
        $table_editor->describe('abbreviation', 'abbréviation', true);
        $table_editor->describe('still_exists', 'existe encore ?', true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_corps_rank(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Grade dans les Corps');
        $page->assign('title', 'Gestion des grade dans les Corps');
        $table_editor = new PLTableEditor('admin/corps_rank', 'profile_corps_rank_enum', 'id');
        $table_editor->describe('name', 'intitulé', true);
        $table_editor->describe('abbreviation', 'abbréviation', true);
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
