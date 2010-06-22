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

class AXLetterModule extends PLModule
{
    function handlers()
    {
        return array(
            'ax'             => $this->make_hook('index',  AUTH_COOKIE),
            'ax/out'         => $this->make_hook('out',    AUTH_PUBLIC),
            'ax/show'        => $this->make_hook('show',   AUTH_COOKIE),
            'ax/edit'        => $this->make_hook('submit', AUTH_MDP),
            'ax/edit/cancel' => $this->make_hook('cancel', AUTH_MDP),
            'ax/edit/valid'  => $this->make_hook('valid',  AUTH_MDP),
            'admin/axletter' => $this->make_hook('admin',  AUTH_MDP, 'admin'),
        );
    }

    function handler_out(&$page, $hash = null)
    {
        if (!$hash) {
            if (!S::logged()) {
                return PL_DO_AUTH;
            } else {
                return $this->handler_index($page, 'out');
            }
        }
        $this->load('axletter.inc.php');
        $page->changeTpl('axletter/unsubscribe.tpl');
        $page->assign('success', AXLetter::unsubscribe($hash, true));
    }

    function handler_index(&$page, $action = null)
    {
        $this->load('axletter.inc.php');

        $page->changeTpl('axletter/index.tpl');
        $page->setTitle('Envois de l\'AX');

        switch ($action) {
          case 'in':  AXLetter::subscribe(); break;
          case 'out': AXLetter::unsubscribe(); break;
        }

        $perm = AXLetter::hasPerms();
        if ($perm) {
            $res = XDB::query("SELECT * FROM axletter_ins");
            $page->assign('count', $res->numRows());
            $page->assign('new', AXLetter::awaiting());
        }
        $page->assign('axs', AXLetter::subscriptionState());
        $page->assign('ax_list', AXLetter::listSent());
        $page->assign('ax_rights', $perm);
    }

