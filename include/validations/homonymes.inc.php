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

require_once('validations.inc.php');
// {{{ class HomonymeReq

class HomonymeReq extends Validate
{
    // {{{ properties
    
    var $loginbis;

    var $warning = true;

    var $homonymes_forlife;

    var $rules = "Ces requêtes sont générées par un cron, la validation permet d'éviter de spammer les gens si le cron s'emballe ou de créer un robot pour une personne qui ne devrait pas en avoir.";

    // }}}
    // {{{ constructor
   
    function HomonymeReq($_uid, $_loginbis, $_homonymes_forlife, $warning=true)
    {
        global $global;

        $this->warning = $warning;

        $this->Validate($_uid, true, $this->title());

        $this->refuse = false;

        $this->loginbis = $_loginbis;

        $this->homonymes_forlife = $_homonymes_forlife;

    }
    
    // }}}
    // {{{ title()
    
    function title() {
        return $this->warning?'alerte alias':'robot répondeur';
    }

    // }}}
    // {{{ function get_request()

    function get_request($uid)
    {
        return parent::get_request($uid,$this->title);
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.homonymes.tpl'; }

    // }}}
    // {{{ function _mail_subj

    function _mail_subj()
    {
        return "[Polytechnique.org/Support] ".($this->warning?"Dans une semaine : suppression de l'alias":"Mise en place du robot")." $loginbis@polytechnique.org";
    }

    // }}}
    // {{{ function _mail_body
    
    function _mail_body($isok)
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

    function sendmail($isok)
    {
        if (!$isok) return false;
        global $globals;
        require_once('diogenes/diogenes.hermes.inc.php');
        $mailer = new HermesMailer;
        $cc = "support+homonyme@".$globals->mail->domain;
        $FROM = "\"Support Polytechnique.org\" <$cc>";
        $mailer->setSubject($this->_mail_subj());
        $mailer->setFrom($FROM);
        $mailer->addTo("\"{$this->prenom} {$this->nom}\" <{$this->bestalias}@{$globals->mail->domain}>");
        $mailer->addCc($cc);

        $body = $this->prenom.",\n\n"
              . $this->_mail_body($isok)
              . (Env::has('comm') ? "\n\n".Env::get('comm') : '')
              . "\n\nCordialement,\nL'équipe Polytechnique.org\n";

        $mailer->setTxtBody(wordwrap($body));
        $mailer->send();
    }
    // }}}
    // {{{ function commit()
    
    function commit()
    {
        global $globals;
        require_once('homonymes.inc.php');

        switch_bestalias($this->uid, $this->loginbis);
        if (!$this->warning) {
            $globals->xdb->execute("UPDATE aliases SET type='homonyme',expire=NOW() WHERE alias={?}", $this->loginbis);
            $globals->xdb->execute("REPLACE INTO homonymes (homonyme_id,user_id) VALUES({?},{?})", $this->uid, $this->uid);
        }
        
        return true;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
