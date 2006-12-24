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

class EmailModule extends PLModule
{
    function handlers()
    {
        return array(
            'emails' => $this->make_hook('emails', AUTH_COOKIE),
            'emails/alias'    => $this->make_hook('alias', AUTH_MDP),
            'emails/antispam' => $this->make_hook('antispam', AUTH_MDP),
            'emails/broken'   => $this->make_hook('broken', AUTH_COOKIE),
            'emails/redirect' => $this->make_hook('redirect', AUTH_MDP),
            'emails/send'     => $this->make_hook('send', AUTH_MDP),
            'emails/antispam/submit'  => $this->make_hook('submit', AUTH_COOKIE),

            'admin/emails/duplicated' => $this->make_hook('duplicated', AUTH_MDP, 'admin')
        );
    }

    function handler_emails(&$page, $action = null, $email = null)
    {
        global $globals;

        $page->changeTpl('emails/index.tpl');
        $page->assign('xorg_title','Polytechnique.org - Mes emails');

        $uid = S::v('uid');

        if ($action == 'best' && $email) {
            // bestalias is the first bit : 1
            // there will be maximum 8 bits in flags : 255
            XDB::execute("UPDATE  aliases SET flags=flags & (255 - 1) WHERE id={?}", $uid);
            XDB::execute("UPDATE  aliases SET flags=flags | 1 WHERE id={?} AND alias={?}",
                                   $uid, $email);
        }

        // on regarde si on a affaire à un homonyme
        $sql = "SELECT  alias, (type='a_vie') AS a_vie,
                        (alias REGEXP '\\\\.[0-9]{2}$') AS cent_ans,
                        FIND_IN_SET('bestalias',flags) AS best, expire
                  FROM  aliases
                 WHERE  id = {?} AND type!='homonyme'
              ORDER BY  LENGTH(alias)";
        $page->assign('aliases', XDB::iterator($sql, $uid));

		$homonyme = XDB::query("SELECT alias FROM aliases INNER JOIN homonymes ON (id = homonyme_id) WHERE user_id = {?} AND type = 'homonyme'", $uid);
		$page->assign('homonyme', $homonyme->fetchOneCell());
		
        $sql = "SELECT email
                FROM emails
                WHERE uid = {?} AND FIND_IN_SET('active', flags)";
        $page->assign('mails', XDB::iterator($sql, $uid));


        // on regarde si l'utilisateur a un alias et si oui on l'affiche !
        $forlife = S::v('forlife');
        $res = XDB::query(
                "SELECT  alias
                   FROM  virtual          AS v
             INNER JOIN  virtual_redirect AS vr USING(vid)
                  WHERE  (redirect={?} OR redirect={?}) 
                         AND alias LIKE '%@{$globals->mail->alias_dom}'",
                $forlife.'@'.$globals->mail->domain, $forlife.'@'.$globals->mail->domain2);
        $page->assign('melix', $res->fetchOneCell());
    }

