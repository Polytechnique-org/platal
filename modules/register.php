<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

class RegisterModule extends PLModule
{
    function handlers()
    {
        return array(
            'register'         => $this->make_hook('register', AUTH_PUBLIC),
            'register/end'     => $this->make_hook('end',      AUTH_PUBLIC),
            'register/end.php' => $this->make_hook('end_old',  AUTH_PUBLIC),
            'register/success' => $this->make_hook('success',  AUTH_MDP),
        );
    }

    function handler_register(&$page, $hash = null)
    {
        $alert     = null;
        $sub_state = S::v('sub_state', Array());
        if (!isset($sub_state['step'])) {
            $sub_state['step'] = 0;
        }
        if (!isset($sub_state['backs'])) {
            $sub_state['backs'] = array();
        }
        if (Get::has('back') && Get::i('back') < $sub_state['step']) {
            $sub_state['step'] = max(0,Get::i('back'));
            $state = $sub_state;
            unset($state['backs']);
            $sub_state['backs'][] = $state;
            if (count($sub_state['backs']) == 3) {
                $alert .= "Tentative d'inscription tres hesitante - ";
            }   
        }

        // Compatibility with old sources, keep it atm
        if (!$hash && Env::has('hash')) {
            $hash = Env::v('hash');
        }

        if ($hash) {
            $res = XDB::query(
                    "SELECT  m.uid, u.promo, u.nom, u.prenom, u.matricule, FIND_IN_SET('watch', u.flags)
                       FROM  register_marketing AS m
                 INNER JOIN  auth_user_md5      AS u ON u.user_id = m.uid
                      WHERE  m.hash={?}", $hash);
            if (list($uid, $promo, $nom, $prenom, $ourmat, $watch) = $res->fetchOneRow()) {
                $sub_state['uid']    = $uid;
                $sub_state['hash']   = $hash;
                $sub_state['promo']  = $promo;
                $sub_state['nom']    = $nom;
                $sub_state['prenom'] = $prenom;
                $sub_state['ourmat'] = $ourmat;
                $sub_state['watch']  = $watch;

                XDB::execute(
                        "REPLACE INTO  register_mstats (uid,sender,success)
                               SELECT  m.uid, m.sender, 0
                                 FROM  register_marketing AS m
                                WHERE  m.hash", $sub_state['hash']);
            }
        }

        switch ($sub_state['step']) {
            case 0:
                require_once('wiki.inc.php');
                wiki_require_page('Reference.Charte');
                if (Post::has('step1')) {
                    $sub_state['step'] = 1;
                    if (isset($sub_state['hash'])) {
                        $sub_state['step'] = 3;
                        require_once(dirname(__FILE__) . '/register/register.inc.php');
                        create_aliases($sub_state);
                    }
                }
                break;

            case 1:
                if (Post::has('promo')) {
                    $promo = Post::i('promo');
                    $res = XDB::query("SELECT COUNT(*)
                                         FROM auth_user_md5
                                        WHERE  perms='pending' AND deces = '0000-00-00'
                                               AND promo = {?}",
                                      $promo);
                    if (!$res->fetchOneCell()) {
                        $err = "La promotion saisie est incorrecte ou tous les camardes de cette promo sont inscrits !";
                    } else {
                        $sub_state['step']  = 2;
                        $sub_state['promo'] = $promo;
                        if ($promo >= 1996 && $promo<2000) {
                            $sub_state['mat'] = ($promo % 100)*10 . '???';
                        } elseif($promo >= 2000) {
                            $sub_state['mat'] = 100 + ($promo % 100) . '???';
                        }
                    }
                }
                break;

            case 2:
                if (count($_POST)) {
                    require_once(dirname(__FILE__) . '/register/register.inc.php');
                    $sub_state['prenom'] = Post::v('prenom');
                    $sub_state['nom']    = Post::v('nom');
                    $sub_state['mat']    = Post::v('mat');
                    $err = check_new_user($sub_state);

                    if ($err !== true) { break; }
                    $err = create_aliases($sub_state);
                    if ($err === true) {
                        unset($err);
                        $sub_state['step'] = 3;
                    }
                }
                break;

            case 3:
                if (count($_POST)) {
                    require_once(dirname(__FILE__) . '/register/register.inc.php');
                    if (!isvalid_email(Post::v('email'))) {
                        $err[] = "Le champ 'E-mail' n'est pas valide.";
                    } elseif (!isvalid_email_redirection(Post::v('email'))) {
                        $err[] = $sub_state['forlife']." doit renvoyer vers un email existant ".
                            "valide, en particulier, il ne peut pas être renvoyé vers lui-même.";
                    }
                    $birth = trim(Env::v('naissance'));
                    if (!preg_match('/^[0-3][0-9][01][0-9][12][90][0-9][0-9]$/', $birth)) {
                        $err[] = "La 'Date de naissance' n'est pas correcte.";
                    } else {
                        $year  = (int)substr($birth, 4, 4);
                        $promo = (int)$sub_state['promo'];
                        if ($year > $promo - 15 || $year < $promo - 30) {
                            $err[] = "La 'Date de naissance' n'est pas correcte.";
                            $alert = "Date de naissance incorrecte a l'inscription - ";
                        }
                    }

                    // Check if the given email is known as dangerous
                    $res = Xdb::iterRow("SELECT  w.state, w.description, a.alias
                                           FROM  emails       AS e
                                     INNER JOIN  emails_watch AS w ON (e.email = w.email AND w.state != 'safe')
                                     INNER JOIN  aliases      AS a ON (e.uid = a.id AND a.type = 'a_vie')
                                          WHERE  e.email = {?}
                                       ORDER BY  a.alias", Post::v('email'));
                    $aliases = array();
                    while(list($gstate, $gdescription, $alias) = $res->next()) {
                        $state       = $gstate;
                        $description = $gdescription;
                        $aliases[]   = $alias;
                    }
                    if (count($aliases) != 0) {
                        $alert .= "Email surveille propose a l'inscription - ";
                    }
                    if ($sub_state['watch']) {
                        $alter .= "Inscription d'un utilisateur surveillé - ";
                    }

                    if (check_ip('unsafe')) {
                        unset($err);
                    }

                    if (isset($err)) {
                        $err = join('<br />', $err);
                    } else {
                        $sub_state['naissance'] = sprintf("%s-%s-%s",
                                                          substr($birth,4,4),
                                                          substr($birth,2,2),
                                                          substr($birth,0,2));
                        $sub_state['email']     = Post::v('email');
                        if (check_ip('unsafe')) {
                            $err = "Une erreur s'est produite lors de l'inscription."
                                 . " Merci de contacter <a href='register@polytechnique.org'>register@polytechnique.org</a>"
                                 . " pour nous faire part de cette erreur";
                            $alert .= "Tentative d'inscription depuis une IP surveillee";
                        } else {
                            $sub_state['step'] = 4;
                            if (count($sub_state['backs']) >= 3) {
                                $alert .= "Fin d'une inscription hésitante";
                            }
                            finish_ins($sub_state);
                        }
                    }
                }
                break;
        }

        $_SESSION['sub_state'] = $sub_state;
        if ($alert) {
            send_warning_mail($alert);
        }
        $page->changeTpl('register/step'.intval($sub_state['step']).'.tpl');
        if (isset($err)) {
            $page->trig($err);
        }
    }

    function handler_end_old(&$page)
    {
        return $this->handler_end($page, Env::v('hash'));
    }

    function handler_end(&$page, $hash = null)
    {
        global $globals;

        $page->changeTpl('register/end.tpl');
        $_SESSION['sub_state'] = array('step' => 5);
        require_once('user.func.inc.php');

        if ($hash) {
            $res = XDB::query(
                    "SELECT  r.uid, r.forlife, r.bestalias, r.mailorg2,
                             r.password, r.email, r.naissance, u.nom, u.prenom,
                             u.promo, u.flags
                       FROM  register_pending AS r
                 INNER JOIN  auth_user_md5    AS u ON r.uid = u.user_id
                      WHERE  hash={?} AND hash!='INSCRIT'", $hash);
        }

        if (!$hash || !list($uid, $forlife, $bestalias, $mailorg2, $password, $email,
                            $naissance, $nom, $prenom, $promo, $femme) = $res->fetchOneRow())
        {
            $page->kill("<p>Cette adresse n'existe pas, ou plus, sur le serveur.</p>
                         <p>Causes probables :</p>
                         <ol>
                           <li>Vérifie que tu visites l'adresse du dernier
                               e-mail reçu s'il y en a eu plusieurs.</li>
                           <li>Tu as peut-être mal copié l'adresse reçue par
                               mail, vérifie-la à la main.</li>
                           <li>Tu as peut-être attendu trop longtemps pour
                               confirmer.  Les pré-inscriptions sont annulées
                               tous les 30 jours.</li>
                           <li>Tu es en fait déjà inscrit.</li>
                        </ol>");
        }



        /***********************************************************/
        /****************** REALLY CREATE ACCOUNT ******************/
        /***********************************************************/

        XDB::execute('UPDATE  auth_user_md5
                                   SET  password={?}, perms="user",
                                        date=NOW(), naissance={?}, date_ins = NOW()
                                 WHERE  user_id={?}', $password, $naissance, $uid);
        XDB::execute('REPLACE INTO auth_user_quick (user_id) VALUES ({?})', $uid);
        XDB::execute('INSERT INTO aliases (id,alias,type)
                                     VALUES ({?}, {?}, "a_vie")', $uid,
                                     $forlife);
        XDB::execute('INSERT INTO aliases (id,alias,type,flags)
                                     VALUES ({?}, {?}, "alias", "bestalias")',
                                     $uid, $bestalias);
        if ($mailorg2) {
            XDB::execute('INSERT INTO aliases (id,alias,type)
                                         VALUES ({?}, {?}, "alias")', $uid,
                                         $mailorg2);
        }

        require_once('emails.inc.php');
        $redirect = new Redirect($uid);
        $redirect->add_email($email);

        // on cree un objet logger et on log l'inscription
        $logger = new CoreLogger($uid);
        $logger->log('inscription', $email);

        XDB::execute('UPDATE register_pending SET hash="INSCRIT" WHERE uid={?}', $uid);

        global $platal;
        $platal->on_subscribe($forlife, $uid, $promo, $password);

        $mymail = new PlMailer('register/inscription.reussie.tpl');
        $mymail->assign('forlife', $forlife);
        $mymail->assign('prenom', $prenom);
        $mymail->send();

        if (!start_connexion($uid,false)) {
            return PL_FORBIDDEN;
        }
        $_SESSION['auth'] = AUTH_MDP;

        /***********************************************************/
        /************* envoi d'un mail au démarcheur ***************/
        /***********************************************************/
        $res = XDB::iterRow(
                "SELECT  DISTINCT sa.alias, IF(s.nom_usage,s.nom_usage,s.nom) AS nom,
                         s.prenom, FIND_IN_SET('femme', s.flags) AS femme
                   FROM  register_marketing AS m
             INNER JOIN  auth_user_md5      AS s  ON ( m.sender = s.user_id )
             INNER JOIN  aliases            AS sa ON ( sa.id = m.sender
                                                       AND FIND_IN_SET('bestalias', sa.flags) )
                  WHERE  m.uid = {?}", $uid);
        XDB::execute("UPDATE register_mstats SET success=NOW() WHERE uid={?}", $uid);

        while (list($salias, $snom, $sprenom, $sfemme) = $res->next()) {
            $mymail = new PlMailer();
            $mymail->setSubject("$prenom $nom s'est inscrit à Polytechnique.org !");
            $mymail->setFrom('"Marketing Polytechnique.org" <register@polytechnique.org>');
            $mymail->addTo("\"$sprenom $snom\" <$salias@{$globals->mail->domain}>");
            $msg = ($sfemme?'Chère':'Cher')." $sprenom,\n\n"
                 . "Nous t'écrivons pour t'informer que {$prenom} {$nom} (X{$promo}), "
                 . "que tu avais incité".($femme?'e':'')." à s'inscrire à Polytechnique.org, "
                 . "vient à l'instant de terminer son inscription.\n\n"
                 . "Merci de ta participation active à la reconnaissance de ce site !!!\n\n"
                 . "Bien cordialement,\n"
                 . "L'équipe Polytechnique.org";
            $mymail->setTxtBody(wordwrap($msg, 72));
            $mymail->send();
        }

        /**** send a mail to X.org administrators ****/
        if ($globals->register->notif) {
            $mymail = new PlMailer();
            $mymail->setSubject("Inscription de $prenom $nom (X$promo)");
            $mymail->setFrom('"Webmaster Polytechnique.org" <web@polytechnique.org>');
            $mymail->addTo($globals->register->notif);
            $msg = "$prenom $nom (X$promo) a terminé son inscription avec les données suivantes :\n"
                 . " - nom       : $nom\n"
                 . " - prenom    : $prenom\n"
                 . " - promo     : $promo\n"
                 . " - naissance : $naissance\n"
                 . " - forlife   : $forlife\n"
                 . " - email     : $email\n"
                 . " - sexe      : $femme\n"
                 . " - ip        : " . (@$_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR']
                                                                          : $_SERVER['REMOTE_ADDR']) . "\n"
                 . " - proxy     : " . (@$_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['REMOTE_ADDR'] : "") . "\n";
            $mymail->setTxtBody($msg);
            $mymail->send(); 
        }

        XDB::execute("DELETE FROM register_marketing WHERE uid = {?}", $uid);

        pl_redirect('register/success');
        $page->assign('uid', $uid);
    }

    function handler_success(&$page)
    {
        $page->changeTpl('register/success.tpl');

        $_SESSION['sub_state'] = array('step' => 5);
        if (Env::has('response2'))  {
            $_SESSION['password'] = $password = Post::v('response2');

            XDB::execute('UPDATE auth_user_md5 SET password={?}
                                     WHERE user_id={?}', $password,
                                   S::v('uid'));

            $log =& S::v('log');
            $log->log('passwd', '');

            if (Cookie::v('ORGaccess')) {
                require_once('secure_hash.inc.php');
                setcookie('ORGaccess', hash_encrypt($password), (time()+25920000), '/', '' ,0);
            }

            $page->assign('mdpok', true);
        }

        $page->addJsLink('motdepasse.js');
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
