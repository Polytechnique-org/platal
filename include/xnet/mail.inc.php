<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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

require_once 'diogenes/diogenes.hermes.inc.php';

// {{{ get_all_redirects

function get_all_redirects($membres, $mls, &$client)
{
    global $globals;
    
    $tos = array();
    
    if ($membres) {
        $res = $globals->xdb->query(
                    'SELECT  IF(u.nom <> "", u.nom, m.nom) AS nom,
                             IF(u.prenom <> "", u.prenom, m.prenom) AS prenom,
                             IF(m.email <> "", m.email, CONCAT(a.alias, "@polytechnique.org")) as email,
                             FIND_IN_SET("femme", u.flags) AS sexe
                       FROM  groupex.membres AS m
                  LEFT JOIN  auth_user_md5   AS u ON (m.uid=u.user_id AND m.uid<50000)
                  LEFT JOIN  aliases         AS a ON (a.id=u.user_id and a.type="a_vie")
                      WHERE  asso_id = {?}', $globals->asso('id'));
        $tos = $res->fetchAllAssoc();
    }

    foreach ($mls as $ml) {
        if (list(,$members) = $client->get_members($ml)) {
            foreach ($members as $mem) {
                list($m, $dom) = explode('@',$mem[1]);
                if ($dom == $globals->mail->domain || $dom == $globals->mail->domain2) {
                    $res = $globals->xdb->query('SELECT  prenom, nom, FIND_IN_SET("femme", u.flags) AS sexe
                                                   FROM  auth_user_md5 AS u
                                             INNER JOIN  aliases AS a ON u.user_id = a.id
                                                  WHERE  a.alias = {?}', $m);
                    if ($person = $res->fetchOneAssoc()) {
                        $person['email'] = $mem[1];
                        $tos[] = $person;
                    }
                } else {
                    $res = $globals->xdb->query('SELECT prenom, nom FROM groupex.membres WHERE email={?}', $mem);
                    if ($person = $res->fetchOneAssoc()) {
                        $person['email'] = $mem[1];
                        $tos[] = $person;
                    } else {
                        $tos[] = array('email' => $mem[1]);
                    }
                }
            }
        }
    }

    return $tos;
}

// }}}
// {{{ _send_xnet_mail

function _send_xnet_mail($user, $body, $mailer)
{
    $cher = isset($user['sexe']) ? ($user['sexe'] ? 'Chère' : 'Cher') : 'Cher(e)';
    $nom  = isset($user['nom']) ? $user['nom'] : "";
    $pnom = isset($user['prenom']) ? $user['prenom'] : preg_replace('!@.*!', '', $user['email']);
    $to   = isset($user['prenom']) ? "\"{$user['prenom']} {$user['nom']}\" <{$user['email']}>" : $user['email'];
    
    $text = $body;
    $text = preg_replace('!<cher>!i',   $cher, $text);
    $text = preg_replace('!<nom>!i',    $nom,  $text);
    $text = preg_replace('!<prenom>!i', $pnom, $text);

    $mailer->addHeader('To', $to);
    $mailer->setTxtBody(wordwrap($text, 72));
    $mailer->send();
}

// }}}
// {{{ send_xnet_mails

function send_xnet_mails($from, $sujet, $body, $tos)
{
    $sent = array();

    $mailer = new HermesMailer();
    $mailer->setSubject($sujet);
    $mailer->setFrom($from);

    foreach ($tos as $user) {
        if ($sent[$user['email']]) continue;
        _send_xnet_mail($user, $body, $mailer);
        $sent[$user['email']] = true;
    }
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
