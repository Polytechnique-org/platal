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

// {{{ function mark_send_mail

function mark_send_mail($uid, $email)
{
    global $globals;

    $hash = rand_url_id(12);
    $globals->xdb->execute('UPDATE register_marketing SET nb=nb+1,hash={?},last=NOW() WHERE uid={?} AND email={?}', $hash, $id, $email);

    // TODO HERE SEND A MARKETING MAIL
}

// }}}
// {{{ function relance

function relance($uid, $nbx = -1)
{
    require_once('xorg.mailer.inc.php');
    global $globals;

    if ($nbx < 0) {
        $res = $globals->xdb->query("SELECT COUNT(*) FROM auth_user_md5 WHERE deces=0");
        $nbx = $res->fetchOneCell();
    }

    $res = $globals->xdb->query(
            "SELECT  r.date, u.promo, u.nom, u.prenom, r.email, r.bestalias
               FROM  register_pending AS r
         INNER JOIN  auth_user_md5    AS u ON u.user_id = r.uid
              WHERE  hash!='INSCRIT' AND uid={?} AND TO_DAYS(relance) < TO_DAYS(NOW())", $uid);
    if (!list($date, $promo, $nom, $prenom, $email, $alias) = $res->fetchOneRow()) {
        return false;
    }

    $hash     = rand_url_id(12);
    $pass     = rand_pass();
    $pass_md5 = md5($pass);
    $fdate    = strftime('%d %B %Y', strtotime($date));
    
    $mymail = new XOrgMailer('marketing.relance.tpl');
    $mymail->assign('nbdix',      $nbx);
    $mymail->assign('fdate',      $fdate);
    $mymail->assign('lusername',  $alias);
    $mymail->assign('nveau_pass', $pass);
    $mymail->assign('baseurl',    $globals->baseurl);
    $mymail->assign('lins_id',    $hash);
    $mymail->assign('lemail',     $email);
    $mymail->assign('subj',       $alias.'@'.$globals->mail->domain);
    $mymail->send();
    $globals->xdb->execute('UPDATE register_pending SET hash={?}, password={?}, relance=NOW() WHERE uid={?}', $hash, $pass_md5, $uid);

    return "$prenom $nom ($promo)";
}

// }}}
?>
