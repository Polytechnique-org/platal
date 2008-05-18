<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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
            'ax'             => $this->make_hook('index',        AUTH_COOKIE),
            'ax/out'         => $this->make_hook('out',    AUTH_PUBLIC),
            'ax/show'        => $this->make_hook('show',   AUTH_COOKIE),
            'ax/edit'        => $this->make_hook('submit', AUTH_MDP),
            'ax/edit/cancel' => $this->make_hook('cancel', AUTH_MDP),
            'ax/edit/valid'  => $this->make_hook('valid',  AUTH_MDP),
            'admin/axletter' => $this->make_hook('admin', AUTH_MDP, 'admin'),
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
        require_once dirname(__FILE__) . '/axletter/axletter.inc.php';
        $page->changeTpl('axletter/unsubscribe.tpl');
        $page->assign('success', AXLetter::unsubscribe($hash, true));
    }

    function handler_index(&$page, $action = null)
    {
        require_once dirname(__FILE__) . '/axletter/axletter.inc.php';

        $page->changeTpl('axletter/index.tpl');
        $page->assign('xorg_title','Polytechnique.org - Envois de l\'AX');

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
        require_once dirname(__FILE__) . '/axletter/axletter.inc.php';
        if (!AXLetter::hasPerms()) {
            return PL_FORBIDDEN;
        }

        $page->changeTpl('axletter/edit.tpl');

        $saved     = Post::i('saved');
        $new       = false;
        $id        = Post::i('id');
        $shortname = trim(Post::v('shortname'));
        $subject   = trim(Post::v('subject'));
        $title     = trim(Post::v('title'));
        $body      = rtrim(Post::v('body'));
        $signature = trim(Post::v('signature'));
        $promo_min = Post::i('promo_min');
        $promo_max = Post::i('promo_max');
        $echeance  = Post::has('echeance_date') ? Post::v('echeance_date') . ' ' . Post::v('echeance_time')
                                                : Post::v('echeance');
        $echeance_date = Post::v('echeance_date');
        $echeance_time = Post::v('echeance_time');

        if (!$id) {
            $res = XDB::query("SELECT * FROM axletter WHERE FIND_IN_SET('new', bits)");
            if ($res->numRows()) {
                extract($res->fetchOneAssoc(), EXTR_OVERWRITE);
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
            if (!$subject && $title) {
                $subject = $title;
            }
            if (!$title && $subject) {
                $title = $subject;
            }
            if (!$subject || !$title || !$body) {
                $page->trig("L'article doit avoir un sujet et un contenu");
                Post::kill('valid');
            }
            if (($promo_min > $promo_max && $promo_max != 0)||
                ($promo_min != 0 && ($promo_min <= 1900 || $promo_min >= 2020)) ||
                ($promo_max != 0 && ($promo_max <= 1900 || $promo_max >= 2020)))
            {
                $page->trig("L'intervalle de promotions n'est pas valide");
                Post::kill('valid');
            }
            if (empty($shortname)) {
                $page->trig("L'annonce doit avoir un nom raccourci pour simplifier la navigation dans les archives");
                Post::kill('valid');
            } elseif (!preg_match('/^[a-z][-a-z0-9]*[a-z0-9]$/', $shortname)) {
                $page->trig("Le nom raccourci n'est pas valide, il doit comporter au moins 2 caractères et n'être composé "
                          . "que de chiffres, lettres et tirets");
                Post::kill('valid');
            } elseif ($shortname != Post::v('old_shortname')) {
                $res = XDB::query("SELECT id FROM axletter WHERE short_name = {?}", $shortname);
                if ($res->numRows() && $res->fetchOneCell() != $id) {
                    $page->trig("Le nom $shortname est déjà utilisé, merci d'en choisir un autre");
                    $shortname = Post::v('old_shortname');
                    if (empty($shortname)) {
                        Post::kill('valid');
                    }
                }
            }

            switch (@Post::v('valid')) {
              case 'Aperçu':
                require_once dirname(__FILE__) . '/axletter/axletter.inc.php';
                $al = new AXLetter(array($id, $shortname, $subject, $title, $body, $signature,
                                         $promo_min, $promo_max, $echeance, 0, 'new'));
                $al->toHtml($page, S::v('prenom'), S::v('nom'), S::v('femme'));
                break;

              case 'Confirmer':
                XDB::execute("REPLACE INTO  axletter
                                       SET  id = {?}, short_name = {?}, subject = {?}, title = {?}, body = {?},
                                            signature = {?}, promo_min = {?}, promo_max = {?}, echeance = {?}",
                             $id, $shortname, $subject, $title, $body, $signature, $promo_min, $promo_max, $echeance);
                if (!$saved) {
                    $mailer = new PlMailer();
                    $mailer->setFrom("support@" . $globals->mail->domain);
                    $mailer->setSubject("Un nouveau projet de mail de l'AX vient d'être proposé");
                    $mailer->setTxtBody("Un nouveau mail vient d'être rédigé en prévision d'un envoi prochain. Vous pouvez "
                                      . "le modifier jusqu'à ce qu'il soit verrouillé pour l'envoi\n\n"
                                      . "Le sujet du mail : $subject\n"
                                      . "L'échéance d'envoi est fixée à $echeance.\n"
                                      . "Le mail pourra néanmoins partir avant cette échéance si un administrateur de "
                                      . "Polytechnique.org le valide.\n\n"
                                      . "Pour modifier, valider ou annuler le mail :\n"
                                      . "https://www.polytechnique.org/ax/edit\n"
                                      . "-- \n"
                                      . "Association Polytechnique.org\n");
                    $res = XDB::iterRow("SELECT IF(u.nom_usage != '', u.nom_usage, u.nom) AS nom,
                                                u.prenom, a.alias AS bestalias
                                           FROM axletter_rights AS ar
                                     INNER JOIN auth_user_md5   AS u USING(user_id)
                                     INNER JOIN aliases         AS a ON (u.user_id = a.id
                                     AND FIND_IN_SET('bestalias', a.flags))");
                    global $globals;
                    while (list($nom, $prenom, $alias) = $res->next()) {
                        $mailer->addTo("$nom $prenom <$alias@{$globals->mail->domain}>");
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
        $page->assign('shortname', $shortname);
        $page->assign('subject', $subject);
        $page->assign('title', $title);
        $page->assign('body', $body);
        $page->assign('signature', $signature);
        $page->assign('promo_min', $promo_min);
        $page->assign('promo_max', $promo_max);
        $page->assign('echeance', $echeance);
        $page->assign('echeance_date', $echeance_date);
        $page->assign('echeance_time', $echeance_time);
        $page->assign('saved', $saved);
        $page->assign('new', $new);
        $page->assign('is_xorg', S::has_perms());

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
        require_once dirname(__FILE__) . '/axletter/axletter.inc.php';
        if (!AXLetter::hasPerms()) {
            return PL_FORBIDDEN;
        }

        $url = parse_url($_SERVER['HTTP_REFERER']);
        if ($force != 'force' && trim($url['path'], '/') != 'ax/edit') {
            return PL_FORBIDDEN;
        }

        $al = AXLetter::awaiting();
        if (!$alg) {
            $page->kill("Aucune lettre en attente");
            return;
        }
        if (!$al->invalid()) {
            $page->kill("Une erreur est survenue lors de l'annulation de l'envoi");
            return;
        }

        $page->kill("L'envoi de l'annonce {$al->title()} est annulé");
    }

    function handler_valid(&$page, $force = null)
    {
        require_once dirname(__FILE__) . '/axletter/axletter.inc.php';
        if (!AXLetter::hasPerms()) {
            return PL_FORBIDDEN;
        }

        $url = parse_url($_SERVER['HTTP_REFERER']);
        if ($force != 'force' && trim($url['path'], '/') != 'ax/edit') {
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

        $page->kill("L'envoi de l'annonce aura lieu dans l'heure qui vient.");
    }

    function handler_show(&$page, $nid = 'last')
    {
        require_once dirname(__FILE__) . '/axletter/axletter.inc.php';
        $page->changeTpl('axletter/show.tpl');

        $nl  = new AXLetter($nid);
        if (Get::has('text')) {
            $nl->toText($page, S::v('prenom'), S::v('nom'), S::v('femme'));
        } else {
            $nl->toHtml($page, S::v('prenom'), S::v('nom'), S::v('femme'));
        }
        if (Post::has('send')) {
            $nl->sendTo(S::v('prenom'), S::v('nom'),
                        S::v('bestalias'), S::v('femme'),
                        S::v('mail_fmt') != 'texte');
        }
    }

    function handler_admin(&$page, $action = null, $uid = null)
    {
        require_once dirname(__FILE__) . '/axletter/axletter.inc.php';
        if (Post::has('action')) {
            $action = Post::v('action');
            $uid    = Post::v('uid');
        }
        if ($uid) {
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
                    $page->trig("Personne ne oorrespond à l'identifiant '$uid'");
                }
            }
        }

        $page->changeTpl('axletter/admin.tpl');
        $res = XDB::iterator("SELECT IF(u.nom_usage != '', u.nom_usage, u.nom) AS nom,
                                     u.prenom, u.promo, a.alias AS forlife
                                FROM axletter_rights AS ar
                          INNER JOIN auth_user_md5   AS u USING(user_id)
                          INNER JOIN aliases         AS a ON (u.user_id = a.id AND a.type = 'a_vie')");
        $page->assign('admins', $res);

        $importer = new CSVImporter('axletter_ins');
        $importer->registerFunction('user_id', 'email vers Id X.org', array($this, 'idFromMail'));
        $importer->forceValue('hash', array($this, 'createHash'));
        $importer->apply($page, "admin/axletter", array('user_id', 'email', 'prenom', 'nom', 'promo', 'flag', 'hash'));
    }

    function idFromMail($line, $key)
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
        $email = $line[$field];
        if (strpos($email, '@') === false) {
            $user  = $email;
            $domain = $globals->mail->domain2;
        } else {
            list($user, $domain) = explode('@', $email);
        }
        if ($domain != $globals->mail->domain && $domain != $globals->mail->domain2
                && $domain != $globals->mail->alias_dom && $domain != $globals->mail->alias_dom2) {
            $res = XDB::query("SELECT uid FROM emails WHERE email = {?}", $email);
            if ($res->numRows() == 1) {
                return $res->fetchOneCell();
            }
            return '0';
        }
        list($user) = explode('+', $user);
        list($user) = explode('_', $user);
        if ($domain == $globals->mail->alias_dom || $domain == $globals->mail->alias_dom2) {
            $res = XDB::query("SELECT a.id
                                 FROM virtual          AS v
                           INNER JOIN virtual_redirect AS r USING(vid)
                           INNER JOIN aliases          AS a ON (a.type = 'a_vie'
                                                            AND r.redirect = CONCAT(a.alias, '@{$globals->mail->domain2}'))
                                WHERE v.alias = CONCAT({?}, '@{$globals->mail->alias_dom}')", $user);
            $id = $res->fetchOneCell();
            return $id ? $id : '0';
        }
        $res = XDB::query("SELECT id FROM aliases WHERE alias = {?}", $user);
        $id = $res->fetchOneCell();
        return $id ? $id : '0';
    }

    function createHash($line, $key)
    {
        $hash = implode(time(), $line) . rand();
        $hash = md5($hash);
        return $hash;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
