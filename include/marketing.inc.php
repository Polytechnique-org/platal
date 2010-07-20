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
    private $personal_notes;

    private $hash = '';

    public function __construct($uid, $email, $type, $data, $from, $sender = null, $personal_notes = null)
    {
        $this->user         = $this->getUser($uid, $email);
        $this->sender_mail  = $this->getFrom($from, $sender);
        $this->engine      =& $this->getEngine($type, $data, $from == 'user' ? $sender : null, $personal_notes);

        $this->type   = $type;
        $this->data   = $data;
        $this->from   = $from;
        $this->sender = $sender;
        $this->personal_notes = $personal_notes;
    }

    private function getUser($uid, $email)
    {
        $user = User::getSilent($uid);
        if (!$user) {
            return null;
        }

        global $globals;
        return array(
            'user'           => $user,
            'id'             => $user->id(),
            'sexe'           => $user->isFemale(),
            'mail'           => $email,
            'to'             => '"' . $user->fullName() . '" <' . $email . '>',
            'forlife_email'  => $user->login() . '@' . $globals->mail->domain,
            'forlife_email2' => $user->login() . '@' . $globals->mail->domain2,
        );
    }

    private function getFrom($from, $sender)
    {
        global $globals;

        if ($from == 'staff' || !($user = User::getSilent($sender))) {
            return "\"L'équipe de Polytechnique.org\" <register@" . $globals->mail->domain . '>';
        }
        return '"' . $user->fullName() . '" <' . $user->bestEmail() . '>';
    }

    private function &getEngine($type, $data, $from, $personal_notes)
    {
        $class = $type . 'Marketing';
        if (!class_exists($class, false)) {
            $class= 'DefaultMarketing';
        }
        $engine = new $class($data, $from, $personal_notes);
        if (!$engine instanceof MarketingEngine) {
            $engine = null;
        }
        return $engine;
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
        $text = str_replace(array('%%hash%%', '%%sender%%', '%%personal_notes%%'),
                            array($this->hash, "Cordialement,\n-- \n" . $this->sender_mail, ''), $text);
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
                                          (uid, sender, email, date, last, nb, type, hash, message, message_data, personal_notes)
                                  VALUES  ({?}, {?}, {?}, NOW(), 0, 0, {?}, {?}, {?}, {?}, {?})',
                    $this->user['id'], $this->sender, $this->user['mail'], $this->from, $this->hash,
                    $this->type, $this->data, $this->personal_notes);
        $this->engine->process($this->user);
        if ($valid) {
            require_once 'validations.inc.php';
            $sender = User::getSilent($this->sender);
            $valid = new MarkReq($sender, $this->user['user'], $this->user['mail'],
                                 $this->from == 'user', $this->type, $this->data, $this->personal_notes);
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

    static public function get($uid, $email, $recentOnly = false)
    {
        $res = XDB::query("SELECT  uid, email, message, message_data, type, sender, personal_notes
                             FROM  register_marketing
                            WHERE  uid = {?}
                              AND  email = {?}".(
              $recentOnly ? ' AND  DATEDIFF(NOW(), last) < 30' : ''), $uid, $email);

        if ($res->numRows() == 0) {
            return null;
        }
        list ($uid, $email, $type, $data, $from, $sender, $personal_notes) = $res->fetchOneRow();
        return new Marketing($uid, $email, $type, $data, $from, $sender, $personal_notes);
    }

    static public function clear($uid, $email = null)
    {
        if (!$email) {
            XDB::execute("DELETE FROM register_marketing WHERE uid = {?}", $uid);
        } else {
            XDB::execute("DELETE FROM register_marketing WHERE uid = {?} AND email = {?}", $uid, $email);
        }
    }

    static public function relance(PlUser &$user, $nbx = -1)
    {
        global $globals;

        if ($nbx < 0) {
            $nbx = $globals->core->NbIns;
        }

        $res = XDB::fetchOneCell('SELECT  r.date, r.email, r.bestalias
                                    FROM  register_pending
                                   WHERE  r.hash = \'INSCRIT\' AND uid = {?}',
                                   $user->id());
        if (!$res) {
            return false;
        } else {
            list($date, $email, $alias) = $res;
        }

        $hash     = rand_url_id(12);
        $pass     = rand_pass();
        $pass_encrypted = sha1($pass);
        $fdate    = strftime('%d %B %Y', strtotime($date));

        $mymail = new PlMailer('marketing/relance.mail.tpl');
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
                       WHERE  uid={?}', $hash, $pass_encrypted, $user->id());
        return $user->fullName();
    }
}

interface MarketingEngine
{
    public function __construct($data, $from, $personal_notes = null);
    public function getTitle();
    public function getText(array $user);
    public function process(array $user);
}

class AnnuaireMarketing implements MarketingEngine
{
    protected $titre;
    protected $intro;
    protected $signature;
    protected $personal_notes;

    public function __construct($data, $from, $personal_notes = null)
    {
        $this->titre = "Rejoins la communauté polytechnicienne sur Internet";
        $this->intro = "   Tu n'as pas de fiche dans l'annuaire des polytechniciens sur Internet. "
                     . "Pour y figurer, il te suffit de visiter cette page ou de copier cette adresse "
                     . "dans la barre de ton navigateur :";
        if ($from === null) {
            $page = new XorgPage();
            $page->changeTpl('include/signature.mail.tpl', NO_SKIN);
            $page->assign('mail_part', 'text');
            $this->signature = $page->raw();
        } else {
            $this->signature = '%%sender%%';
        }
        if (is_null($personal_notes) || $personal_notes == '') {
            $this->personal_notes = '%%personal_notes%%';
        } else {
            $this->personal_notes = "\n" . $personal_notes . "\n";
        }
    }

    public function getTitle()
    {
        return $this->titre;
    }

    private function getIntro()
    {
        return $this->intro;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function getPersonalNotes()
    {
        return $this->personal_notes;
    }

    protected function prepareText(PlPage &$page, array $user)
    {
        $page->assign('intro', $this->getIntro());
        $page->assign('u', $user);
        $page->assign('sign', $this->getSignature());
        $page->assign('personal_notes', $this->getPersonalNotes());
    }

    public function getText(array $user)
    {
        $page = new XorgPage();
        $page->changeTpl('marketing/marketing.mail.tpl', NO_SKIN);
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
    public function __construct($data, $from, $personal_notes = null)
    {
        list($this->name, $this->domain) = explode('@', $data);
        if ($from && ($user = User::getSilent($from))) {
            $from = $user->fullName();
        } else {
            $from = "Je";
        }
        $this->titre = "Un camarade solicite ton inscription à $data";
        $this->intro = "Polytechnique.org, l'annuaire des polytechniciens sur internet, "
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
    public function __construct($data, $from, $personal_notes = null)
    {
        $this->group = $data;
        if ($from && ($user = User::getSilent($from))) {
            $from = $user->fullName() . " vient";
        } else {
            $from = "Je viens";
        }
        $this->titre = "Profite de ton inscription au groupe \"$data\" pour découvrir Polytechnique.org";
        $this->intro = "Polytechnique.org, l'annuaire des polytechniciens sur internet, fournit "
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