    function handler_submit(&$page, $action = null)
    {
        $this->load('axletter.inc.php');
        if (!AXLetter::hasPerms()) {
            return PL_FORBIDDEN;
        }

        $page->changeTpl('axletter/edit.tpl');

        $saved      = Post::i('saved');
        $new        = false;
        $id         = Post::i('id');
        $short_name = trim(Post::v('short_name'));
        $subject    = trim(Post::v('subject'));
        $title      = trim(Post::v('title'));
        $body       = rtrim(Post::v('body'));
        $signature  = trim(Post::v('signature'));
        $promo_min  = Post::i('promo_min');
        $promo_max  = Post::i('promo_max');
        $subset_to  = preg_split("/[ ,;\:\n\r]+/", Post::v('subset_to'), -1, PREG_SPLIT_NO_EMPTY);
        $subset     = (count($subset_to) > 0);
        $subset_rm  = Post::b('subset_rm');
        $echeance   = Post::has('echeance_date') ?
              preg_replace('/^(\d\d\d\d)(\d\d)(\d\d)$/', '\1-\2-\3', Post::v('echeance_date')) . ' ' . Post::v('echeance_time')
            : Post::v('echeance');
        $echeance_date = Post::v('echeance_date');
        $echeance_time = Post::v('echeance_time');

        if (!$id) {
            $res = XDB::query("SELECT * FROM axletter WHERE FIND_IN_SET('new', bits)");
            if ($res->numRows()) {
                extract($res->fetchOneAssoc(), EXTR_OVERWRITE);
                $subset_to = ($subset ? explode("\n", $subset) : array());
                $subset = (count($subset_to) > 0);
                $saved = true;
            } else  {
                XDB::execute("INSERT INTO axletter SET id = NULL");
                $id  = XDB::insertId();
            }
            if (!$echeance || $echeance == '0000-00-00 00:00:00') {
                $saved = false;
                $new   = true;
            }
        } elseif (Post::has('valid')) {
            S::assert_xsrf_token();

            if (!$subject && $title) {
                $subject = $title;
            }
            if (!$title && $subject) {
                $title = $subject;
            }
            if (!$subject || !$title || !$body) {
                $page->trigError("L'article doit avoir un sujet et un contenu");
                Post::kill('valid');
            }
            if (($promo_min > $promo_max && $promo_max != 0)||
                ($promo_min != 0 && ($promo_min <= 1900 || $promo_min >= 2020)) ||
                ($promo_max != 0 && ($promo_max <= 1900 || $promo_max >= 2020)))
            {
                $page->trigError("L'intervalle de promotions n'est pas valide");
                Post::kill('valid');
            }
            if (empty($short_name)) {
                $page->trigError("L'annonce doit avoir un nom raccourci pour simplifier la navigation dans les archives");
                Post::kill('valid');
            } elseif (!preg_match('/^[a-z][-a-z0-9]*[a-z0-9]$/', $short_name)) {
                $page->trigError("Le nom raccourci n'est pas valide, il doit comporter au moins 2 caractères et n'être composé "
                          . "que de chiffres, lettres et tirets");
                Post::kill('valid');
            } elseif ($short_name != Post::v('old_short_name')) {
                $res = XDB::query("SELECT id FROM axletter WHERE short_name = {?}", $short_name);
                if ($res->numRows() && $res->fetchOneCell() != $id) {
                    $page->trigError("Le nom $short_name est déjà utilisé, merci d'en choisir un autre");
                    $short_name = Post::v('old_short_name');
                    if (empty($short_name)) {
                        Post::kill('valid');
                    }
                }
            }

            switch (@Post::v('valid')) {
              case 'Vérifier les emails':
                // Same as 'preview', but performs a test of all provided emails
                if ($subset) {
                    require_once 'emails.inc.php';
                    $ids = ids_from_mails($subset_to);
                    $nb_error = 0;
                    foreach ($subset_to as $e) {
                        if (!array_key_exists($e, $ids)) {
                            if ($nb_error == 0) {
                                $page->trigError("Emails inconnus :");
                            }
                            $nb_error++;
                            $page->trigError($e);
                        }
                    }
                    if ($nb_error == 0) {
                        if (count($subset_to) == 1) {
                            $page->trigSuccess("L'email soumis a été reconnu avec succès.");
                        } else {
                            $page->trigSuccess("Les " . count($subset_to) . " emails soumis ont été reconnus avec succès.");
                        }
                    } else {
                        $page->trigError("Total : $nb_error erreur" . ($nb_error > 1 ? "s" : "") . " sur " . count($subset_to) . " adresses mail soumises.");
                    }
                    $page->trigSuccess("Les adresses soumises correspondent à un total de " . count(array_unique($ids)) . " camarades.");
                }
                // XXX : no break here, since Vérifier is a subcase of Aperçu.
              case 'Aperçu':
                $this->load('axletter.inc.php');
                $al = new AXLetter(array($id, $short_name, $subject, $title, $body, $signature,
                                         $promo_min, $promo_max, $subset, $subset_rm, $echeance, 0, 'new'));
                $al->toHtml($page, S::user());
                break;

              case 'Confirmer':
                XDB::execute("REPLACE INTO  axletter
                                       SET  id = {?}, short_name = {?}, subject = {?}, title = {?}, body = {?},
                                            signature = {?}, promo_min = {?}, promo_max = {?}, echeance = {?}, subset = {?}, subset_rm = {?}",
                             $id, $short_name, $subject, $title, $body, $signature, $promo_min, $promo_max, $echeance, $subset ? implode("\n", $subset_to) : null, $subset_rm);
                if (!$saved) {
                    global $globals;
                    $mailer = new PlMailer();
                    $mailer->setFrom("support@" . $globals->mail->domain);
                    $mailer->setSubject("Un nouveau projet d'email de l'AX vient d'être proposé");
                    $mailer->setTxtBody("Un nouvel email vient d'être rédigé en prévision d'un envoi prochain. Vous pouvez "
                                      . "le modifier jusqu'à ce qu'il soit verrouillé pour l'envoi\n\n"
                                      . "Le sujet de l'email : $subject\n"
                                      . "L'échéance d'envoi est fixée à $echeance.\n"
                                      . "L'email pourra néanmoins partir avant cette échéance si un administrateur de "
                                      . "Polytechnique.org le valide.\n\n"
                                      . "Pour modifier, valider ou annuler l'email :\n"
                                      . "https://www.polytechnique.org/ax/edit\n"
                                      . "-- \n"
                                      . "Association Polytechnique.org\n");
                    $users = User::getBulkUsersWithUIDs(XDB::fetchColumn('SELECT  uid
                                                                            FROM  axletter_rights'));
                    foreach ($users as $user) {
                        $mailer->addTo($user);
                    }
                    $mailer->send();
                }
                $saved = true;
                $echeance_date = null;
                $echeance_time = null;
                pl_redirect('ax');
                break;
            }
        }
        $page->assign('id', $id);
        $page->assign('short_name', $short_name);
        $page->assign('subject', $subject);
        $page->assign('title', $title);
        $page->assign('body', $body);
        $page->assign('signature', $signature);
        $page->assign('promo_min', $promo_min);
        $page->assign('promo_max', $promo_max);
        $page->assign('subset_to', implode("\n", $subset_to));
        $page->assign('subset', $subset);
        $page->assign('subset_rm', $subset_rm);
        $page->assign('echeance', $echeance);
        $page->assign('echeance_date', $echeance_date);
        $page->assign('echeance_time', $echeance_time);
        $page->assign('saved', $saved);
        $page->assign('new', $new);
        $page->assign('is_xorg', S::admin());

        if (!$saved) {
            $select = '';
            for ($i = 0 ; $i < 24 ; $i++) {
                $stamp = sprintf('%02d:00:00', $i);
                if ($stamp == $echeance_time) {
                    $sel = ' selected="selected"';
                } else {
                    $sel = '';
                }
                $select .= "<option value=\"$stamp\"$sel>{$i}h</option>\n";
            }
            $page->assign('echeance_time', $select);
        }
    }

    function handler_cancel(&$page, $force = null)
    {
        $this->load('axletter.inc.php');
        if (!AXLetter::hasPerms() || !S::has_xsrf_token()) {
            return PL_FORBIDDEN;
        }

        $al = AXLetter::awaiting();
        if (!$al) {
            $page->kill("Aucune lettre en attente");
            return;
        }
        if (!$al->invalid()) {
            $page->kill("Une erreur est survenue lors de l'annulation de l'envoi");
            return;
        }

        $page->killSuccess("L'envoi de l'annonce {$al->title()} est annulé.");
    }

    function handler_valid(&$page, $force = null)
    {
        $this->load('axletter.inc.php');
        if (!AXLetter::hasPerms() || !S::has_xsrf_token()) {
            return PL_FORBIDDEN;
        }

        $al = AXLetter::awaiting();
        if (!$al) {
            $page->kill("Aucune lettre en attente");
            return;
        }
        if (!$al->valid()) {
            $page->kill("Une erreur est survenue lors de la validation de l'envoi");
            return;
        }

        $page->killSuccess("L'envoi de l'annonce aura lieu dans l'heure qui vient.");
    }

    function handler_show(&$page, $nid = 'last')
    {
        $this->load('axletter.inc.php');
        $page->changeTpl('axletter/show.tpl');

        try {
            $nl = new AXLetter($nid);
            $user =& S::user();
            if (Get::has('text')) {
                $nl->toText($page, $user);
            } else {
                $nl->toHtml($page, $user);
            }
            if (Post::has('send')) {
                $nl->sendTo($user);
            }
        } catch (MailNotFound $e) {
            return PL_NOT_FOUND;
        }
    }

    function handler_admin(&$page, $action = null, $uid = null)
    {
        $this->load('axletter.inc.php');
        if (Post::has('action')) {
            $action = Post::v('action');
            $uid    = Post::v('uid');
        }
        if ($uid) {
            S::assert_xsrf_token();

            $uids   = preg_split('/ *[,;\: ] */', $uid);
            foreach ($uids as $uid) {
                switch ($action) {
                  case 'add':
                    $res = AXLetter::grantPerms($uid);
                    break;
                  case 'del';
                    $res = AXLetter::revokePerms($uid);
                    break;
                }
                if (!$res) {
                    $page->trigError("Personne ne correspond à l'identifiant '$uid'");
                }
            }
        }

        $page->changeTpl('axletter/admin.tpl');
        $page->assign('admins', User::getBulkUsersWithUIDs(XDB::fetchColumn('SELECT  uid
                                                                               FROM  axletter_rights')));

        $importer = new CSVImporter('axletter_ins');
        $importer->registerFunction('uid', 'email vers Id X.org', array($this, 'idFromMail'));
        $importer->forceValue('hash', array($this, 'createHash'));
        $importer->apply($page, "admin/axletter", array('uid', 'email', 'prenom', 'nom', 'promo', 'flag', 'hash'));
    }

    function idFromMail($line, $key, $relation = null)
    {
        static $field;
        global $globals;
        if (!isset($field)) {
            $field = array('email', 'mail', 'login', 'bestalias', 'forlife', 'flag');
            foreach ($field as $fld) {
                if (isset($line[$fld])) {
                    $field = $fld;
                    break;
                }
            }
        }
        $uf = new UserFilter(new UFC_Email($line[$field]));
        $id = $uf->getUIDs();
        return count($id) == 1 ? $id[0] : 0;
    }

    function createHash($line, $key, $relation)
    {
        $hash = implode(time(), $line) . rand();
        $hash = md5($hash);
        return $hash;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
