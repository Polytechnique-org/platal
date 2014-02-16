<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

class ListsModule extends PLModule
{
    function handlers()
    {
        return array(
            'lists'              => $this->make_hook('lists',     AUTH_PASSWD, 'user'),
            'lists/ajax'         => $this->make_hook('ajax',      AUTH_PASSWD, 'user', NO_AUTH),
            'lists/create'       => $this->make_hook('create',    AUTH_PASSWD, 'lists'),

            'lists/members'      => $this->make_hook('members',   AUTH_COOKIE, 'user'),
            'lists/csv'          => $this->make_hook('csv',       AUTH_COOKIE, 'user'),
            'lists/annu'         => $this->make_hook('annu',      AUTH_COOKIE, 'user'),
            'lists/archives'     => $this->make_hook('archives',  AUTH_COOKIE, 'user'),
            'lists/archives/rss' => $this->make_hook('rss',       AUTH_PUBLIC, 'user', NO_HTTPS),

            'lists/moderate'     => $this->make_hook('moderate',  AUTH_PASSWD, 'user'),
            'lists/admin'        => $this->make_hook('admin',     AUTH_PASSWD, 'user'),
            'lists/options'      => $this->make_hook('options',   AUTH_PASSWD, 'user'),
            'lists/delete'       => $this->make_hook('delete',    AUTH_PASSWD, 'user'),

            'lists/soptions'     => $this->make_hook('soptions',  AUTH_PASSWD, 'user'),
            'lists/check'        => $this->make_hook('check',     AUTH_PASSWD, 'user'),
            'admin/lists'        => $this->make_hook('admin_all', AUTH_PASSWD, 'admin'),
            'admin/aliases'      => $this->make_hook('aaliases',  AUTH_PASSWD, 'admin')
        );
    }

    protected function prepare_client($user = null)
    {
        if (is_null($user)) {
            $user = S::user();
        }

        $domain = $this->get_lists_domain();

        return new MMList($user, $domain);
    }

    protected function get_lists_domain()
    {
        global $globals;
        return $globals->mail->domain;
    }

    /** Prepare a MailingList from its mailbox
     */
    protected function prepare_list($mbox)
    {
        // Required: modules/xnetlists.php uses it too.
        Platal::load('lists', 'lists.inc.php');

        return new MailingList($mbox, $this->get_lists_domain());
    }

    /** Ensure the current user is an administrator of the group.
     */
    protected function is_group_admin($page)
    {
        $force_rights = false;
        if ($GLOBALS['IS_XNET_SITE']) {
            $perms = S::v('perms');
            if (is_object($perms) && $perms->hasFlag('groupadmin')) {
                $force_rights = true;
            }
        }
        $page->assign('group_admin', $force_rights);

        return $force_rights;
    }

