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

// {{{ function checkId

function checkId(&$subState)
{
    $subState->set('xorgid', Profile::getXorgId($subState->i('schoolid')));
    if (!$subState->v('xorgid')) {
        return 'Le matricule est incorrect.';
    }

    $uf = new UserFilter(new PFC_And(
            new UFC_SchoolId('xorg', $subState->i('xorgid')),
            new PFC_Not(new UFC_Dead())
    ));
    $profile = $uf->getProfile();

    if ($profile->__get('state') == 'active') {
        return "Tu es déjà inscrit ou ton matricule est incorrect !";
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
    $subState->set('birthdateRef', $profile->__get('birthdate_ref'));
    return true;
}

// }}}
// {{{ function checkOldId

function checkOldId(&$subState)
{
    $uf = new UserFilter(new PFC_And(
            new PFC_Not(new UFC_Dead()),
            new UFC_Promo('=', UserFilter::DISPLAY, $subState->s('promo')),
            new PFC_Not(new UFC_Registered(true))
    ));
    $it = $uf->iterProfiles();
    while ($profile = $it->next()) {
        if ($profile->compareNames($subState->s('firstname'), $subState->s('lastname'))) {
            $subState->set('lastname', $profile->lastName());
            $subState->set('firstname', $profile->firstName());
            $subState->set('uid', $profile->owner()->id());
            $subState->set('watch', $profile->owner()->watch);
            $subState->set('birthdateRef', $profile->__get('birthdate_ref'));
            $subState->set('xorgid', $profile->__get('xorg_id'));
            return true;
        }
    }

    $uf = new UserFilter(new PFC_And(
            new PFC_Not(new UFC_Dead()),
            new UFC_Promo('=', UserFilter::DISPLAY, $subState->s('promo')),
            new UFC_Registered(true)
    ));
    $it = $uf->iterProfiles();
    while ($profile = $it->next()) {
        if ($profile->compareNames($subState->s('firstname'), $subState->s('lastname'))) {
            $subState->set('uid', $profile->owner()->id());
            $subState->set('watch', $profile->owner()->watch);
            $subState->set('birthdateRef', $profile->__get('birthdate_ref'));
            $subState->set('xorgid', $profile->__get('xorg_id'));
            return 'Tu es vraisemblablement déjà inscrit !';
        }
    }
    return 'Erreur : vérifie que tu as bien orthographié ton nom !';
}

// }}}
// {{{ function checkNewUser

function checkNewUser(&$subState)
{
    $firstname = preg_replace("/[ \t]+/", ' ', $subState->t('firstname'));
    $firstname = preg_replace("/--+/", '-', $firstname);
    $firstname = preg_replace("/''+/", '\'', $firstname);
    $subState->set('firstname', PlUser::fixFirstnameCase($firstname));

    $lastname = preg_replace("/[ \t]+/", ' ', $subState->t('lastname'));
    $lastname = preg_replace("/--+/", '-', $lastname);
    $lastname = preg_replace("/''+/", '\'', $lastname);
    $subState->set('lastname', mb_strtoupper($lastname));

    if ($subState->i('yearpromo') >= 1996) {
        $res = checkId($subState);
    } else {
        $res = checkOldId($subState);
    }
    if ($res !== true) {
        return $res;
    }

    return true;
}

// }}}
// {{{ function createAliases

function createAliases(&$subState)
{
    global $globals;

    $emailXorg  = PlUser::makeUserName($subState->t('firstname'), $subState->t('lastname'));
    $emailXorg2 = $emailXorg . sprintf(".%02u", ($subState->i('yearpromo') % 100));

    $res = XDB::query("SELECT  hruid
                         FROM  accounts
                        WHERE  uid = {?} AND hruid != ''", $subState->i('uid'));
    if ($res->numRows() == 0) {
        return "Tu n'as pas d'adresse à vie pré-attribuée.<br />"
            . "Envoie un mail à <a href=\"mailto:support@{$globals->mail->domain}</a>\">"
            . "support@{$globals->mail->domain}</a> en expliquant ta situation.";
    } else {
        $forlife = $res->fetchOneCell();
    }

    $res = XDB::query('SELECT  uid, type, expire
                         FROM  aliases
                        WHERE  alias = {?}', $emailXorg);
    if ($res->numRows()) {
        list($h_id, $h_type, $expire) = $res->fetchOneRow();
        if ($h_type != 'homonyme' and empty($expire)) {
            XDB::execute('UPDATE  aliases
                             SET  expire = ADDDATE(NOW(), INTERVAL 1 MONTH)
                           WHERE  alias = {?}', $emailXorg);
            XDB::execute('REPLACE INTO  homonyms (homonyme_id, uid)
                                VALUES  ({?}, {?})', $h_id, $h_id);
            XDB::execute('REPLACE INTO  homonyms (homonyme_id, uid)
                                VALUES  ({?}, {?})', $h_id, $uid);
            $res = XDB::query('SELECT  alias
                                 FROM  aliases
                                WHERE  uid = {?} AND expire IS NULL', $h_id);
            $als = $res->fetchColumn();

            $mailer = new PlMailer('register/lostalias.mail.tpl');
            $mailer->addTo($emailXorg . '@' . $globals->mail->domain);
            $mailer->setSubject("Perte de ton alias $emailXorg dans un mois !");
            $mailer->assign('emailXorg', $emailXorg);
            $mailer->assign('als', join(', ', $als));
            $mailer->SetTxtBody(wordwrap($msg,72));
            $mailer->send();
        }

        $subState->set('forlife', $forlife);
        $subState->set('bestalias', $emailXorg2);
        $subState->set('emailXorg2', null);
    } else {
        $subState->set('forlife', $forlife);
        $subState->set('bestalias', $emailXorg);
        $subState->set('emailXorg2', $emailXorg2);
    }

    return true;
}

// }}}
// {{{ function finishRegistration

function finishRegistration($subState)
{
    global $globals;

    $hash = rand_url_id(12);
    XDB::execute('REPLACE INTO  register_pending (uid, forlife, bestalias, mailorg2, password,
                                                  email, date, relance, naissance, hash, services)
                        VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, NOW(), 0, {?}, {?}, {?})',
                 $subState->i('uid'), $subState->s('forlife'), $subState->s('bestalias'),
                 $subState->s('emailXorg2'), $subState->s('password'), $subState->s('email'),
                 $subState->s('birthdate'), $hash, implode(',', $subState->v('services')));

    $mymail = new PlMailer('register/end.mail.tpl');
    $mymail->assign('emailXorg', $subState->s('bestalias'));
    $mymail->assign('to', $subState->s('email'));
    $mymail->assign('baseurl', $globals->baseurl);
    $mymail->assign('hash', $hash);
    $mymail->assign('subject', $subState->s('bestalias') . '@' . $globals->mail->domain);
    $mymail->send();
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
