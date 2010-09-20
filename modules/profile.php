<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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
            'profile/ajax/alternates'    => $this->make_hook('ajax_alternates',            AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/skill'         => $this->make_hook('ajax_skill',                 AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/searchname'    => $this->make_hook('ajax_searchname',            AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/buildnames'    => $this->make_hook('ajax_buildnames',            AUTH_COOKIE, 'user', NO_AUTH),
            'profile/ajax/tree/jobterms' => $this->make_hook('ajax_tree_job_terms',        AUTH_COOKIE, 'user', NO_AUTH),
            'profile/jobterms'           => $this->make_hook('jobterms',                   AUTH_COOKIE, 'user', NO_AUTH),
            'javascript/education.js'    => $this->make_hook('education_js',               AUTH_COOKIE),
            'javascript/grades.js'       => $this->make_hook('grades_js',                  AUTH_COOKIE),
            'profile/medal'              => $this->make_hook('medal',                      AUTH_PUBLIC),
            'profile/name_info'          => $this->make_hook('name_info',                  AUTH_PUBLIC),

            'referent'                   => $this->make_hook('referent',                   AUTH_COOKIE),
            'referent/ssect'             => $this->make_hook('ref_sect',                   AUTH_COOKIE, 'user', NO_AUTH),
            'referent/country'           => $this->make_hook('ref_country',                AUTH_COOKIE, 'user', NO_AUTH),
            'referent/autocomplete'      => $this->make_hook('ref_autocomplete',           AUTH_COOKIE, 'user', NO_AUTH),

            'groupes-x'                  => $this->make_hook('xnet',                       AUTH_COOKIE),
            'groupes-x/logo'             => $this->make_hook('xnetlogo',                   AUTH_PUBLIC),

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
            'admin/sectors'              => $this->make_hook('admin_sectors',              AUTH_MDP, 'admin'),
            'admin/corps_enum'           => $this->make_hook('admin_corps_enum',           AUTH_MDP, 'admin'),
            'admin/corps_rank'           => $this->make_hook('admin_corps_rank',           AUTH_MDP, 'admin'),
            'admin/names'                => $this->make_hook('admin_names',                AUTH_MDP, 'admin'),
        );
    }

    /* Function needed for compatibility reasons.
     * TODO: removes calls to fiche.php?user=blah.machin.2083 and then removes this.
     */
    function handler_fiche(&$page)
    {
        return $this->handler_profile($page, Env::v('user'));
    }

    function handler_photo(&$page, $x = null, $req = null)
    {
        if (!$x || !($profile = Profile::get($x))) {
            return PL_NOT_FOUND;
        }

        // Retrieve the photo and its mime type.
        if ($req && S::logged()) {
            $myphoto = PhotoReq::get_request($profile->id());
            $photo = PlImage::fromData($myphoto->data, $myphoto->mimetype);
        } else {
            $photo = $profile->getPhoto(true, true);
        }

        // Display the photo, or a default one when not available.
        $photo->send();
    }

    function handler_medal(&$page, $mid)
    {
        $thumb = ($mid == 'thumb');
        $mid = $thumb ? @func_get_arg(2) : $mid;

        $res = XDB::query("SELECT  img
                             FROM  profile_medal_enum
                            WHERE  id = {?}",
                          $mid);
        $img  = $thumb ?
            dirname(__FILE__).'/../htdocs/images/medals/thumb/' . $res->fetchOneCell() :
            dirname(__FILE__).'/../htdocs/images/medals/' . $res->fetchOneCell();
        pl_cached_content_headers(mime_content_type($img));
        echo file_get_contents($img);
        exit;
    }

    function handler_name_info(&$page)
    {
        pl_content_headers("text/html");
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
                            WHERE  nwid = {?}",
                          $mid);
        $img  = dirname(__FILE__) . '/../htdocs/images/networking/' . $res->fetchOneCell();
        pl_cached_content_headers(mime_content_type($img));
        echo file_get_contents($img);
        exit;
    }

    /** Tries to return the correct user from given hrpid
     * Will redirect to $returnurl$hrpid if $hrpid was empty
     */
    private function findProfile($returnurl, $hrpid = null)
    {
        if (is_null($hrpid)) {
            $user = S::user();
            if (!$user->hasProfile()) {
                return PL_NOT_FOUND;
            } else {
                pl_redirect($returnurl . $user->profile()->hrid());
            }
        } else {
            $profile = Profile::get($hrpid);
            if (!$profile) {
                return PL_NOT_FOUND;
            } else if (!S::user()->canEdit($profile) && Platal::notAllowed()) {
                return PL_FORBIDDEN;
            }
        }
        return $profile;
    }

    function handler_photo_change(&$page, $hrpid = null)
    {
        global $globals;
        $profile = $this->findProfile('photo/change/', $hrpid);
        if (! ($profile instanceof Profile) && ($profile == PL_NOT_FOUND || $profile == PL_FORBIDDEN)) {
            return $profile;
        }

        $page->changeTpl('profile/trombino.tpl');
        $page->assign('hrpid', $profile->hrid());

        $trombi_x = '/home/web/trombino/photos' . $profile->promo() . '/' . $profile->hrid() . '.jpg';
        if (Env::has('upload')) {
            S::assert_xsrf_token();

            $upload = new PlUpload($profile->hrid(), 'photo');
            if (!$upload->upload($_FILES['userfile']) && !$upload->download(Env::v('photo'))) {
                $page->trigError('Une erreur est survenue lors du téléchargement du fichier');
            } else {
                $myphoto = new PhotoReq(S::user(), $profile, $upload);
                if ($myphoto->isValid()) {
                    $myphoto->submit();
                }
            }
        } elseif (Env::has('trombi')) {
            S::assert_xsrf_token();

            $upload = new PlUpload($profile->hrid(), 'photo');
            if ($upload->copyFrom($trombi_x)) {
                $myphoto = new PhotoReq(S::user(), $profile, $upload);
                if ($myphoto->isValid()) {
                    $myphoto->commit();
                    $myphoto->clean();
                }
            }
        } elseif (Env::v('suppr')) {
            S::assert_xsrf_token();

            XDB::execute('DELETE FROM  profile_photos
                                WHERE  pid = {?}',
                         $profile->id());
            XDB::execute("DELETE FROM  requests
                                WHERE  pid = {?} AND type = 'photo'",
                         $profile->id());
            $globals->updateNbValid();
            $page->trigSuccess("Ta photo a bien été supprimée. Elle ne sera plus visible sur le site dans au plus une heure.");
        } elseif (Env::v('cancel')) {
            S::assert_xsrf_token();

            $sql = XDB::query("DELETE FROM  requests
                                     WHERE  pid = {?} AND type = 'photo'",
                              $profile->id());
            $globals->updateNbValid();
        }

        $sql = XDB::query("SELECT  COUNT(*)
                             FROM  requests
                            WHERE  pid = {?} AND type = 'photo'",
                          $profile->id());
        $page->assign('submited', $sql->fetchOneCell());
        $page->assign('has_trombi_x', file_exists($trombi_x));
    }

    function handler_profile(&$page, $id = null)
    {
        // Checks if the identifier corresponds to an actual profile. Numeric
        // identifiers canonly be user by logged users.
        if (is_null($id)) {
            return PL_NOT_FOUND;
        }
        $pid = (!is_numeric($id) || S::admin()) ? Profile::getPID($id) : null;
        if (is_null($pid)) {
            if (S::logged()) {
                $page->trigError($id . " inconnu dans l'annuaire.");
            }
            return PL_NOT_FOUND;
        }

        // Now that we know this is an existing profile, we can switch to the
        // appropriate template.
        $page->changeTpl('profile/profile.tpl', SIMPLE);

        // Determines the access level at which the profile will be displayed.
        if (!S::logged() || !S::user()->checkPerms('directory_ax') || Env::v('view') == 'public') {
            $view = 'public';
        } else if (!S::user()->checkPerms('directory_private') || Env::v('view') == 'ax') {
            $view = 'ax';
        } else {
            $view = 'private';
        }

        // Display pending picture
        if (S::logged() && Env::v('modif') == 'new') {
            $page->assign('with_pending_pic', true);
        }

        // Fetches profile's and profile's owner information and redirects to
        // marketing if the owner has not subscribed and the requirer has logged in.
        $profile = Profile::get($pid, Profile::FETCH_ALL, $view);
        $owner = $profile->owner();
        if (S::logged() && !is_null($owner) && $owner->state == 'pending') {
            pl_redirect('marketing/public/' . $profile->hrid());
        }

        // Profile view are logged.
        if (S::logged()) {
            S::logger()->log('view_profile', $profile->hrid());
        }

        // Sets the title of the html page.
        $page->setTitle($profile->fullName());

        // Determines and displays the virtual alias.
        if (!is_null($owner) && $profile->alias_pub == 'public') {
            $page->assign('virtualalias', $owner->emailAlias());
        }

        $page->assign_by_ref('profile', $profile);
        $page->assign_by_ref('owner', $owner);
        $page->assign('view', $view);
        $page->assign('logged', S::logged());

        $page->addJsLink('close_on_esc.js');
        header('Last-Modified: ' . date('r', strtotime($profile->last_change)));
    }

    function handler_ax(&$page, $user = null)
    {
        $user = Profile::get($user);
        if (!$user) {
            return PL_NOT_FOUND;
        }
        if (!$user->ax_id) {
            $page->kill("Le matricule AX de {$user->hrid()} est inconnu");
        }
        http_redirect("http://www.polytechniciens.com/?page=AX_FICHE_ANCIEN&ancc_id=" . $user->ax_id);
    }

    function handler_p_edit(&$page, $hrpid = null, $opened_tab = null, $mode = null, $success = null)
    {
        global $globals;

        $profile = $this->findProfile('profile/edit/', $hrpid);
        if (! ($profile instanceof Profile) && ($profile == PL_NOT_FOUND || $profile == PL_FORBIDDEN)) {
            return $profile;
        }

        // Build the page
        $page->addJsLink('ajax.js');
        $page->addJsLink('education.js', false); /* dynamic content */
        $page->addJsLink('grades.js', false);    /* dynamic content */
        $page->addJsLink('profile.js');
        $page->addJsLink('jquery.autocomplete.js');
        $wiz = new PlWizard('Profil', PlPage::getCoreTpl('plwizard.tpl'), true, true, false);
        $wiz->addUserData('profile', $profile);
        $wiz->addUserData('owner', $profile->owner());
        $this->load('page.inc.php');
        $wiz->addPage('ProfilePageGeneral', 'Général', 'general');
        $wiz->addPage('ProfilePageAddresses', 'Adresses personnelles', 'adresses');
        $wiz->addPage('ProfilePageJobs', 'Informations professionnelles', 'emploi');
        if (S::user()->checkPerms(User::PERM_DIRECTORY_PRIVATE)) {
            $wiz->addPage('ProfilePageGroups', 'Groupes X - Binets', 'poly');
        }
        $wiz->addPage('ProfilePageDecos', 'Décorations - Medailles', 'deco');
        if (S::user()->checkPerms(User::PERM_DIRECTORY_PRIVATE)) {
            $wiz->addPage('ProfilePageSkills', 'Compétences diverses', 'skill');
            $wiz->addPage('ProfilePageMentor', 'Mentoring', 'mentor');
        }
        $wiz->apply($page, 'profile/edit/' . $profile->hrid(), $opened_tab, $mode);

        if (!$profile->birthdate) {
            $page->trigWarning("Ta date de naissance n'est pas renseignée, ce qui t'empêcheras de réaliser"
                      . " la procédure de récupération de mot de passe si un jour tu le perdais.");
        }

       $page->setTitle('Mon Profil');
       if (isset($success) && $success) {
           $page->trigSuccess('Ton profil a bien été mis à jour.');
       }
    }

    function handler_education_js(&$page)
    {
        pl_cached_content_headers("text/javascript", "utf-8");
        $page->changeTpl('profile/education.js.tpl', NO_SKIN);
        require_once 'education.func.inc.php';
    }

    function handler_grades_js(&$page)
    {
        pl_cached_content_headers("text/javascript", "utf-8");
        $page->changeTpl('profile/grades.js.tpl', NO_SKIN);
        $res    = XDB::iterator("SELECT  *
                                   FROM  profile_medal_grade_enum
                               ORDER BY  mid, pos");
        $grades = array();
        while ($tmp = $res->next()) {
            $grades[$tmp['mid']][] = $tmp;
        }
        $page->assign('grades', $grades);

        $res    = XDB::iterator("SELECT  *, FIND_IN_SET('validation', flags) AS validate
                                   FROM  profile_medal_enum
                               ORDER BY  type, text");
        $mlist  = array();
        while ($tmp = $res->next()) {
            $mlist[$tmp['type']][] = $tmp;
        }
        $page->assign('medal_list', $mlist);
    }

    function handler_ajax_address(&$page, $id)
    {
        pl_content_headers("text/html");
        $page->changeTpl('profile/adresses.address.tpl', NO_SKIN);
        $page->assign('i', $id);
        $page->assign('address', array());
    }

    function handler_ajax_tel(&$page, $prefid, $prefname, $telid)
    {
        pl_content_headers("text/html");
        $page->changeTpl('profile/phone.tpl', NO_SKIN);
        $page->assign('prefid', $prefid);
        $page->assign('prefname', $prefname);
        $page->assign('telid', $telid);
        $phone = new Phone();
        $page->assign('tel', $phone->toFormArray());
    }

    function handler_ajax_edu(&$page, $eduid, $class)
    {
        pl_content_headers("text/html");
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
        pl_content_headers("text/html");
        $page->changeTpl('profile/deco.medal.tpl', NO_SKIN);
        $page->assign('id', $id);
        $page->assign('medal', array('valid' => 0, 'grade' => 0));
    }

    function handler_ajax_job(&$page, $id)
    {
        pl_content_headers("text/html");
        $page->changeTpl('profile/jobs.job.tpl', NO_SKIN);
        $page->assign('i', $id);
        $page->assign('job', array());
        $page->assign('new', true);
        $res = XDB::query("SELECT  id, name AS label
                             FROM  profile_job_sector_enum");
        $page->assign('sectors', $res->fetchAllAssoc());
        require_once "emails.combobox.inc.php";
        fill_email_combobox($page);
    }

    function handler_ajax_sector(&$page, $id, $jobid, $jobpref, $sect, $ssect = -1)
    {
        pl_content_headers("text/html");
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
        pl_content_headers("text/html");
        $res = XDB::iterator("SELECT  id, name
                                FROM  profile_job_subsubsector_enum
                               WHERE  subsectorid = {?}", $ssect);
        $page->changeTpl('profile/jobs.sub_sector.tpl', NO_SKIN);
        $page->assign('id', $id);
        $page->assign('subSubSectors', $res);
        $page->assign('sel', $sssect);
    }

    /**
     * Page for url "profile/ajax/tree/jobterms". Display a JSon page containing
     * the sub-branches of a branch in the job terms tree.
     * @param $page the Platal page
     * @param $filter filter helps to display only jobterms that are contained in jobs or in mentors
     *
     * @param Env::i('jtid') job term id of the parent branch, if none trunk will be used
     * @param Env::v('attrfunc') the name of a javascript function that will be called when a branch
     * is chosen
     * @param Env::v('treeid') tree id that will be given as first argument of attrfunc function
     * the second argument will be the chosen job term id and the third one the chosen job full name.
     */
    function handler_ajax_tree_job_terms(&$page, $filter = JobTerms::ALL)
    {
        JobTerms::ajaxGetBranch(&$page, $filter);
    }

    function handler_ajax_alternates(&$page, $id, $sssect)
    {
        pl_content_headers("text/html");
        $res = XDB::iterator('SELECT  name
                                FROM  profile_job_alternates
                               WHERE  subsubsectorid = {?}
                            ORDER BY  id',
                             $sssect);
        $page->changeTpl('profile/jobs.alternates.tpl', NO_SKIN);
        $alternates = '';
        if ($res->total() > 0) {
            $alternate  = $res->next();
            $alternates = $alternate['name'];
            while ($alternate  = $res->next()) {
                $alternates .= ', ' . $alternate['name'];
            }
        }
        $page->assign('alternates', $alternates);
    }

    function handler_ajax_skill(&$page, $cat, $id)
    {
        pl_content_headers("text/html");
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

    function handler_ajax_searchname(&$page, $id, $isFemale)
    {
        pl_content_headers("text/html");
        $page->changeTpl('profile/general.searchname.tpl', NO_SKIN);
        $res = XDB::query("SELECT  id, name, FIND_IN_SET('public', flags) AS pub
                             FROM  profile_name_enum
                            WHERE  NOT FIND_IN_SET('not_displayed', flags)
                                   AND NOT FIND_IN_SET('always_displayed', flags)");
        $page->assign('sn_type_list', $res->fetchAllAssoc());
        $page->assign('isFemale', $isFemale);
        $page->assign('i', $id);
    }

    function handler_ajax_buildnames(&$page, $data, $isFemale)
    {
        pl_content_headers("text/html");
        $page->changeTpl('profile/general.buildnames.tpl', NO_SKIN);
        require_once 'name.func.inc.php';
        $page->assign('names', build_javascript_names($data, $isFemale));
    }

    function handler_referent(&$page, $pf)
    {
        $page->changeTpl('profile/fiche_referent.tpl', SIMPLE);

        $pf = Profile::get($pf);
        if (!$pf) {
            return PL_NOT_FOUND;
        }

        $page->assign_by_ref('profile', $pf);

        /////  recuperations infos referent

        // Sectors
        $sectors = $subSectors = Array();
        $res = XDB::iterRow(
                "SELECT  s.name AS label, ss.name AS label
                   FROM  profile_mentor_sector      AS m
              LEFT JOIN  profile_job_sector_enum    AS s  ON(m.sectorid = s.id)
              LEFT JOIN  profile_job_subsector_enum AS ss ON(m.sectorid = ss.sectorid AND m.subsectorid = ss.id)
                  WHERE  pid = {?}", $pf->id());
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
                  WHERE  pid = {?}", $pf->id());
        $page->assign('pays', $res->fetchColumn());

        $page->addJsLink('close_on_esc.js');
    }

    function handler_ref_sect(&$page, $sect)
    {
        pl_content_headers("text/html");
        $page->changeTpl('include/field.select.tpl', NO_SKIN);
        $page->assign('onchange', 'setSSectors()');
        $page->assign('id', 'ssect_field');
        $page->assign('name', 'subSector');
        $it = XDB::iterator("SELECT  pjsse.id, pjsse.name AS field
                               FROM  profile_job_subsector_enum AS pjsse
                         INNER JOIN  profile_mentor_sector AS pms ON (pms.sectorid = pjsse.sectorid AND pms.subsectorid = pjsse.id)
                              WHERE  pjsse.sectorid = {?}
                           GROUP BY  pjsse.id
                           ORDER BY  pjsse.name", $sect);
        $page->assign('list', $it);
    }

    function handler_ref_country(&$page, $sect, $ssect = '')
    {
        pl_content_headers("text/html");
        $page->changeTpl('include/field.select.tpl', NO_SKIN);
        $page->assign('name', 'pays_sel');
        $where = ($ssect ? ' AND ms.subsectorid = {?}' : '');
        $it = XDB::iterator("SELECT  gc.iso_3166_1_a2 AS id, gc.countryFR AS field
                               FROM  geoloc_countries       AS gc
                         INNER JOIN  profile_mentor_country AS mp ON (mp.country = gc.iso_3166_1_a2)
                         INNER JOIN  profile_mentor_sector  AS ms ON (ms.pid = mp.pid)
                              WHERE  ms.sectorid = {?} " . $where . "
                           GROUP BY  iso_3166_1_a2
                           ORDER BY  countryFR", $sect, $ssect);
        $page->assign('list', $it);
    }

    /**
     * Page for url "referent/autocomplete". Display an "autocomplete" page (plain/text with values
     * separated by "|" chars) for jobterms in referent (mentor) search.
     * @see handler_jobterms
     */
    function handler_ref_autocomplete(&$page)
    {
        $this->handler_jobterms(&$page, 'mentor');
    }

    /**
     * Page for url "profile/jobterms" (function also used for "referent/autocomplete" @see
     * handler_ref_autocomplete). Displays an "autocomplete" page (plain text with values
     * separated by "|" chars) for jobterms to add in profile.
     * @param $page the Platal page
     * @param $type set to 'mentor' to display the number of mentors for each term and order
     *  by descending number of mentors.
     *
     * @param Env::v('q') the text that has been typed and to complete automatically
     */
    function handler_jobterms(&$page, $type = 'nomentor')
    {
        pl_content_headers("text/plain");

        $q = Env::v('q').'%';
        $tokens = JobTerms::tokenize($q);
        if (count($tokens) == 0) {
            exit;
        }
        sort($tokens);
        $q_normalized = implode(' ', $tokens);

        // try to look in cached results
        $cache = XDB::query('SELECT  result
                               FROM  search_autocomplete
                              WHERE  name = {?} AND
                                     query = {?} AND
                                     generated > NOW() - INTERVAL 1 DAY',
                            $type, $q_normalized);
        if ($res = $cache->fetchOneCell()) {
            echo $res;
            die();
        }

        $joins = JobTerms::token_join_query($tokens, 'e');
        if ($type == 'mentor') {
            $count = ', COUNT(DISTINCT pid) AS nb';
            $countjoin = ' INNER JOIN  profile_job_term_relation AS r ON(r.jtid_1 = e.jtid) INNER JOIN  profile_mentor_term AS m ON(r.jtid_2 = m.jtid)';
            $countorder = 'nb DESC, ';
        } else {
            $count = $countjoin = $countorder = '';
        }
        $list = XDB::iterator('SELECT  e.jtid AS id, e.full_name AS field'.$count.'
                                 FROM  profile_job_term_enum AS e '.$joins.$countjoin.'
                             GROUP BY  e.jtid
                             ORDER BY  '.$countorder.'field
                                LIMIT  11');
        $nbResults = 0;
        $res = '';
        while ($result = $list->next()) {
            $nbResults++;
            if ($nbResults == 11) {
                $res .= $q."|-1\n";
            } else {
                $res .= $result['field'].'|';
                if ($count) {
                    $res .= $result['nb'].'|';
                }
                $res .= $result['id'];
            }
            $res .= "\n";
        }
        XDB::query('REPLACE INTO  search_autocomplete
                          VALUES  ({?}, {?}, {?}, NOW())',
                    $type, $q_normalized, $res);
        echo $res;
        exit();
    }

    function handler_xnet(&$page)
    {
        $page->changeTpl('profile/groupesx.tpl');
        $page->setTitle('Promo, Groupes X, Binets');

        $req = XDB::query('
            SELECT m.asso_id, a.nom, diminutif, a.logo IS NOT NULL AS has_logo,
                   COUNT(e.eid) AS events, mail_domain AS lists
              FROM group_members AS m
        INNER JOIN groups AS a ON(m.asso_id = a.id)
         LEFT JOIN group_events AS e ON(e.asso_id = m.asso_id AND e.archive = 0)
             WHERE uid = {?} GROUP BY m.asso_id ORDER BY a.nom', S::i('uid'));
        $page->assign('assos', $req->fetchAllAssoc());
    }

    function handler_xnetlogo(&$page, $id)
    {
        if (is_null($id)) {
            return PL_NOT_FOUND;
        }

        $res = XDB::query('SELECT  logo, logo_mime
                             FROM  groups
                            WHERE  id = {?}', $id);
        list($logo, $logo_mime) = $res->fetchOneRow();

        if (!empty($logo)) {
            pl_cached_dynamic_content_headers($logo_mime);
            echo $logo;
        } else {
            pl_cached_dynamic_content_headers("image/jpeg");
            readfile(dirname(__FILE__) . '/../htdocs/images/dflt_carre.jpg');
        }

        exit;
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
        $vcard->addProfile(Profile::get($x));
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
                PlImage::fromFile("/home/web/trombino/photos" . $user->promo() . "/" . $user->login() . ".jpg", "image/jpeg")->send();
                exit;

            case "new":
                S::assert_xsrf_token();

                $data = file_get_contents($_FILES['userfile']['tmp_name']);
            	list($x, $y) = getimagesize($_FILES['userfile']['tmp_name']);
            	$mimetype = substr($_FILES['userfile']['type'], 6);
            	unlink($_FILES['userfile']['tmp_name']);
                XDB::execute(
                        "REPLACE INTO profile_photos SET pid={?}, attachmime = {?}, attach={?}, x={?}, y={?}",
                        $user->profile()->id(), $mimetype, $data, $x, $y);
            	break;

            case "delete":
                S::assert_xsrf_token();

                XDB::execute('DELETE FROM profile_photos WHERE pid = {?}', $user->profile()->id());
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
        $table_editor = new PLTableEditor('admin/binets', 'profile_binet_enum', 'id');
        $table_editor->add_join_table('profile_binets','binet_id',true);
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
        $table_editor = new PLTableEditor('admin/education_degree', 'profile_education_degree_enum', 'id');
        $table_editor->add_join_table('profile_education_degree', 'degreeid', true);
        $table_editor->add_join_table('profile_education', 'degreeid', true);
        $table_editor->describe('degree', 'niveau', true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_education_degree_set(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Correspondances formations - niveau de formation');
        $page->assign('title', 'Gestion des correspondances formations - niveau de formation');
        $table_editor = new PLTableEditor('admin/education_degree_set', 'profile_education_degree', 'eduid', true);
        $table_editor->describe('eduid', 'id formation', true);
        $table_editor->describe('degreeid', 'id niveau', true);

        // Adds fields to show the names of education
        $table_editor->add_option_table('profile_education_enum','profile_education_enum.id = eduid');
        $table_editor->add_option_field('profile_education_enum.name', 'edu_name', 'formation', null, 'degreeid');
        // Adds fields to show the names of degrees
        $table_editor->add_option_table('profile_education_degree_enum','profile_education_degree_enum.id = t.degreeid');
        $table_editor->add_option_field('profile_education_degree_enum.degree', 'degree_name', 'niveau');

        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_sections(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Sections');
        $page->assign('title', 'Gestion des sections');
        $table_editor = new PLTableEditor('admin/sections','profile_section_enum','id');
        $table_editor->describe('text','intitulé',true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_sectors(&$page, $action = 'list', $id = null) {
        $page->setTitle('Administration - Secteurs');
        $page->assign('title', 'Gestion des secteurs');
        $table_editor = new PLTableEditor('admin/sectors', 'profile_job_subsubsector_enum', 'id', true);
        $table_editor->describe('sectorid', 'id du secteur', false);
        $table_editor->describe('subsectorid', 'id du sous-secteur', false);
        $table_editor->describe('name', 'nom', true);
        $table_editor->describe('flags', 'affichage', true);
        $table_editor->apply($page, $action, $id);
    }
    function handler_admin_networking(&$page, $action = 'list', $id = null) {
        $page->assign('xorg_title', 'Polytechnique.org - Administration - Networking');
        $page->assign('title', 'Gestion des types de networking');
        $table_editor = new PLTableEditor('admin/networking', 'profile_networking_enum', 'nwid');
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
        $table_editor = new PLTableEditor('admin/medals','profile_medal_enum','id');
        $table_editor->describe('text', 'intitulé',  true);
        $table_editor->describe('img',  'nom de l\'image', false);
        $table_editor->describe('flags', 'valider', true);
        $table_editor->apply($page, $action, $id);
        if ($id && $action == 'edit') {
            $page->changeTpl('profile/admin_decos.tpl');

            $mid = $id;

            if (Post::v('act') == 'del') {
                XDB::execute('DELETE FROM  profile_medal_grade_enum
                                    WHERE  mid={?} AND gid={?}', $mid, Post::i('gid'));
            } else {
                foreach (Post::v('grades', array()) as $gid=>$text) {
                    if ($gid === 0) {
                        if (!empty($text)) {
                            $res = XDB::query('SELECT  MAX(gid)
                                                 FROM  profile_medal_grade_enum
                                                WHERE  mid = {?}', $mid);
                            $gid = $res->fetchOneCell() + 1;

                            XDB::execute('INSERT INTO  profile_medal_grade_enum (mid, gid, text, pos)
                                               VALUES  ({?}, {?}, {?}, {?})',
                                $mid, $gid, $text, $_POST['pos']['0']);
                        }
                    } else {
                        XDB::execute('UPDATE  profile_medal_grade_enum
                                         SET  pos={?}, text={?}
                                       WHERE  gid={?} AND mid={?}', $_POST['pos'][$gid], $text, $gid, $mid);
                    }
                }
            }
            $res = XDB::iterator('SELECT gid, text, pos FROM profile_medal_grade_enum WHERE mid={?} ORDER BY pos', $mid);
            $page->assign('grades', $res);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
