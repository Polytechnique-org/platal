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

class ListsModule extends PLModule
{
    var $client;

    function handlers()
    {
        return array(
            'lists'           => $this->make_hook('lists',     AUTH_MDP),
            'lists/create'    => $this->make_hook('create',    AUTH_MDP),

            'lists/members'   => $this->make_hook('members',   AUTH_COOKIE),
            'lists/trombi'    => $this->make_hook('trombi',    AUTH_COOKIE),
            'lists/archives'  => $this->make_hook('archives',  AUTH_COOKIE),

            'lists/moderate'  => $this->make_hook('moderate',  AUTH_MDP),
            'lists/admin'     => $this->make_hook('admin',     AUTH_MDP),
            'lists/options'   => $this->make_hook('options',   AUTH_MDP),
            'lists/delete'    => $this->make_hook('delete',    AUTH_MDP),

            'lists/soptions'  => $this->make_hook('soptions',  AUTH_MDP),
            'lists/check'     => $this->make_hook('check',     AUTH_MDP),
        );
    }

    function prepare_client(&$page)
    {
        require_once 'lists.inc.php';

        $this->client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'));
    }

    function handler_lists(&$page)
    {
        global $globals;

        $this->prepare_client($page);

        $page->changeTpl('listes/index.tpl');
        $page->assign('xorg_title','Polytechnique.org - Listes de diffusion');


        if (Get::has('del')) {
            $this->client->unsubscribe(Get::get('del'));
            redirect('lists');
        }
        if (Get::has('add')) {
            $this->client->subscribe(Get::get('add'));
            redirect('lists');
        }
        if (Post::has('promo_add')) {
            $promo = Post::getInt('promo_add');
            if ($promo >= 1900 and $promo < 2100) {
                $this->client->subscribe("promo$promo");
            } else {
                $page->trig("promo incorrecte, il faut une promo sur 4 chiffres.");
            }
        }
        $listes = $this->client->get_lists();
        $page->assign_by_ref('listes', $listes);
    }

    function handler_create(&$page)
    {
        global $globals;
        $page->changeTpl('listes/create.tpl');

        $owners  = preg_split("/[\s]+/", Post::get('owners'), -1, PREG_SPLIT_NO_EMPTY);
        $members = preg_split("/[\s]+/", Post::get('members'), -1, PREG_SPLIT_NO_EMPTY);

        // click on validate button 'add_owner_sub' or type <enter>
        if (Post::has('add_owner_sub') && Post::has('add_owner')) {
            require_once('user.func.inc.php');
            // if we want to add an owner and then type <enter>, then both
            // add_owner_sub and add_owner are filled.
            if (Post::get('add_owner') != "") {
                if (($forlife = get_user_forlife(Post::get('add_owner'))) !== false) {
                    $owners [] = $forlife;
                }
                // if we want to add a member and then type <enter>, then
                // add_owner_sub is filled, whereas add_owner is empty.
            } else if (Post::has('add_member')) {
                if (($forlife = get_user_forlife(Post::get('add_member'))) !== false) {
                    $members[] = $forlife;
                }
            }
        }

        // click on validate button 'add_member_sub'
        if (Post::has('add_member_sub') && Post::has('add_member')) {
            require_once('user.func.inc.php');
            if (($forlife = get_user_forlife(Post::get('add_member'))) !== false) {
                $members[] = $forlife;
            }
        }

        ksort($owners);	 array_unique($owners);
        ksort($members); array_unique($members);

        $page->assign('owners', join(' ', $owners));
        $page->assign('members', join(' ', $members));

        if (!Post::has('submit')) {
            return;
        }

        $liste = Post::get('liste');

        if (empty($liste)) {
            $page->trig('champs «addresse souhaitée» vide');
        }
        if (!preg_match("/^[a-zA-Z0-9\-]*$/", $liste)) {
            $page->trig('le nom de la liste ne doit contenir que des lettres, chiffres et tirets');
        }

        $res = XDB::query("SELECT COUNT(*) FROM aliases WHERE alias={?}", $liste);
        $n   = $res->fetchOneCell();

        if ($n) {
            $page->trig('cet alias est déjà pris');
        }

        if (!Post::get(desc)) {
            $page->trig('le sujet est vide');
        }

        if (!count($owners)) {
            $page->trig('pas de gestionnaire');
        }

        if (count($members)<4) {
            $page->trig('pas assez de membres');
        }

        if (!$page->nb_errs()) {
            $page->assign('created', true);
            require_once 'validations.inc.php';
            $req = new ListeReq(Session::getInt('uid'), $liste,
                                Post::get('desc'), Post::getInt('advertise'),
                                Post::getInt('modlevel'), Post::getInt('inslevel'),
                                $owners, $members);
            $req->submit();
        }
    }

