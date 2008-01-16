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

require_once 'validations.inc.php';
// {{{ class HomonymeReq

class HomonymeReq extends Validate
{
    // {{{ properties

    public $loginbis;

    public $warning = true;

    public $homonymes_forlife;

    public $rules = "Accepter, sauf cas particulier d'utilisateur dont l'homonymie est traité plus ... manuellement";

    // }}}
    // {{{ constructor

    public function __construct($_uid, $_loginbis, $_homonymes_forlife, $warning=true)
    {
        $this->warning = $warning;

        parent::__construct($_uid, true, $this->title());

        $this->refuse = false;
        $this->loginbis = $_loginbis;
        $this->homonymes_forlife = $_homonymes_forlife;
    }

    // }}}
    // {{{ title()

    private function title()
    {
        return $this->warning?'alerte alias':'robot répondeur';
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.homonymes.tpl';
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org/Support] ".($this->warning?"Dans une semaine : suppression de l'alias":"Mise en place du robot")." $loginbis@" . $globals->mail->domain;
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        global $globals;
        return
"
Comme nous t'en avons informé par mail il y a quelques temps,
pour respecter nos engagements en terme d'adresses e-mail devinables,
tu te verras bientôt retirer l'alias ".$this->loginbis."@".$globals->mail->domain." pour
ne garder que ".$this->forlife."@".$globals->mail->domain.".

Toute personne qui écrira à ".$this->loginbis."@".$globals->mail->domain." recevra la
réponse d'un robot qui l'informera que ".$this->loginbis."@".$globals->mail->domain."
est ambigu pour des raisons d'homonymie et signalera ton email exact.";
    }

    // }}}
    // {{{ function sendmail()

    protected function sendmail($isok)
    {
        if (!$isok) return false;
        global $globals;
        $mailer = new PlMailer;
        $cc = "support+homonyme@".$globals->mail->domain;
        $FROM = "\"Support Polytechnique.org\" <$cc>";
        $mailer->setSubject($this->_mail_subj());
        $mailer->setFrom($FROM);
        $mailer->addTo("\"{$this->prenom} {$this->nom}\" <{$this->bestalias}@{$globals->mail->domain}>");
        $mailer->addCc($cc);

        $body = $this->prenom.",\n\n"
              . $this->_mail_body($isok)
              . (Env::has('comm') ? "\n\n".Env::v('comm') : '')
              . "\n\nCordialement,\n\n-- \nL'équipe de Polytechnique.org\n";

        $mailer->setTxtBody(wordwrap($body));
        $mailer->send();
    }
    // }}}
    // {{{ function commit()

    public function commit()
    {
        require_once('homonymes.inc.php');

        switch_bestalias($this->uid, $this->loginbis);
        if (!$this->warning) {
            XDB::execute("UPDATE aliases SET type='homonyme',expire=NOW() WHERE alias={?}", $this->loginbis);
            XDB::execute("REPLACE INTO homonymes (homonyme_id,user_id) VALUES({?},{?})", $this->uid, $this->uid);
        }

        return true;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
