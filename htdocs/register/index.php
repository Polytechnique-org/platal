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

require_once('xorg.inc.php');

$sub_state = Session::getMixed('sub_state', Array());
if (!isset($sub_state['step'])) {
    $sub_state['step'] = 0;
}
if (Get::has('back') && Get::getInt('back') < $sub_state['step']) {
    $sub_state['step'] = max(0,Get::getInt('back'));
}

if (Env::has('hash')) {
    $res = $globals->xdb->query(
            "SELECT  m.uid, u.promo, u.prenom, u.nom, u.matricule
               FROM  register_marketing AS m
         INNER JOIN  auth_user_md5      AS u ON u.user_id = m.uid
              WHERE  m.hash={?}", Env::get('hash'));
    if (list($uid, $promo, $nom, $prenom, $ourmat) = $res->fetchOneRow()) {
        $sub_state['hash']   = Env::get('hash');
        $sub_state['promo']  = $promo;
        $sub_state['nom']    = $nom;
        $sub_state['prenom'] = $prenom;
        $sub_state['ourmat'] = $ourmat;

        $globals->xdb->execute(
                "REPLACE INTO  register_mstats (uid,sender,success)
                       SELECT  m.uid, m.sender, NOW()
                         FROM  register_marketing AS m
                        WHERE  m.hash", $sub_state['hash']);
    }
}

switch ($sub_state['step']) {
    case 0:
        if (Post::has('step1')) {
            $sub_state['step'] = 1;
            if (isset($sub_date['hash'])) {
                $sub_state['step'] = 3;
                create_aliases($sub_state);
            }
        }
        break;

    case 1:
        if (Post::has('promo')) {
            $promo = Post::getInt('promo');
            if ($promo < 1900 || $promo > date('Y')) {
                $err = "La promotion saisie est incorrecte !";
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
            require_once('register.inc.php');
            $sub_state['prenom'] = Post::get('prenom');
            $sub_state['nom']    = Post::get('nom');
            $sub_state['mat']    = Post::get('mat');
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
            require_once('register.inc.php');
            if (!isvalid_email(Post::get('email'))) {
                $err[] = "Le champ 'E-mail' n'est pas valide.";
            } elseif (!isvalid_email_redirection(Post::get('email'))) {
                $err[] = $sub_state['forlife']." doit renvoyer vers un email existant ".
                    "valide, en particulier, il ne peut pas être renvoyé vers lui-même.";
            }
            if (!preg_match('/^[0-3][0-9][01][0-9][12][90][0-9][0-9]$/', Post::get('naissance'))) {
                $err[] = "La 'Date de naissance' n'est pas correcte.";
            }

            if (isset($err)) {
                $err = join('<br />', $err);
            } else {
                $birth = sprintf("%s-%s-%s", substr(Env::get('naissance'),4,4),
                        substr(Env::get('naissance'),2,2), substr(Env::get('naissance'),0,2));
                $sub_state['step']      = 4;
                $sub_state['email']     = Post::get('email');
                $sub_state['naissance'] = $birth;
                finish_ins($sub_state);
            }
        }
        break;
}

$_SESSION['sub_state'] = $sub_state;
new_skinned_page('register/step'.intval($sub_state['step']).'.tpl', AUTH_PUBLIC);
if (isset($err)) { $page->trig($err); }
$page->run();
?>
