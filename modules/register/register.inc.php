<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

// {{{ function strongCheckId

function strongCheckId($subState)
{
    $subState->set('xorgid', Profile::getXorgId($subState->i('schoolid')));
    if (!$subState->v('xorgid')) {
        return 'Le matricule est incorrect.';
    }

    $uf = new UserFilter(new PFC_And(
            new UFC_SchoolId('xorg', $subState->i('xorgid')),
            new PFC_Not(new UFC_Dead()),
            new PFC_Not(new UFC_Registered(true))
    ));
    $profile = $uf->getProfile();
    if (is_null($profile)) {
        $max_promo_year = XDB::fetchOneCell("SELECT  MAX(promo_year)
                                               FROM  profile_education
                                              WHERE  FIND_IN_SET(flags, 'primary')
                                                     AND eduid = (
                                                         SELECT  id
                                                         FROM    profile_education_enum
                                                         WHERE   abbreviation='X'
                                                     )
                                                     AND degreeid = (
                                                         SELECT  id
                                                         FROM    profile_education_degree_enum
                                                         WHERE   abbreviation = 'Ing.'
                                                     )");
        if ($subState->i('yearpromo') > $max_promo_year) {
            return "Ta promo n'est pas encore dans l'annuaire, réessaie dans quelques semaines.";
        }
        return 'Cherche ton nom dans l\'annuaire public. Si tu te trouves, tu es déjà inscrit ou ton matricule est incorrect ! Sinon regarde la <a href="https://auth.polytechnique.org/faq">FAQ</a> ou envoie-nous un email à support@polytechnique.org';
    }

    if ($profile->promo() != $subState->s('promo')) {
        return 'Le matricule est incorrect.';
    }

    if (!$profile->compareNames($subState->s('firstname'), $subState->s('lastname'))) {
        return "Erreur dans l'identification. Réessaie, il y a une erreur quelque part !";
    }

    $subState->set('lastname', $profile->lastName());
    $subState->set('firstname', $profile->firstName());
    $subState->set('uid', $profile->owner()->id());
    $subState->set('watch', $profile->owner()->watch);
    $subState->set('comment', $profile->owner()->comment);
    $subState->set('birthdateRef', $profile->__get('birthdate_ref'));
    return true;
}

// }}}
// {{{ function weakCheckId

function weakCheckId($subState)
{
    $uf = new UserFilter(new PFC_And(
            new PFC_Not(new UFC_Dead()),
            new UFC_Promo('=', $subState->s('edu_type'), $subState->s('yearpromo')),
            new PFC_Not(new UFC_Registered(true))
    ));
    if ($it = $uf->iterProfiles()) {
        while ($profile = $it->next()) {
            if ($profile->compareNames($subState->s('firstname'), $subState->s('lastname'))) {
                $subState->set('lastname', $profile->lastName());
                $subState->set('firstname', $profile->firstName());
                $subState->set('uid', $profile->owner()->id());
                $subState->set('watch', $profile->owner()->watch);
                $subState->set('comment', $profile->owner()->comment);
                $subState->set('birthdateRef', $profile->__get('birthdate_ref'));
                $subState->set('xorgid', $profile->__get('xorg_id'));
                return true;
            }
        }
    }

    $uf = new UserFilter(new PFC_And(
            new PFC_Not(new UFC_Dead()),
            new UFC_Promo('=', $subState->s('edu_type'), $subState->s('yearpromo')),
            new UFC_Registered(true)
    ));
    if ($it = $uf->iterProfiles()) {
        while ($profile = $it->next()) {
            if ($profile->compareNames($subState->s('firstname'), $subState->s('lastname'))) {
                $subState->set('uid', $profile->owner()->id());
                $subState->set('watch', $profile->owner()->watch);
                $subState->set('comment', $profile->owner()->comment);
                $subState->set('birthdateRef', $profile->__get('birthdate_ref'));
                $subState->set('xorgid', $profile->__get('xorg_id'));
                return 'Tu es vraisemblablement déjà inscrit !';
            }
        }
    }
    return 'Nous n\'avons pas les mêmes informations (promo, accents…) que toi ! Il arrive que cela soit dû au fait que l\'École transmette les noms de famille en majuscule, enlevant ainsi les accents. Tu peux vérifier en cherchant ta fiche sur l\'annuaire public, si ça ne fonctionne toujours pas tu peux regarder la <a href="https://auth.polytechnique.org/faq">FAQ</a> ou nous envoyer un email à support@polytechnique.org';
}

// }}}
// {{{ function checkNewUser

function checkNewUser($subState)
{
    $firstname = preg_replace("/[ \t]+/", ' ', $subState->t('firstname'));
    $firstname = preg_replace("/--+/", '-', $firstname);
    $firstname = preg_replace("/''+/", '\'', $firstname);
    $subState->set('firstname', PlUser::fixFirstnameCase($firstname));

    $lastname = preg_replace("/[ \t]+/", ' ', $subState->t('lastname'));
    $lastname = preg_replace("/--+/", '-', $lastname);
    $lastname = preg_replace("/''+/", '\'', $lastname);
    $subState->set('lastname', mb_strtoupper($lastname));

    if ($subState->v('edu_type') == Profile::DEGREE_X && $subState->i('yearpromo') >= 1996) {
        $res = strongCheckId($subState);
    } else {
        $res = weakCheckId($subState);
    }
    if ($res !== true) {
        return $res;
    }

    return true;
}

// }}}
// {{{ function createAliases

function createAliases($subState)
{
    global $globals;

    $res = XDB::query("SELECT  hruid, state, type
                         FROM  accounts
                        WHERE  uid = {?} AND hruid != ''",
                      $subState->i('uid'));
    if ($res->numRows() == 0) {
        return "Tu n'as pas d'adresse à vie pré-attribuée.<br />"
            . "Envoie un mail à <a href=\"mailto:support@{$globals->mail->domain}\">"
            . "support@{$globals->mail->domain}</a> en expliquant ta situation.";
    } else {
        list($forlife, $state, $type) = $res->fetchOneRow();
    }
    if ($state == 'active') {
        return "Tu es déjà inscrit, si tu ne te souviens plus de ton mot de passe d'accès au site, "
             . "tu peux suivre <a href=\"recovery\">la procédure de récupération de mot de passe</a>.";
    } else if ($state == 'disabled') {
        return "Ton compte a été désactivé par les administrateurs du site suite à des abus. "
             . "Pour plus d'information ou pour demander la réactivation du compte, tu peux t'adresser à "
             . "<a href=\"mailto:support@{$globals->mail->domain}\">support@{$globals->mail->domain}</a>.";
    }


    $emailXorg  = PlUser::makeUserName($subState->t('firstname'), $subState->t('lastname'));
    $suffix = User::$study_letter[$type] . substr($subState->v('yearpromo'), -2);
    $emailXorg2 = $emailXorg . '.' . $suffix;
    // Search for homonyms:
    //  * first case: only one homonym already registered.
    $res = XDB::query('SELECT  uid, expire
                         FROM  email_source_account
                        WHERE  email = {?} AND type != \'alias_aux\'',
                      $emailXorg);
    //  * second case: at least two homonyms registerd.
    $result = XDB::query("SELECT  hrmid
                            FROM  email_source_other
                           WHERE  type = 'homonym' AND email = {?}",
                         $emailXorg);
    if ($res->numRows() || $result->numRows()) {
        if ($res->numRows()) {
            list($h_id, $expire) = $res->fetchOneRow();
            if (empty($expire)) {
                XDB::execute('UPDATE  email_source_account
                                 SET  expire = ADDDATE(NOW(), INTERVAL 1 MONTH)
                               WHERE  email = {?} AND type != \'alias_aux\'',
                             $emailXorg);
                $hrmid = User::makeHomonymHrmid($emailXorg);
                XDB::execute('INSERT IGNORE INTO  homonyms_list (hrmid, uid)
                                          VALUES  ({?}, {?}), ({?}, {?})',
                             $hrmid, $h_id, $hrmid, $subState->i('uid'));
                $als = XDB::fetchColumn('SELECT  email
                                           FROM  email_source_account
                                          WHERE  uid = {?} AND type != \'alias_aux\' AND expire IS NULL',
                                        $h_id);

                $homonym = User::getSilentWithUID($h_id);
                $mailer = new PlMailer('register/lostalias.mail.tpl');
                $mailer->addTo($homonym);
                $mailer->setSubject("Perte de ton alias $emailXorg dans un mois !");
                $mailer->assign('emailXorg', $emailXorg);
                $mailer->assign('als', join(', ', $als));
                $mailer->SetTxtBody(wordwrap($msg,72));
                $mailer->send();
            }
        } else {
            $hrmid = $result->fetchOneCell();
            XDB::execute('INSERT IGNORE INTO  homonyms_list (hrmid, uid)
                                      VALUES  ({?}, {?})',
                         $hrmid, $subState->i('uid'));
            // TODO: insert into source_other if new domain
        }

        $subState->set('forlife', $forlife);
        $subState->set('bestalias', $emailXorg2);
        $subState->set('emailXorg2', null);
    } else {
        $subState->set('forlife', $forlife);
        $subState->set('bestalias', $emailXorg);
        $subState->set('emailXorg2', $emailXorg2);
    }
    $subState->set('main_mail_domain', User::$sub_mail_domains[$type] . Platal::globals()->mail->domain);

    return true;
}

// }}}
// {{{ function finishRegistration

function finishRegistration($subState)
{
    global $globals;

    $hash = rand_url_id(12);
    XDB::execute('INSERT INTO  register_pending (uid, forlife, bestalias, mailorg2, password,
                                                 email, date, relance, naissance, hash, services)
                              VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, NOW(), 0, {?}, {?}, {?})
                              ON DUPLICATE KEY UPDATE password=VALUES(password), email=VALUES(email),
                                                      date=VALUES(date), naissance=VALUES(naissance),
                                                      hash=VALUES(hash), services=VALUES(services)',
                 $subState->i('uid'), $subState->s('forlife'), $subState->s('bestalias'),
                 $subState->s('emailXorg2'), $subState->s('password'), $subState->s('email'),
                 $subState->s('birthdate'), $hash, implode(',', $subState->v('services')));

    $mymail = new PlMailer('register/end.mail.tpl');
    $mymail->assign('emailXorg', $subState->s('bestalias'));
    $mymail->assign('to', $subState->s('email'));
    $mymail->assign('baseurl', $globals->baseurl);
    $mymail->assign('hash', $hash);
    $mymail->assign('subject', ucfirst($globals->mail->domain) . ' : ' . $subState->s('bestalias'));
    $mymail->send();
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