    function handler_alias(&$page, $action = null, $value = null)
    {
        require_once 'validations.inc.php';

        global $globals;

        $page->changeTpl('emails/alias.tpl');
        $page->assign('xorg_title','Polytechnique.org - Alias melix.net');

        $uid     = S::v('uid');
        $forlife = S::v('forlife');

        $page->assign('demande', AliasReq::get_request($uid));

        if ($action == 'suppr' && $value) {
            //Suppression d'un alias
            XDB::execute(
                'DELETE virtual, virtual_redirect
                   FROM virtual
             INNER JOIN virtual_redirect USING (vid)
                  WHERE alias = {?} AND (redirect = {?} OR redirect = {?})', $value,
                $forlife.'@'.$globals->mail->domain, $forlife.'@'.$globals->mail->domain2);
        }

        //Récupération des alias éventuellement existants
        $res = XDB::query(
                "SELECT  alias, emails_alias_pub
                   FROM  auth_user_quick, virtual
             INNER JOIN  virtual_redirect USING(vid)
                   WHERE ( redirect={?} OR redirect= {?} )
                         AND alias LIKE '%@{$globals->mail->alias_dom}' AND user_id = {?}", 
                $forlife.'@'.$globals->mail->domain,
                $forlife.'@'.$globals->mail->domain2, S::v('uid'));
        list($alias, $visibility) = $res->fetchOneRow();
        $page->assign('actuel', $alias);

        if ($action == 'ask' && Env::has('alias') and Env::has('raison')) {
            //Si l'utilisateur vient de faire une damande

            $alias  = Env::v('alias');
            $raison = Env::v('raison');
            $public = (Env::v('public', 'off') == 'on')?"public":"private";

            $page->assign('r_alias', $alias);
            $page->assign('r_raison', $raison);
            if ($public == 'public') {
                $page->assign('r_public', true);
            }

            //Quelques vérifications sur l'alias (caractères spéciaux)
            if (!preg_match( "/^[a-zA-Z0-9\-.]{3,20}$/", $alias)) {
                $page->trig("L'adresse demandée n'est pas valide.
                            Vérifie qu'elle comporte entre 3 et 20 caractères
                            et qu'elle ne contient que des lettres non accentuées,
                            des chiffres ou les caractères - et .");
                return;
            } else {
                //vérifier que l'alias n'est pas déja pris
                $res = XDB::query('SELECT COUNT(*) FROM virtual WHERE alias={?}',
                                            $alias.'@'.$globals->mail->alias_dom);
                if ($res->fetchOneCell() > 0) {
                    $page->trig("L'alias $alias@{$globals->mail->alias_dom} a déja été attribué.
                                Tu ne peux donc pas l'obtenir.");
                    return;
                }

                //vérifier que l'alias n'est pas déja en demande
                $it = new ValidateIterator ();
                while($req = $it->next()) {
                    if ($req->type == "alias" and $req->alias == $alias) {
                        $page->trig("L'alias $alias@{$globals->mail->alias_dom} a déja été demandé.
                                    Tu ne peux donc pas l'obtenir pour l'instant.");
                        return ;
                    }
                }

                //Insertion de la demande dans la base, écrase les requêtes précédente
                $myalias = new AliasReq($uid, $alias, $raison, $public);
                $myalias->submit();
                $page->assign('success',$alias);
                return;
            }
        }
        elseif ($action == 'set'
            && ($value == 'public' || $value == 'private'))
        {
            if ($value == 'public') {
                XDB::execute("UPDATE auth_user_quick SET emails_alias_pub = 'public'
                                         WHERE user_id = {?}", S::v('uid'));
            } else {
                XDB::execute("UPDATE auth_user_quick SET emails_alias_pub = 'private'
                                         WHERE user_id = {?}", S::v('uid'));
            }

            $visibility = $value;
        }

        $page->assign('mail_public', ($visibility == 'public'));
    }

    function handler_redirect(&$page, $action = null, $email = null)
    {
        global $globals;

        require_once 'emails.inc.php';

        $page->changeTpl('emails/redirect.tpl');

        $uid     = S::v('uid');
        $forlife = S::v('forlife');

        $page->assign('eleve', S::i('promo') >= date("Y") - 5);

        $redirect = new Redirect(S::v('uid'));

        if ($action == 'remove' && $email) {
            $retour = $redirect->delete_email($email);
            $page->assign('retour', $retour);
        }
		
		if ($action == 'active' && $email) {
			$redirect->modify_one_email($email, true);
		}
		
		if ($action == 'inactive' && $email) {
			$redirect->modify_one_email($email, false);
		}
		
		if ($action == 'rewrite' && $email) {
			$rewrite = @func_get_arg(3);
			$redirect->modify_one_email_redirect($email, $rewrite);
		}
		
        if (Env::has('emailop')) {
            $actifs = Env::v('emails_actifs', Array());
            print_r(Env::v('emails_rewrite'));
            if (Env::v('emailop') == "ajouter" && Env::has('email')) {
                $page->assign('retour', $redirect->add_email(Env::v('email')));
            } elseif (empty($actifs)) {
                $page->assign('retour', ERROR_INACTIVE_REDIRECTION);
            } elseif (is_array($actifs)) {
                $page->assign('retour', $redirect->modify_email($actifs,
                    Env::v('emails_rewrite',Array())));
            }
        }

        $res = XDB::query(
                "SELECT  alias
                   FROM  virtual
             INNER JOIN  virtual_redirect USING(vid)
                  WHERE  (redirect={?} OR redirect={?})
                         AND alias LIKE '%@{$globals->mail->alias_dom}'", 
                $forlife.'@'.$globals->mail->domain, $forlife.'@'.$globals->mail->domain2);
        $melix = $res->fetchOneCell();
        if ($melix) {
            list($melix) = explode('@', $melix);
            $page->assign('melix',$melix);
        }

        $res = XDB::query(
                "SELECT  alias,expire
                   FROM  aliases
                  WHERE  id={?} AND (type='a_vie' OR type='alias')
               ORDER BY  !FIND_IN_SET('usage',flags), LENGTH(alias)", $uid);
        $page->assign('alias', $res->fetchAllAssoc());
        $page->assign('emails',$redirect->emails);
    }

    function handler_antispam(&$page, $statut_filtre = null)
    {
        require_once 'emails.inc.php';

        $page->changeTpl('emails/antispam.tpl');

        $bogo = new Bogo(S::v('uid'));
        if (isset($statut_filtre)) {
            $bogo->change(S::v('uid'), $statut_filtre + 0);
        }
        $page->assign('filtre',$bogo->level());
    }

    function handler_submit(&$page)
    {
        $page->changeTpl('emails/submit_spam.tpl');

        if (Post::has('send_email')) {
            $upload = $_FILES['mail']['tmp_name'];
            if (!is_uploaded_file($upload)) {
                $page->trig('Une erreur a été rencontrée lors du transfert du fichier');
                return;
            }
            $mime = mime_content_type($upload);
            if ($mime != 'text/x-mail' && $mime != 'message/rfc822') {
                $page->trig('Le fichier ne contient pas un mail complet');
                return;
            }
            global $globals;
            $box    = Post::v('type') . '@' . $globals->mail->domain;
            $mailer = new PlMailer();
            $mailer->addTo($box);
            $mailer->setFrom('"' . S::v('prenom') . ' ' . S::v('nom') . '" <web@' . $globals->mail->domain . '>');
            $mailer->setTxtBody(Post::v('type') . ' soumis par ' . S::v('forlife') . ' via le web');
            $mailer->addAttachment($upload, 'message/rfc822', $_FILES['mail']['name']);
            $mailer->send();
            $page->trig('Le message a été transmis à ' . $box);
        }
    }

    function handler_send(&$page)
    {
        global $globals;

        $page->changeTpl('emails/send.tpl');

        $page->assign('xorg_title','Polytechnique.org - Envoyer un email');

        // action si on recoit un formulaire
        if (Env::v('submit') == 'Envoyer')
        {
            function getEmails($aliases)
            {
                if (!is_array($aliases)) {
                    return null;
                }
                $rel = Env::v('contacts');
                $ret = array();
                foreach ($aliases as $alias) {
                    $ret[$alias] = $rel[$alias];
                }
                return join(', ', $ret);
            }

            $to2  = getEmails(Env::v('to_contacts'));
            $cc2  = getEmails(Env::v('cc_contacts'));
            $txt  = str_replace('^M', '', Env::v('contenu'));
            $to   = Env::v('to');
            $subj = Env::v('sujet');
            $from = Env::v('from');
            $cc   = Env::v('cc');
            $bcc  = Env::v('bcc');

            if (empty($to) && empty($cc) && empty($to2)) {
                $page->trig("Indique au moins un destinataire.");
            } else {
                $mymail = new PlMailer();
                $mymail->setFrom($from);
                $mymail->setSubject($subj);
                if (!empty($to))  { $mymail->addTo($to); }
                if (!empty($cc))  { $mymail->addCc($cc); }
                if (!empty($bcc)) { $mymail->addBcc($bcc); }
                if (!empty($to2)) { $mymail->addTo($to2); }
                if (!empty($cc2)) { $mymail->addCc($cc2); }
                if (isset($_FILES['uploaded'])) {
                    $mymail->addAttachment($_FILES['uploaded']['tmp_name'],
                                           $_FILES['uploaded']['type'],
                                           $_FILES['uploaded']['name']);  
                }
                $mymail->setTxtBody(wordwrap($txt,72,"\n"));
                if ($mymail->send()) {
                    $page->trig("Ton mail a bien été envoyé.");
                    $_REQUEST = array('bcc' => S::v('bestalias').'@'.$globals->mail->domain);
                } else {
                    $page->trig("Erreur lors de l'envoi du courriel, réessaye.");
                }
            }
        } else {
            $_REQUEST['bcc'] = S::v('bestalias').'@'.$globals->mail->domain;
        }

        $res = XDB::query(
                "SELECT  u.prenom, u.nom, u.promo, a.alias as forlife
                   FROM  auth_user_md5 AS u
             INNER JOIN  contacts      AS c ON (u.user_id = c.contact)
             INNER JOIN  aliases       AS a ON (u.user_id=a.id AND FIND_IN_SET('bestalias',a.flags))
                  WHERE  c.uid = {?}
                 ORDER BY u.nom, u.prenom", S::v('uid'));
        $page->assign('contacts', $res->fetchAllAssoc());
    }

    function handler_broken(&$page, $warn = null, $email = null)
    {
        require_once 'emails.inc.php';

        global $globals;

        $page->changeTpl('emails/broken.tpl');

        if ($warn == 'warn' && $email) {
            $email = valide_email($email);
            // vérifications d'usage
            $sel = XDB::query(
                    "SELECT  e.uid, a.alias
                       FROM  emails        AS e
                 INNER JOIN  auth_user_md5 AS u ON e.uid = u.user_id
                 INNER JOIN  aliases       AS a ON (e.uid = a.id AND type!='homonyme'
                                                    AND FIND_IN_SET('bestalias',a.flags))
                      WHERE  e.email={?}", $email);

            if (list($uid, $dest) = $sel->fetchOneRow()) {
                // envoi du mail
                $message = "Bonjour !

Ce mail a été généré automatiquement par le service de patte cassée de
Polytechnique.org car un autre utilisateur, ".S::v('prenom').' '.S::v('nom').",
nous a signalé qu'en t'envoyant un mail, il avait reçu un message d'erreur
indiquant que ton adresse de redirection $email
ne fonctionnait plus !

Nous te suggérons de vérifier cette adresse, et le cas échéant de mettre
à jour sur le site <{$globals->baseurl}/emails> tes adresses
de redirection...

Pour plus de rensignements sur le service de patte cassée, n'hésites pas à
consulter la page <{$globals->baseurl}/emails/broken>.


A bientôt sur Polytechnique.org !
L'équipe d'administration <support@polytechnique.org>";

                $mail = new PlMailer();
                $mail->setFrom('"Polytechnique.org" <support@polytechnique.org>');
                $mail->addTo("$dest@polytechnique.org");
                $mail->setSubject("Une de tes adresse de redirection Polytechnique.org ne marche plus !!");
                $mail->setTxtBody($message);
                $mail->send();
                $page->trig("Mail envoyé ! :o)");
            }
        } elseif (Post::has('email')) {
            $email = valide_email(Post::v('email'));

            list(,$fqdn) = explode('@', $email);
            $fqdn = strtolower($fqdn);
            if ($fqdn == 'polytechnique.org' || $fqdn == 'melix.org'
            ||  $fqdn == 'm4x.org' || $fqdn == 'melix.net')
            {
                $page->assign('neuneu', true);
            } else {
                $page->assign('email',$email);
                $sel = XDB::query(
                        "SELECT  e1.uid, e1.panne != 0 AS panne, count(e2.uid) AS nb_mails,
                                 u.nom, u.prenom, u.promo
                           FROM  emails as e1
                      LEFT JOIN  emails as e2 ON(e1.uid = e2.uid 
                                                 AND FIND_IN_SET('active', e2.flags)
                                                 AND e1.email != e2.email)
                     INNER JOIN  auth_user_md5 as u ON(e1.uid = u.user_id)
                          WHERE  e1.email = {?}
                       GROUP BY  e1.uid", $email);
                if ($x = $sel->fetchOneAssoc()) {
                    // on écrit dans la base que l'adresse est cassée
                    if (!$x['panne']) {
                        XDB::execute("UPDATE emails
                                         SET panne=NOW(),
                                             last=NOW(),
                                             panne_level = 1
                                       WHERE email = {?}", $email);
                    } else {
                        XDB::execute("UPDATE emails
                                         SET panne_level = 1
                                       WHERE email = {?} AND panne_level = 0");
                    }
                    $page->assign_by_ref('x', $x);
                }
            }
        }
    }

    function handler_duplicated(&$page, $action = 'list', $email = null)
    {
        $page->changeTpl('emails/duplicated.tpl');

        $states = array('pending'   => 'En attente...',
                        'safe'      => 'Pas d\'inquiétude',
                        'unsafe'    => 'Recherches en cours',
                        'dangerous' => 'Usurpations par cette adresse');
        $page->assign('states', $states);

        switch (Post::v('action')) {
        case 'create':
            if (trim(Post::v('emailN')) != '') {
                Xdb::execute('INSERT IGNORE INTO emails_watch (email, state, detection, last, uid, description)
                                          VALUES ({?}, {?}, CURDATE(), NOW(), {?}, {?})',
                             trim(Post::v('emailN')), Post::v('stateN'), S::i('uid'), Post::v('descriptionN'));
            };
            break;

        case 'edit':
            Xdb::execute('UPDATE emails_watch
                             SET state = {?}, last = NOW(), uid = {?}, description = {?}
                           WHERE email = {?}', Post::v('stateN'), S::i('uid'), Post::v('descriptionN'), Post::v('emailN'));
            break;

        default:
            if ($action == 'delete' && !is_null($email)) {
                Xdb::execute('DELETE FROM emails_watch WHERE email = {?}', $email);
            }
        }
        if ($action != 'create' && $action != 'edit') {
            $action = 'list';
        }
        $page->assign('action', $action);

        if ($action == 'list') {
            $sql = "SELECT  w.email, w.detection, w.state, a.alias AS forlife
                      FROM  emails_watch  AS w
                 LEFT JOIN  emails        AS e USING(email)
                 LEFT JOIN  aliases       AS a ON (a.id = e.uid AND a.type = 'a_vie')
                  ORDER BY  w.state, w.email, a.alias";
            $it = Xdb::iterRow($sql);

            $table = array();
            $props = array();
            while (list($email, $date, $state, $forlife) = $it->next()) {
                if (count($props) == 0 || $props['mail'] != $email) {
                    if (count($props) > 0) {
                        $table[] = $props;
                    }
                    $props = array('mail' => $email,
                                   'detection' => $date,
                                   'state' => $state,
                                   'users' => array($forlife));
                } else {
                    $props['users'][] = $forlife;
                }
            }
            if (count($props) > 0) {
                $table[] = $props;
            }
            $page->assign('table', $table);
        } elseif ($action == 'edit') {
            $sql = "SELECT  w.detection, w.state, w.last, w.description,
                            a1.alias AS edit, a2.alias AS forlife
                      FROM  emails_watch AS w
                 LEFT JOIN  aliases      AS a1 ON (a1.id = w.uid AND a1.type = 'a_vie')     
                 LEFT JOIN  emails       AS e  ON (w.email = e.email)
                 LEFT JOIN  aliases      AS a2 ON (a2.id = e.uid AND a2.type = 'a_vie')
                     WHERE  w.email = {?}
                  ORDER BY  a2.alias";
            $it = Xdb::iterRow($sql, $email);

            $props = array();
            while (list($detection, $state, $last, $description, $edit, $forlife) = $it->next()) {
                if (count($props) == 0) {
                    $props = array('mail'        => $email,
                                   'detection'   => $detection,
                                   'state'       => $state,
                                   'last'        => $last,
                                   'description' => $description,
                                   'edit'        => $edit,
                                   'users'       => array($forlife));
                } else {
                    $props['users'][] = $forlife;
                }
            }
            $page->assign('doublon', $props);
        }
    }
}

?>
