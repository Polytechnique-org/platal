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

class Marketing
{
    static private $engines = array(
        //user name  => array(class name,          require data)
        'annuaire'   => array('AnnuaireMarketing', false),
        'groupe'     => array('GroupMarketing',    true),
        'liste'      => array('ListMarketing',     true),
    );

    private $engine;
    public $sender_mail;
    public $user;

    private $type;
    private $data;
    private $from;
    private $sender;

    private $hash = '';

    public function __construct($uid, $email, $type, $data, $from, $sender = null)
    {
        $this->user         = $this->getUser($uid, $email);
        $this->sender_mail  = $this->getFrom($from, $sender);
        $this->engine       = $this->getEngine($type, $data, $from == 'user' ? null : $this->sender);

        $this->type   = $type;
        $this->data   = $data;
        $this->from   = $from;
        $this->sender = $sender; 
    }

    private function getUser($uid, $email)
    {
        require_once("xorg.misc.inc.php");
        $res = XDB::query("SELECT  FIND_IN_SET('femme', flags) AS sexe, nom, prenom, promo
                             FROM  auth_user_md5
                            WHERE  user_id = {?}", $uid);
        if ($res->numRows() == 0) {
            return null;
        } 
        $user            = $res->fetchOneAssoc();
        $user['id']      = $uid;
        $user['forlife'] = make_forlife($user['prenom'], $user['nom'], $user['promo']);
        $user['mail']    = $email;
        $user['to']      = '"' . $user['prenom'] . ' ' . $user['nom'] . '" <' . $email . '>';
        return $user;
    }

    private function getFrom($from, $sender)
    {
        if ($from == 'staff') {
            return '"Equipe Polytechnique.org" <register@polytechnique.org>';
        } else {
            $res = XDB::query("SELECT  u.nom, u.prenom, a.alias
                                 FROM  auth_user_md5 AS u
                           INNER JOIN  aliases       AS a ON (a.id = u.user_id AND FIND_IN_SET('bestalias', a.flags))
                                WHERE  u.user_id = {?}", $sender);
            if (!$res->numRows()) {
                return '"Equipe Polytechnique.org" <register@polytechnique.org>';
            }
            $sender = $res->fetchOneAssoc();
            return '"' . $sender['prenom'] . ' ' . $sender['nom'] . '" <' . $sender['alias'] . '@polytechnique.org>';
        }
    }

    private function getEngine($type, $data, $from)
    {
        $class = $type . 'Marketing';
        if (!class_exists($class, false)) {
            $class= 'DefaultMarketing';
        }
        return new $class($data, $from);
    }

    public function getTitle()
    {
        return $this->engine->getTitle();
    }

    public function getText()
    {
        return $this->engine->getText($this->user);
    }

    public function send($title = null, $text = null)
    {
        $this->hash = rand_url_id(12);
        if (!$title) {
            $title = $this->engine->getTitle();
        }
        if (!$text) {
            $text = $this->engine->getText($this->user);
        }
        $sender = substr($this->sender_mail, 1, strpos($this->sender_mail, '"', 2)-1);
        $text = str_replace(array("%%hash%%", "%%sender%%"),
            array($this->hash, $this->sender_mail),
            $text);
        $mailer = new PlMailer();
        $mailer->setFrom($this->sender_mail);
        $mailer->addTo($this->user['mail']);
        $mailer->setSubject($title);
        $mailer->setTxtBody($text);
        $mailer->send();
        $this->incr();
    }

    public function add($valid = true)
    {
        XDB::execute('INSERT IGNORE INTO  register_marketing
                                          (uid, sender, email, date, last, nb, type, hash, message, message_data)
                                  VALUES  ({?}, {?}, {?}, NOW(), 0, 0, {?}, {?}, {?}, {?})',
                    $this->user['id'], $this->sender, $this->user['mail'], $this->from, $this->hash,
                    $this->type, $this->data);
        $this->engine->process($this->user);
        if ($valid) {
            require_once 'validations.inc.php';
            $valid = new MarkReq($this->sender, $this->user['id'], $this->user['mail'],
                                 $this->from == 'user', $this->type, $this->data); 
            $valid->submit();
        }
        return true;
    }

    private function incr()
    {
        XDB::execute('UPDATE  register_marketing
                         SET  nb=nb+1, hash={?}, last=NOW()
                       WHERE  uid={?} AND email={?}',
            $this->hash, $this->user['id'], $this->user['mail']);
    }

    static public function getEngineList($exclude_data = true)
    {
        $array = array();
        foreach (Marketing::$engines as $e => $d) {
            if (!$d[1] || !$exclude_data) {
                $array[] = $e;
            }
        }
        return $array;
    }

    static public function get($uid, $email)
    {
        $res = XDB::query("SELECT  uid, email, message, message_data, type, sender
                             FROM  register_marketing
                            WHERE  uid = {?} AND email = {?}", $uid, $email);
        if ($res->numRows() == 0) {
            return null;
        }
        list ($uid, $email, $type, $data, $from, $sender) = $res->fetchOneRow();
        return new Marketing($uid, $email, $type, $data, $from, $sender);
    }

    static public function clear($uid, $email = null)
    {
        if (!$email) {
            XDB::execute("DELETE FROM register_marketing WHERE uid = {?}", $uid);
        } else {
            XDB::execute("DELETE FROM register_marketing WHERE uid = {?} AND email = {?}", $uid, $email);        
            XDB::execute("DELETE FROM register_subs WHERE uid = {?}", $uid);
        }
    }

    static public function relance($uid, $nbx = -1)
    {
        global $globals;

        if ($nbx < 0) {
            $res = XDB::query("SELECT COUNT(*) FROM auth_user_md5 WHERE deces=0");
            $nbx = $res->fetchOneCell();
        }
    
        $res = XDB::query("SELECT  r.date, u.promo, u.nom, u.prenom, r.email, r.bestalias
                             FROM  register_pending AS r
                       INNER JOIN  auth_user_md5    AS u ON u.user_id = r.uid
                            WHERE  hash!='INSCRIT' AND uid={?} AND TO_DAYS(relance) < TO_DAYS(NOW())", $uid);
        if (!list($date, $promo, $nom, $prenom, $email, $alias) = $res->fetchOneRow()) {
            return false;
        }
    
        require_once('secure_hash.inc.php');
        $hash     = rand_url_id(12);
        $pass     = rand_pass();
        $pass_encrypted = hash_encrypt($pass);
        $fdate    = strftime('%d %B %Y', strtotime($date));
    
        $mymail = new PlMailer('marketing/mail.relance.tpl');
        $mymail->assign('nbdix',      $nbx);
        $mymail->assign('fdate',      $fdate);
        $mymail->assign('lusername',  $alias);
        $mymail->assign('nveau_pass', $pass);
        $mymail->assign('baseurl',    $globals->baseurl);
        $mymail->assign('lins_id',    $hash);
        $mymail->assign('lemail',     $email);
        $mymail->assign('subj',       $alias.'@'.$globals->mail->domain);
        $mymail->send();
        XDB::execute('UPDATE  register_pending
                         SET  hash={?}, password={?}, relance=NOW()
                       WHERE uid={?}', $hash, $pass_encrypted, $uid);
        return "$prenom $nom ($promo)";
    }
}

interface MarketingEngine
{
    public function __construct($data, $from);
    public function getTitle();
    public function getText(array $user);
    public function process(array $user);
}

// 
class AnnuaireMarketing implements MarketingEngine
{
    protected $titre;
    protected $intro;

    public function __construct($data, $from)
    {
        $this->titre = "Annuaire en ligne des Polytechniciens";
        $this->intro = "   Ta fiche n'est pas à jour dans l'annuaire des Polytechniciens sur Internet. "
                     . "Pour la mettre à jour, il te it de visiter cette page ou de copier cette adresse "
                     . "dans la barre de ton navigateur :";
    }

    public function getTitle()
    {
        return $this->titre;
    }

    private function getIntro()
    {
        return $this->intro;
    }

    protected function prepareText(PlatalPage &$page, array $user)
    {
        $page->assign('intro', $this->getIntro());
        $page->assign('u', $user);
        $res = XDB::query("SELECT COUNT(*) FROM auth_user_md5 WHERE perms IN ('user', 'admin') AND deces = 0");
        $page->assign('num_users', $res->fetchOneCell());
    }

    public function getText(array $user)
    {
        $page = new PlatalPage('marketing/mail.marketing.tpl', NO_SKIN);
        $this->prepareText($page, $user);
        return $page->raw();
    }

    public function process(array $user)
    {
    }
}

class ListMarketing extends AnnuaireMarketing
{
    private $name;
    private $domain;
    public function __construct($data, $from)
    {
        list($this->name, $this->domain) = explode('@', $data);
        $res = XDB::query("SELECT  prenom, IF (nom_usage != '', nom_usage, nom)
                             FROM  auth_user_md5
                            WHERE  user_id = {?} AND user_id != 0", $from ? $from : 0);
        if ($res->numRows()) {
            list($prenom, $nom) = $res->fetchOneRow();
            $from = "$prenom $nom";
        } else {
            $from = "Je";
        }
        $this->titre = "Un camarade solicite ton inscription à $data";
        $this->intro = "Polytechnique.org, l'annuaire des Polytechniciens sur internet, "
                     . "fournit de nombreux services aux groupes X, ainsi que des listes "
                     . "de diffusion pour les X en faisant la demande.\n\n"
                     . "$from solicite ton inscription à la liste <$data>. "
                     . "Cependant, seuls les X inscrits sur Polytechnique.org peuvent "
                     . "profiter de l'ensemble de nos services, c'est pourquoi nous te "
                     . "proposons auparavant de t'inscrire sur notre site. Pour cela, il "
                     . "te suffit de visiter cette page ou de copier cette adresse dans "
                     . "la barre de ton navigateur :";
    }

    public function process(array $user)
    {
        return XDB::execute("REPLACE INTO  register_subs (uid, type, sub, domain)
                                   VALUES  ({?}, 'list', {?}, {?})",
                            $user['id'], $this->name, $this->domain);
    }
}

class GroupMarketing extends AnnuaireMarketing
{
    private $group;
    public function __construct($data, $from)
    {
        $this->group = $data;
        $res = XDB::query("SELECT  prenom, IF (nom_usage != '', nom_usage, nom)
                             FROM  auth_user_md5
                            WHERE  user_id = {?} AND user_id != 0", $from ? $from : 0);
        if ($res->numRows()) {
            list($prenom, $nom) = $res->fetchOneRow();
            $from = "$prenom $nom vient";
        } else {
            $from = "Je viens";
        }
        $this->titre = "Profite de ton inscription au groupe \"$data\" pour découvrir Polytechnique.org";
        $this->intro = "Polytechnique.org, l'annuaire des Polytechniciens sur internet, fournit "
                     . "de nombreux services aux groupes X ( listes de diffusion, paiement en "
                     . "ligne, sites internet...), en particulier pour le groupe \"$data\"\n\n"
                     . "$from de t'inscrire dans l'annuaire du groupe \"$data\". "
                     . "Cependant, seuls les X inscrits sur Polytechnique.org peuvent profiter "
                     . "de l'ensemble de nos services, c'est pourquoi nous te proposons de "
                     . "t'inscrire sur notre site . Pour cela, il te suffit de visiter cette page "
                     . "ou de copier cette adresse dans la barre de ton navigateur :";
    }

    public function process(array $user)
    {
        return XDB::execute("REPLACE INTO  register_subs (uid, type, sub, domain)
                                   VALUES  ({?}, 'group', {?}, '')",
                            $user['id'], $this->group);
    }
}

/// Make AnnuaireMarketing to be the default message
class DefaultMarketing extends AnnuaireMarketing
{
}

// vim:set et sw=4 sts=4 sws=4 enc=utf-8:
?>