    function handler_members(&$page, $liste = null)
    {
        global $globals;

        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $this->prepare_client($page);

        $page->changeTpl('listes/members.tpl');

        if (Get::has('del')) {
            $this->client->unsubscribe($liste);
            redirect($liste);
        }

        if (Get::has('add')) {
            $this->client->subscribe($liste);
            redirect($liste);
        }

        $members = $this->client->get_members($liste);

        $tri_promo = !Env::getBool('alpha');

        if (list($det,$mem,$own) = $members) {
            $membres = list_sort_members($mem, $tri_promo);
            $moderos = list_sort_owners($own, $tri_promo);

            $page->assign_by_ref('details', $det);
            $page->assign_by_ref('members', $membres);
            $page->assign_by_ref('owners',  $moderos);
            $page->assign('nb_m',  count($mem));
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit d'en voir les détails");
        }
    }

    function _get_list($offset, $limit)
    {
        global $globals, $platal;
        list($total, $members) = $this->client->get_members_limit($platal->argv[1], $offset, $limit);

        $membres = Array();
        foreach ($members as $member) {
            list($m) = explode('@',$member[1]);
            $res = XDB::query("SELECT  prenom,if (nom_usage='', nom, nom_usage) AS nom,
                                                 promo, a.alias AS forlife
                                           FROM  auth_user_md5 AS u
                                     INNER JOIN  aliases AS a ON u.user_id = a.id
                                          WHERE  a.alias = {?}", $m);
            if ($tmp = $res->fetchOneAssoc()) {
                $membres[$tmp['nom']] = $tmp;
            } else {
                $membres[$member[0]] = array('addr' => $member[0]);
            }
        }
        return array($total, $membres);
    }

    function handler_trombi(&$page, $liste = null)
    {
        global $globals;

        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $this->prepare_client($page);

        $page->changeTpl('listes/trombi.tpl');

        if (Get::has('del')) {
            $this->client->unsubscribe($liste);
            redirect($liste);
        }
        if (Get::has('add')) {
            $this->client->subscribe($liste);
            redirect($liste);
        }

        $owners = $this->client->get_owners($liste);

        if (is_array($owners)) {
            require_once 'trombi.inc.php';
            $moderos = list_sort_owners($owners[1]);

            $page->assign_by_ref('details', $owners[0]);
            $page->assign_by_ref('owners',  $moderos);

            $trombi = new Trombi(array(&$this, '_get_list'));
            $page->assign('trombi', $trombi);
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit d'en voir les détails");
        }
    }

    function handler_archives(&$page, $liste = null)
    {
        global $globals;

        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $this->prepare_client($page);

        $page->changeTpl('listes/archives.tpl');

        $page->addCssLink('css/lists.archives.css');
        if (list($det) = $this->client->get_members($liste)) {
            if (substr($liste,0,5) != 'promo' && ($det['ins'] || $det['priv'])
            && !$det['own'] && ($det['sub'] < 2))
            {
                $page->kill("La liste n'existe pas ou tu n'as pas le droit de la consulter");
            } elseif (Get::has('file')) {
                $file = Get::get('file');
                $rep  = Get::get('rep');
                if (strstr('/', $file)!==false || !preg_match(',^\d+/\d+$,', $rep)) {
                    $page->kill("La liste n'existe pas ou tu n'as pas le droit de la consulter");
                } else { 
                    $page->assign('archives', $globals->lists->spool
                                  ."/{$domain}{$globals->lists->vhost_sep}$liste/$rep/$file");
                }
            } else {
                $archs = Array();
                foreach (glob($globals->lists->spool
                              ."/{$domain}{$globals->lists->vhost_sep}$liste/*/*") as $rep)
                {
                    if (preg_match(",/(\d*)/(\d*)$,", $rep, $matches)) {
                        $archs[intval($matches[1])][intval($matches[2])] = true;
                    }
                }
                $page->assign('archs', $archs);
                $page->assign('range', range(1,12));
            }
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de la consulter");
        }
    }

    function handler_moderate(&$page, $liste = null)
    {
        global $globals;

        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $this->prepare_client($page);

        $page->changeTpl('listes/moderate.tpl');

        $page->register_modifier('qpd', 'quoted_printable_decode');

        if (Env::has('sadd')) { /* 4 = SUBSCRIBE */
            $this->client->handle_request($liste,Env::get('sadd'),4,'');
            redirect($liste);
        }

        if (Post::has('sdel')) { /* 2 = REJECT */
            $this->client->handle_request($liste,Post::get('sdel'),2,Post::get('reason'));
        }

        if (Env::has('mid')) {
            $mid    = Env::get('mid');
            $mail   = $this->client->get_pending_mail($liste, $mid);
            $reason = '';

            $prenom = Session::get('prenom');
            $nom    = Session::get('nom');

            if (Env::has('mok')) {
                $action  = 1; /** 2 = ACCEPT **/
                $subject = "Message accepté";
                $append .= "a été accepté par $prenom $nom.\n";
            } elseif (Env::has('mno')) {
                $action  = 2; /** 2 = REJECT **/
                $subject = "Message refusé";
                $reason  = Post::get('reason');
                $append  = "a été refusé par $prenom $nom avec la raison :\n\n"
                        .  $reason;
            } elseif (Env::has('mdel')) {
                $action  = 3; /** 3 = DISCARD **/
                $subject = "Message supprimé";
                $append  = "a été supprimé par $prenom $nom.\n\n"
                        .  "Rappel: il ne faut utiliser cette opération "
                        .  "que dans le cas de spams ou de virus !\n";
            }

            if (isset($action) && $this->client->handle_request($liste, $mid, $action, $reason)) {
                $texte = "le message suivant :\n\n"
                        ."    Auteur: {$mail['sender']}\n"
                        ."    Sujet : « {$mail['subj']} »\n"
                        ."    Date  : ".strftime("le %d %b %Y à %H:%M:%S", (int)$mail['stamp'])."\n\n"
                        .$append;
                require_once 'diogenes/diogenes.hermes.inc.php';
                $mailer = new HermesMailer();
                $mailer->addTo("$liste-owner@{$domain}");
                $mailer->setFrom("$liste-bounces@{$domain}");
                $mailer->addHeader('Reply-To', "$liste-owner@{$domain}");
                $mailer->setSubject($subject);
                $mailer->setTxtBody(wordwrap($texte,72));
                $mailer->send();
                Get::kill('mid');
            }

            if (Get::has('mid') && is_array($mail)) {
                $msg = file_get_contents('/etc/mailman/fr/refuse.txt');
                $msg = str_replace("%(adminaddr)s", "$liste-owner@{$domain}", $msg);
                $msg = str_replace("%(request)s",   "<< SUJET DU MAIL >>",    $msg);
                $msg = str_replace("%(reason)s",    "<< TON EXPLICATION >>",  $msg);
                $msg = str_replace("%(listname)s",  $liste, $msg);
                $page->assign('msg', $msg); 

                $page->changeTpl('listes/moderate_mail.tpl');
                $page->assign_by_ref('mail', $mail);
                $page->run();
            }

        } elseif (Env::has('sid')) {

            if (list($subs,$mails) = $this->client->get_pending_ops($liste)) {
                foreach($subs as $user) {
                    if ($user['id'] == Env::get('sid')) {
                        $page->changeTpl('listes/moderate_sub.tpl');
                        $page->assign('del_user',$user);
                        $page->run();
                    }
                }
            }

        }

        if (list($subs,$mails) = $this->client->get_pending_ops($liste)) {
            $page->assign_by_ref('subs', $subs);
            $page->assign_by_ref('mails', $mails);
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de la modérer");
        }
    }

    function handler_admin(&$page, $liste = null)
    {
        global $globals;

        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $this->prepare_client($page);

        $page->changeTpl('listes/admin.tpl');

        if (Env::has('add_member')) {

            require_once('user.func.inc.php');
            $members = explode(' ', Env::get('add_member'));
            if ($members) foreach ($members as $i => $alias) {
                if (($login = get_user_forlife($alias)) !== false) {;
                        $members[$i] = $login;
                }
            }

            $arr = $this->client->mass_subscribe($liste, $members);
            if (is_array($arr)) {
                foreach($arr as $addr) {
                    $page->trig("{$addr[0]} inscrit.");
                }
            }
        }

        if (Env::has('del_member')) {
            if (strpos(Env::get('del_member'), '@') === false) {
                $this->client->mass_unsubscribe(
                    $liste, array(Env::get('del_member').'@'.$globals->mail->domain));
            } else {
                $this->client->mass_unsubscribe($liste, array(Env::get('del_member')));
            }
            redirect($liste);
        }

        if (Env::has('add_owner')) {
            require_once('user.func.inc.php');

            $owners = explode(' ', Env::get('add_owner'));

            if ($owners) foreach ($owners as $alias) {
                if (($login = get_user_forlife($alias)) === false) {;
                    $login = $alias;
                }

                if ($this->client->add_owner($liste, $login)) {
                    $page->trig($alias." ajouté aux modérateurs.");
                }
            }
        }

        if (Env::has('del_owner')) {
            if (strpos(Env::get('del_owner'), '@') === false) {
                $this->client->del_owner($liste, Env::get('del_owner').'@'.$globals->mail->domain);
            } else {
                $this->client->del_owner($liste, Env::get('del_owner'));
            }
            redirect($liste);
        }

        if (list($det,$mem,$own) = $this->client->get_members($liste)) {

            $membres = list_sort_members($mem, $tri_promo);
            $moderos = list_sort_owners($own, $tri_promo);

            $page->assign_by_ref('details', $det);
            $page->assign_by_ref('members', $membres);
            $page->assign_by_ref('owners',  $moderos);
            $page->assign('np_m', count($mem));

        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de l'administrer");
        }
    }

    function handler_options(&$page, $liste = null)
    {
        global $globals;

        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $this->prepare_client($page);

        $page->changeTpl('listes/options.tpl');

        if (Post::has('submit')) {
            $values = $_POST;
            $this->client->set_bogo_level($liste, intval($values['bogo_level']));
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
            $this->client->set_owner_options($liste, $values);
        } elseif (isvalid_email(Post::get('atn_add'))) {
            $this->client->add_to_wl($liste, Post::get('atn_add'));
        } elseif (Get::has('atn_del')) {
            $this->client->del_from_wl($liste, Get::get('atn_del'));
            redirect("{$_SERVER['PHP_SELF']}?liste=$liste");
        }

        if (list($details,$options) = $this->client->get_owner_options($liste)) {
            $page->assign_by_ref('details', $details);
            $page->assign_by_ref('options', $options);
            $page->assign('bogo_level', $this->client->get_bogo_level($liste));
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de l'administrer");
        }
    }

    function handler_delete(&$page, $liste = null)
    {
        global $globals;

        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $this->prepare_client($page);

        $page->changeTpl('listes/delete.tpl');

        if (Post::get('valid') == 'OUI'
        && $this->client->delete_list($liste, Post::getBool('del_archive')))
        {
            foreach (array('', '-owner', '-admin', '-bounces') as $app) {
                XDB::execute("DELETE FROM  aliases
                                              WHERE  type='liste' AND alias='{?}'",
                                       $liste.$app);
            }
            $page->assign('deleted', true);
        } elseif (list($details,$options) = $this->client->get_owner_options($liste)) {
            $page->assign_by_ref('details', $details);
            $page->assign_by_ref('options', $options);
            $page->assign('bogo_level', $this->client->get_bogo_level($liste));
        } else {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de l'administrer");
        }
    }

    function handler_soptions(&$page, $liste = null)
    {
        global $globals;

        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $this->prepare_client($page);

        $page->changeTpl('listes/soptions.tpl');

        if (Post::has('submit')) {
            $values = $_POST;
            unset($values['submit']);
            $values['advertised'] = empty($values['advertised']) ? false : true;
            $values['archive'] = empty($values['archive']) ? false : true;
            $this->client->set_admin_options($liste, $values);
        }

        if (list($details,$options) = $this->client->get_admin_options($liste)) {
            $page->assign_by_ref('details', $details);
            $page->assign_by_ref('options', $options);
        } else {
            $page->kill("La liste n'existe pas");
        }
    }

    function handler_check(&$page, $liste = null)
    {
        global $globals;

        if (is_null($liste)) {
            return PL_NOT_FOUND;
        }

        $this->prepare_client($page);

        $page->changeTpl('listes/check.tpl');

        if (Post::has('correct')) {
            $this->client->check_options($liste, true);
        }

        if (list($details,$options) = $this->client->check_options($liste)) {
            $page->assign_by_ref('details', $details);
            $page->assign_by_ref('options', $options);
        } else {
            $page->kill("La liste n'existe pas");
        }
    }
}

?>