    /** Ensure the current user owns the given MailingList.
     */
    protected function verify_list_owner($page, $mlist)
    {
        if (list(, , $owners) = $mlist->getMembers()) {
            if (!(in_array(S::user()->forlifeEmail(), $owners) || S::admin())) {
                $page->kill("La liste n'existe pas ou tu n'as pas le droit de l'administrer.");
            }
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de l'administrer.<br />"
                      . " Si tu penses qu'il s'agit d'une erreur, "
                      . "<a href='mailto:support@polytechnique.org'>contact le support</a>.");
        }
    }

    /** Fetch pending operations on a MailingList instance.
     */
    protected function get_pending_ops($mlist)
    {
        list($subs, $mails) = $mlist->getPendingOps();
        $res = XDB::query("SELECT  mid
                             FROM  email_list_moderate
                            WHERE  ml = {?} AND domain = {?}",
                          $mlist->mbox, $mlist->domain);
        $mids = $res->fetchColumn();
        foreach ($mails as $key => $mail) {
            if (in_array($mail['id'], $mids)) {
                unset($mails[$key]);
            }
        }
        return array($subs, $mails);
    }

    function handler_lists($page)
    {

        function filter_owner($list)
        {
            return $list['own'];
        }

        function filter_member($list)
        {
            return $list['sub'];
        }

        $page->changeTpl('lists/index.tpl');
        $page->setTitle('Listes de diffusion');


        if (Get::has('del')) {
            S::assert_xsrf_token();
            $mlist = $this->prepare_list(Get::v('del'));
            $mlist->unsubscribe();
            pl_redirect('lists');
        }
        if (Get::has('add')) {
            S::assert_xsrf_token();
            $mlist = $this->prepare_list(Get::v('add'));
            $mlist->subscribe();
            pl_redirect('lists');
        }
        if (Post::has('promo_add')) {
            S::assert_xsrf_token();

            $promo = Post::i('promo_add');
            if ($promo >= 1900 and $promo < 2100) {
                $mlist = MailingList::promo($promo);
                $mlist->subscribe();
            } else {
                $page->trigError("promo incorrecte, il faut une promo sur 4 chiffres.");
            }
        }

        $client = $this->prepare_client();
        if (!is_null($listes = $client->get_lists())) {
            $owner  = array_filter($listes, 'filter_owner');
            $listes = array_diff_key($listes, $owner);
            $member = array_filter($listes, 'filter_member');
            $listes = array_diff_key($listes, $member);
            foreach ($owner as $key => $liste) {
                $mlist = $this->prepare_list($liste['list']);
                list($subs, $mails) = $this->get_pending_ops($mlist);
                $owner[$key]['subscriptions'] = $subs;
                $owner[$key]['mails'] = $mails;
            }
            $page->register_modifier('hdc', 'list_header_decode');
            $page->assign_by_ref('owner',  $owner);
            $page->assign_by_ref('member', $member);
            $page->assign_by_ref('public', $listes);
        }
    }

    function handler_ajax($page, $list = null)
    {
        pl_content_headers("text/html");
        $page->changeTpl('lists/liste.inc.tpl', NO_SKIN);
        S::assert_xsrf_token();

        $mlist = $this->prepare_list($list);
        if (Get::has('unsubscribe')) {
            $mlist->unsubscribe();
        }
        if (Get::has('subscribe')) {
            $mlist->subscribe();
        }
        if (Get::has('sadd')) {
            $mlist->handleRequest(MailingList::REQ_SUBSCRIBE, Get::v('sadd'));
        }
        if (Get::has('mid')) {
            $this->moderate_mail($mlist, Get::i('mid'));
        }

        list($liste, $members, $owners) = $mlist->getMembers();
        if ($liste['own']) {
            list($subs, $mails) = $this->get_pending_ops($mlist);
            $liste['subscriptions'] = $subs;
            $liste['mails'] = $mails;
        }
        $page->register_modifier('hdc', 'list_header_decode');
        $page->assign_by_ref('liste', $liste);
    }

    function handler_create($page)
    {
        global $globals;

        $page->changeTpl('lists/create.tpl');

        $user_promo  = S::user()->profile()->yearPromo();
        $year        = date('Y');
        $month       = date('m');
        // scolar year starts in september
        $scolarmonth = ($year - $user_promo) * 12 + ($month - 8);
        $young_promo = $very_young_promo = 0;
        // binet are accessible only in april in the first year and until
        // march of the 5th year
        if ($scolarmonth >= 8 && $scolarmonth < 56) {
            $young_promo = 1;
        }
        // PSC aliases are accesible only between september and june of the second
        // year of scolarity
        if ($scolarmonth >= 12 && $scolarmonth < 22) {
            $very_young_promo = 1;
        }
        $page->assign('young_promo', $young_promo);
        $page->assign('very_young_promo', $very_young_promo);

        $owners  = preg_split("/[\s]+/", Post::v('owners'), -1, PREG_SPLIT_NO_EMPTY);
        $members = preg_split("/[\s]+/", Post::v('members'), -1, PREG_SPLIT_NO_EMPTY);

        // click on validate button 'add_owner_sub' or type <enter>
        if (Post::has('add_owner_sub') && Post::has('add_owner')) {
            // if we want to add an owner and then type <enter>, then both
            // add_owner_sub and add_owner are filled.
            $oforlifes = User::getBulkForlifeEmails(Post::v('add_owner'), true);
            $mforlifes = User::getBulkForlifeEmails(Post::v('add_member'), true);
            if (!is_null($oforlifes)) {
                $owners = array_merge($owners, $oforlifes);
            }
            // if we want to add a member and then type <enter>, then
            // add_owner_sub is filled, whereas add_owner is empty.
            if (!is_null($mforlifes)) {
                $members = array_merge($members, $mforlifes);
            }
        }

        // click on validate button 'add_member_sub'
        if (Post::has('add_member_sub') && Post::has('add_member')) {
            $forlifes = User::getBulkForlifeEmails(Post::v('add_member'), true);
            if (!is_null($forlifes)) {
                $members = array_merge($members, $forlifes);
            }
        }
        if (Post::has('add_member_sub') && isset($_FILES['add_member_file']) && $_FILES['add_member_file']['tmp_name']) {
            $upload =& PlUpload::get($_FILES['add_member_file'], S::user()->login(), 'list.addmember', true);
            if (!$upload) {
                $page->trigError('Une erreur s\'est produite lors du téléchargement du fichier');
            } else {
                $forlifes = User::getBulkForlifeEmails($upload->getContents(), true);
                if (!is_null($forlifes)) {
                    $members = array_merge($members, $forlifes);
                }
            }
        }

        ksort($owners);	
        $owners = array_unique($owners);
        ksort($members);
        $members = array_unique($members);

        $page->assign('owners', join("\n", $owners));
        $page->assign('members', join("\n", $members));

        if (!Post::has('submit')) {
            return;
        } else {
            S::assert_xsrf_token();
        }

        $asso = Post::t('asso');
        $list = strtolower(Post::t('liste'));

        if (empty($list)) {
            $page->trigError('Le champ «&nbsp;adresse souhaitée&nbsp;» est vide.');
        }
        if (!preg_match("/^[a-zA-Z0-9\-]*$/", $list)) {
            $page->trigError('Le nom de la liste ne doit contenir que des lettres non accentuées, chiffres et tirets.');
        }

        if (($asso == 'binet') || ($asso == 'alias')) {
            $promo = Post::i('promo');
            $domain = $promo . '.' . $globals->mail->domain;

            if (($promo < 1921) || ($promo > date('Y'))) {
                $page->trigError('La promotion est mal renseignée, elle doit être du type&nbsp;: 2004.');
            }

        } elseif ($asso == 'groupex') {
                $domain = XDB::fetchOneCell('SELECT  mail_domain
                                               FROM  groups
                                              WHERE  nom = {?}',
                                            Post::t('groupex_name'));

                if (!$domain) {
                    $page->trigError('Il n\'y a aucun groupe de ce nom sur Polytechnique.net.');
                }
        } else {
            $domain = $globals->mail->domain;
        }

        require_once 'emails.inc.php';
        if (list_exist($list, $domain)) {
            $page->trigError("L'«&nbsp;adresse souhaitée&nbsp;» est déjà prise.");
        }

        if (!Post::t('desc')) {
            $page->trigError('Le sujet est vide.');
        }

        if (!count($owners)) {
            $page->trigError('Il n\'y a pas de gestionnaire.');
        }

        if (count($members) < 4) {
            $page->trigError('Il n\'y a pas assez de membres.');
        }

        if (!$page->nb_errs()) {
            $page->trigSuccess('Demande de création envoyée&nbsp;!');
            $page->assign('created', true);
            $req = new ListeReq(S::user(), $asso, $list, $domain,
                                Post::t('desc'), Post::i('advertise'),
                                Post::i('modlevel'), Post::i('inslevel'),
                                $owners, $members);
            $req->submit();
        }
    }

    function handler_members($page, $liste = null)
    {
        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $mlist = $this->prepare_list($liste);
        $this->is_group_admin($page);

        $page->changeTpl('lists/members.tpl');

        if (Get::has('del')) {
            S::assert_xsrf_token();
            $mlist->unsubscribe();
            pl_redirect('lists/members/' . $liste);
        }

        if (Get::has('add')) {
            S::assert_xsrf_token();
            $mlist->subscribe();
            pl_redirect('lists/members/' . $liste);
        }

        $members = $mlist->getMembers();

        $tri_promo = !Env::b('alpha');

        if (list($det,$mem,$own) = $members) {
            $membres = list_sort_members($mem, $tri_promo);
            $moderos = list_sort_owners($own, $tri_promo);

            $page->assign_by_ref('details', $det);
            $page->assign_by_ref('members', $membres);
            $page->assign_by_ref('owners',  $moderos);
            $page->assign('nb_m',  count($mem));
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit d'en voir les détails.");
        }
    }

    function handler_csv(PlPage $page, $liste = null)
    {
        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }
        $this->is_group_admin($page);

        $mlist = $this->prepare_list($liste);
        $members = $mlist->getMembers();
        $list = list_fetch_basic_info(list_extract_members($members[1]));
        pl_cached_content_headers('text/x-csv', 'iso-8859-1', 1);

        echo utf8_decode("Nom;Prénom;Promotion\n");
        echo utf8_decode(implode("\n", $list));
        exit();
    }

    function handler_annu($page, $liste = null, $action = null, $subaction = null)
    {
        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $this->is_group_admin($page);

        $mlist = $this->prepare_list($liste);

        if (Get::has('del')) {
            S::assert_xsrf_token();
            $mlist->unsubscribe();
            pl_redirect('lists/annu/'.$liste);
        }
        if (Get::has('add')) {
            S::assert_xsrf_token();
            $mlist->subscribe();
            pl_redirect('lists/annu/'.$liste);
        }

        $owners = $mlist->getOwners();
        if (!is_array($owners)) {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit d'en voir les détails.");
        }

        list(,$members) = $mlist->getMembers();

        if ($action == 'moderators') {
            $users = $owners;
            $show_moderators = true;
            $action = $subaction;
            $subaction = '';
        } else {
            $show_moderators = false;
            $users = array();
            foreach ($members as $m) {
                $users[] = $m[1];
            }
        }

        require_once 'userset.inc.php';
        $view = new UserArraySet($users);
        $view->addMod('trombi', 'Trombinoscope', false, array('with_promo' => true));
        $view->addMod('listmember', 'Annuaire', true);
        if (empty($GLOBALS['IS_XNET_SITE'])) {
            $view->addMod('minifiche', 'Mini-fiches', false);
        }
        $view->addMod('map', 'Planisphère');
        $view->apply("lists/annu/$liste", $page, $action, $subaction);

        $page->changeTpl('lists/annu.tpl');
        $page->assign_by_ref('details', $owners[0]);
        $page->assign('show_moderators', $show_moderators);
    }

    function handler_archives($page, $liste = null, $action = null, $artid = null)
    {
        global $globals;

        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $this->is_group_admin($page);

        $mlist = $this->prepare_list($liste);

        $page->changeTpl('lists/archives.tpl');

        if (list($det) = $mlist->getMembers()) {
            if (substr($liste,0,5) != 'promo' && ($det['ins'] || $det['priv'])
                    && !$det['own'] && ($det['sub'] < 2)) {
                $page->kill("La liste n'existe pas ou tu n'as pas le droit de la consulter.");
            }
            $get = Array('listname' => $mlist->mbox, 'domain' => $mlist->domain);
            if (Post::has('updateall')) {
                $get['updateall'] = Post::v('updateall');
            }
            require_once 'banana/ml.inc.php';
            get_banana_params($get, null, $action, $artid);
            run_banana($page, 'MLBanana', $get);
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de la consulter.");
        }
    }

    function handler_rss($page, $liste = null, $alias = null, $hash = null)
    {
        if (!$liste) {
            return PL_NOT_FOUND;
        }
        $user = Platal::session()->tokenAuth($alias, $hash);
        if (is_null($user)) {
            return PL_FORBIDDEN;
        }

        $mlist = $this->prepare_list($liste);

        if (list($det) = $mlist->getMembers()) {
            if (substr($liste,0,5) != 'promo' && ($det['ins'] || $det['priv'])
                    && !$det['own'] && ($det['sub'] < 2)) {
                exit;
            }
            require_once('banana/ml.inc.php');
            $banana = new MLBanana($user, Array(
                'listname' => $mlist->mbox,
                'domain' => $mlist->domain,
                'action' => 'rss2'));
            $banana->run();
        }
        exit;
    }

    /** Register a moderation decision.
     * @param $mlist MailingList: the mailing list being moderated
     * @param $mid int: the message being moderated
     */
    protected function moderate_mail($mlist, $mid)
    {
        if (Env::has('mok')) {
            $action = 'accept';
        } elseif (Env::has('mno')) {
            $action = 'refuse';
        } elseif (Env::has('mdel')) {
            $action = 'delete';
        } else {
            return false;
        }
        Get::kill('mid');
        return XDB::execute("INSERT IGNORE INTO  email_list_moderate
                                         VALUES  ({?}, {?}, {?}, {?}, {?}, NOW(), {?}, NULL)",
                            $mlist->mbox, $mlist->domain, $mid, S::i('uid'), $action, Post::v('reason'));
    }

    function handler_moderate($page, $liste = null)
    {
        if (is_null($liste)) {
             return PL_NOT_FOUND;
        }

        $mlist = $this->prepare_list($liste);
        if (!$this->is_group_admin($page)) {
            $this->verify_list_owner($page, $mlist);
        }

        $page->changeTpl('lists/moderate.tpl');

        $page->register_modifier('hdc', 'list_header_decode');

        if (Env::has('sadd') || Env::has('sdel')) {
            S::assert_xsrf_token();

            if (Env::has('sadd')) {
                // Ensure the moderated request is still active
                $sub = $mlist->getPendingSubscription(Env::v('sadd'));

                $mlist->handleRequest(MailingList::REQ_SUBSCRIBE, Env::v('sadd'));
                $info = "validée";
            }
            if (Post::has('sdel')) {
                // Ensure the moderated request is still active
                $sub = $mlist->getPendingSubscription(Env::v('sdel'));

                $mlist->handleRequest(MailingList::REQ_REJECT, Post::v('sdel'), Post::v('reason'));
                $info = "refusée";
            }
            if ($sub) {
                $mailer = new PlMailer();
                $mailer->setFrom($mlist->getAddress(MailingList::KIND_BOUNCE));
                $mailer->addTo($mlist->getAddress(MailingList::KIND_OWNER));
                $mailer->addHeader('Reply-To', $mlist->getAddress(MailingList::KIND_OWNER));
                $mailer->setSubject("L'inscription de {$sub['name']} a été $info");
                $text = "L'inscription de {$sub['name']} à la liste " . $mlist->address ." a été $info par " . S::user()->fullName(true) . ".\n";
                if (trim(Post::v('reason'))) {
                    $text .= "\nLa raison invoquée est :\n" . Post::v('reason');
                }
                $mailer->setTxtBody(wordwrap($text, 72));
                $mailer->send();
            }
            if (Env::has('sadd')) {
                pl_redirect('lists/moderate/'.$liste);
            }
        }

        if (Post::has('moderate_mails') && Post::has('select_mails')) {
            S::assert_xsrf_token();

            $mails = array_keys(Post::v('select_mails'));
            foreach($mails as $mail) {
                $this->moderate_mail($mlist, $mail);
            }
        } elseif (Env::has('mid')) {
            if (Get::has('mid') && !Env::has('mok') && !Env::has('mdel')) {
                require_once 'banana/moderate.inc.php';

                $page->changeTpl('lists/moderate_mail.tpl');
                $params = array(
                    'listname' => $mlist->mbox,
                    'domain' => $mlist->domain,
                    'artid' => Get::i('mid'),
                    'part' => Get::v('part'),
                    'action' => Get::v('action'));
                $params['client'] = $this->prepare_client();
                run_banana($page, 'ModerationBanana', $params);

                $msg = file_get_contents('/etc/mailman/fr/refuse.txt');
                $msg = str_replace("%(adminaddr)s", $mlist->getAddress(MailingList::KIND_OWNER), $msg);
                $msg = str_replace("%(request)s",   "<< SUJET DU MAIL >>",    $msg);
                $msg = str_replace("%(reason)s",    "<< TON EXPLICATION >>",  $msg);
                $msg = str_replace("%(listname)s",  $liste, $msg);
                $page->assign('msg', $msg);
                return;
            }

            $this->moderate_mail($mlist, Env::i('mid'));
        } elseif (Env::has('sid')) {
            if (list($subs,$mails) = $this->get_pending_ops($mlist)) {
                foreach($subs as $user) {
                    if ($user['id'] == Env::v('sid')) {
                        $page->changeTpl('lists/moderate_sub.tpl');
                        $page->assign('del_user', $user);
                        return;
                    }
                }
            }

        }

        if (list($subs,$mails) = $this->get_pending_ops($mlist)) {
            foreach ($mails as $key=>$mail) {
                $mails[$key]['stamp'] = strftime("%Y%m%d%H%M%S", $mail['stamp']);
                if ($mail['fromx']) {
                    $page->assign('with_fromx', true);
                } else {
                    $page->assign('with_nonfromx', true);
                }
            }
            $page->assign_by_ref('subs', $subs);
            $page->assign_by_ref('mails', $mails);
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de la modérer.");
        }
    }

    static public function no_login_callback($login)
    {
        global $list_unregistered;

        $users = User::getPendingAccounts($login, true);
        if ($users && $users->total()) {
            if (!isset($list_unregistered)) {
                $list_unregistered = array();
            }
            $list_unregistered[$login] = $users;
        } else {
            list($name, $domain) = @explode('@', $login);
            if (User::isMainMailDomain($domain)) {
                User::_default_user_callback($login);
            }
        }
    }

    function handler_admin($page, $liste = null)
    {
        global $globals;

        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $mlist = $this->prepare_list($liste);
        $this->is_group_admin($page);
        if (!$this->is_group_admin($page)) {
            $this->verify_list_owner($page, $mlist);
        }

        $page->changeTpl('lists/admin.tpl');

        if (Env::has('send_mark')) {
            S::assert_xsrf_token();

            $actions = Env::v('mk_action');
            $uids    = Env::v('mk_uid');
            $mails   = Env::v('mk_email');
            foreach ($actions as $key=>$action) {
                switch ($action) {
                  case 'none':
                    break;

                  case 'marketu': case 'markets':
                    require_once 'emails.inc.php';
                    $user = User::get($uids[$key]);
                    $mail = valide_email($mails[$key]);
                    if (isvalid_email_redirection($mail, $user)) {
                        $from = ($action == 'marketu') ? 'user' : 'staff';
                        $market = Marketing::get($uids[$key], $mail);
                        if (!$market) {
                            $market = new Marketing($uids[$key], $mail, 'list', $mlist->address, $from, S::v('uid'));
                            $market->add();
                            break;
                        }
                    }

                  default:
                    XDB::execute('INSERT IGNORE INTO  register_subs (uid, type, sub, domain)
                                              VALUES  ({?}, \'list\', {?}, {?})',
                                  $uids[$key], $mlist->mbox, $mlist->domain);
                }
            }
        }

        if (Env::has('add_member') ||
            isset($_FILES['add_member_file']) && $_FILES['add_member_file']['tmp_name']) {
            S::assert_xsrf_token();

            if (isset($_FILES['add_member_file']) && $_FILES['add_member_file']['tmp_name']) {
                $upload =& PlUpload::get($_FILES['add_member_file'], S::user()->login(), 'list.addmember', true);
                if (!$upload) {
                    $page->trigError("Une erreur s'est produite lors du téléchargement du fichier.");
                } else {
                    $logins = $upload->getContents();
                }
            } else {
                $logins = Env::v('add_member');
            }

            $logins = preg_split("/[; ,\r\n\|]+/", $logins);
            $members = User::getBulkForlifeEmails($logins,
                                                  true,
                                                  array('ListsModule', 'no_login_callback'));
            $unfound = array_diff_key($logins, $members);

            // Make sure we send a list (array_values) of unique (array_unique)
            // emails.
            $members = array_values(array_unique($members));

            $arr = $mlist->subscribeBulk($members);

            $successes = array();
            if (is_array($arr)) {
                foreach($arr as $addr) {
                    $successes[] = $addr[1];
                    $page->trigSuccess("{$addr[0]} inscrit.");
                }
            }

            $already = array_diff($members, $successes);
            if (is_array($already)) {
                foreach ($already as $item) {
                    $page->trigWarning($item . ' est déjà inscrit.');
                }
            }

            if (is_array($unfound)) {
                foreach ($unfound as $item) {
                    if (trim($item) != '') {
                        $page->trigError($item . " ne correspond pas à un compte existant et n'est pas une adresse email.");
                    }
                }
            }
        }

        if (Env::has('del_member')) {
            S::assert_xsrf_token();

            if ($del_member = User::getSilent(Env::t('del_member'))) {
                $mlist->unsubscribeBulk(array($del_member->forlifeEmail()));
            }
            pl_redirect('lists/admin/'.$liste);
        }

        if (Env::has('add_owner')) {
            S::assert_xsrf_token();

            $owners = User::getBulkForlifeEmails(Env::v('add_owner'), false, array('ListsModule', 'no_login_callback'));
            if ($owners) {
                foreach ($owners as $forlife_email) {
                    if ($mlist->addOwner($forlife_email)) {
                        $page->trigSuccess($login ." ajouté aux modérateurs.");
                    }
                }
            }
        }

        if (Env::has('del_owner')) {
            S::assert_xsrf_token();

            if ($del_owner = User::getSilent(Env::t('del_owner'))) {
                $mlist->unsubscribeBulk(array($del_owner->forlifeEmail()));
            }
            pl_redirect('lists/admin/'.$liste);
        }

        if (list($det,$mem,$own) = $mlist->getMembers()) {
            global $list_unregistered;
            if ($list_unregistered) {
                $page->assign_by_ref('unregistered', $list_unregistered);
            }
            $membres = list_sort_members($mem, @$tri_promo);
            $moderos = list_sort_owners($own, @$tri_promo);

            $page->assign_by_ref('details', $det);
            $page->assign_by_ref('members', $membres);
            $page->assign_by_ref('owners',  $moderos);
            $page->assign('np_m', count($mem));
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de l'administrer.<br />"
                      . " Si tu penses qu'il s'agit d'une erreur, "
                      . "<a href='mailto:support@polytechnique.org'>contact le support</a>.");
        }
    }

    function handler_options($page, $liste = null)
    {
        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $mlist = $this->prepare_list($liste);
        if (!$this->is_group_admin($page)) {
            $this->verify_list_owner($page, $mlist);
        }

        $page->changeTpl('lists/options.tpl');

        if (Post::has('submit')) {
            S::assert_xsrf_token();

            $values = $_POST;
            $values = array_map('utf8_decode', $values);
            $spamlevel = intval($values['bogo_level']);
            $unsurelevel = intval($values['unsure_level']);
            if ($spamlevel == 0) {
                $unsurelevel = 0;
            }
            if ($spamlevel > 3 || $spamlevel < 0 || $unsurelevel < 0 || $unsurelevel > 1) {
                $page->trigError("Réglage de l'antispam non valide");
            } else {
                $mlist->setBogoLevel(($spamlevel << 1) + $unsurelevel);
            }
            switch($values['moderate']) {
                case '0':
                    $values['generic_nonmember_action']  = 0;
                    $values['default_member_moderation'] = 0;
                    break;
                case '1':
                    $values['generic_nonmember_action']  = 1;
                    $values['default_member_moderation'] = 0;
                    break;
                case '2':
                    $values['generic_nonmember_action']  = 1;
                    $values['default_member_moderation'] = 1;
                    break;
            }
            unset($values['submit'], $values['bogo_level'], $values['moderate']);
            $values['send_goodbye_msg']      = !empty($values['send_goodbye_msg']);
            $values['admin_notify_mchanges'] = !empty($values['admin_notify_mchanges']);
            $values['subscribe_policy']      = empty($values['subscribe_policy']) ? 0 : 2;
            if (isset($values['subject_prefix'])) {
                $values['subject_prefix'] = trim($values['subject_prefix']).' ';
            }
            $mlist->setOwnerOptions($values);
        } elseif (isvalid_email(Post::v('atn_add'))) {
            S::assert_xsrf_token();
            $mlist->whitelistAdd(Post::v('atn_add'));
        } elseif (Get::has('atn_del')) {
            S::assert_xsrf_token();
            $mlist->whitelistRemove(Post::v('atn_del'));
            pl_redirect('lists/options/'.$liste);
        }

        if (list($details, $options) = $mlist->getOwnerOptions()) {
            $page->assign_by_ref('details', $details);
            $page->assign_by_ref('options', $options);
            $bogo_level = intval($mlist->getBogoLevel());
            $page->assign('unsure_level', $bogo_level & 1);
            $page->assign('bogo_level', $bogo_level >> 1);
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de l'administrer");
        }
    }

    function handler_delete($page, $liste = null)
    {
        global $globals;
        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $mlist = $this->prepare_list($liste);
        if (!$this->is_group_admin($page)) {
            $this->verify_list_owner($page, $mlist);
        }

        $page->changeTpl('lists/delete.tpl');
        if (Post::v('valid') == 'OUI') {
            S::assert_xsrf_token();

            if ($mlist->delete(Post::b('del_archive'))) {
                require_once 'emails.inc.php';

                delete_list($mlist->mbox, $mlist->domain);
                $page->assign('deleted', true);
                $page->trigSuccess('La liste a été détruite&nbsp;!');
            } else {
                $page->kill('Une erreur est survenue lors de la suppression de la liste.<br />'
                         . 'Contact les administrateurs du site pour régler le problème : '
                         . '<a href="mailto:support@polytechnique.org">support@polytechnique.org</a>.');
            }
        } elseif (list($details, $options) = $mlist->getOwnerOptions()) {
            if (!$details['own']) {
                $page->trigWarning('Tu n\'es pas administrateur de la liste, mais du site.');
            }
            $page->assign_by_ref('details', $details);
            $page->assign_by_ref('options', $options);
            $page->assign('bogo_level', $mlist->getBogoLevel());
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de l'administrer.");
        }
    }

    function handler_soptions($page, $liste = null)
    {
        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $mlist = $this->prepare_list($liste);
        if (!$this->is_group_admin($page)) {
            $this->verify_list_owner($page, $mlist);
        }

        $page->changeTpl('lists/soptions.tpl');

        if (Post::has('submit')) {
            S::assert_xsrf_token();

            $values = $_POST;
            $values = array_map('utf8_decode', $values);
            unset($values['submit']);
            $values['advertised'] = empty($values['advertised']) ? false : true;
            $values['archive'] = empty($values['archive']) ? false : true;
            $mlist->setAdminOptions($values);
        }

        if (list($details, $options) = $mlist->getAdminOptions()) {
            $page->assign_by_ref('details', $details);
            $page->assign_by_ref('options', $options);
        } else {
            $page->kill("La liste n'existe pas.");
        }
    }

    function handler_check($page, $liste = null)
    {
        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $mlist = $this->prepare_list($liste);
        if (!$this->is_group_admin($page)) {
            $this->verify_list_owner($page, $mlist);
        }

        $page->changeTpl('lists/check.tpl');

        if (Post::has('correct')) {
            S::assert_xsrf_token();
            $mlist->checkOptions(true);
        }

        if (list($details, $options) = $mlist->checkOptions()) {
            $page->assign_by_ref('details', $details);
            $page->assign_by_ref('options', $options);
        } else {
            $page->kill("La liste n'existe pas.");
        }
    }

    function handler_admin_all($page)
    {
        $page->changeTpl('lists/admin_all.tpl');
        $page->setTitle('Administration - Mailing lists');

        $client = $this->prepare_client();
        $listes = $client->get_all_lists();
        $page->assign_by_ref('listes', $listes);
    }

    function handler_aaliases($page, $alias = null)
    {
        global $globals;
        require_once 'emails.inc.php';
        $page->setTitle('Administration - Aliases');

        if (Post::has('new_alias')) {
            pl_redirect('admin/aliases/' . Post::t('new_alias') . '@' . $globals->mail->domain);
        }

        // If no alias, list them all.
        if (is_null($alias)) {
            $page->changeTpl('lists/admin_aliases.tpl');
            $page->assign('aliases', array_merge(iterate_list_alias($globals->mail->domain), iterate_list_alias($globals->mail->domain2)));
            return;
        }

        list($local_part, $domain) = explode('@', $alias);
        if (!($globals->mail->domain == $domain || $globals->mail->domain2 == $domain)
              || !preg_match("/^[a-zA-Z0-9\-\.]*$/", $local_part)) {
            $page->trigErrorRedirect('Le nom de l\'alias est erroné.', $globals->asso('diminutif') . 'admin/aliases');
        }

        // Now we can perform the action.
        if (Post::has('del_alias')) {
            S::assert_xsrf_token();

            delete_list_alias($local_part, $domain);
            $page->trigSuccessRedirect($alias . ' supprimé.', 'admin/aliases');
        }

        if (Post::has('add_member')) {
            S::assert_xsrf_token();

            if (add_to_list_alias(Post::t('add_member'), $local_part, $domain)) {
                $page->trigSuccess('Ajout réussit.');
            } else {
                $page->trigError('Ajout infructueux.');
            }
        }

        if (Get::has('del_member')) {
            S::assert_xsrf_token();

            if (delete_from_list_alias(Get::t('del_member'), $local_part, $domain)) {
                $page->trigSuccess('Suppression réussie.');
            } else {
                $page->trigError('Suppression infructueuse.');
            }
        }

        $page->changeTpl('lists/admin_edit_alias.tpl');
        $page->assign('members', list_alias_members($local_part, $domain));
        $page->assign('alias', $alias);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
