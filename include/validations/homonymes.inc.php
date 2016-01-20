<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

// {{{ class HomonymeReq

class HomonymeReq extends Validate
{
    // {{{ properties

    public $email;
    public $homonymes_hruid;
    public $warning = true;
    public $rules = "Accepter, sauf cas particulier d'utilisateur dont l'homonymie est traité plus &hellip; manuellement.";

    // }}}
    // {{{ constructor

    public function __construct(User $_user, $_email, $_homonymes_hruid, $warning = true)
    {
        $this->warning = $warning;

        parent::__construct($_user, true, $this->title());

        $this->refuse = false;
        $this->email = $_email;
        $this->homonymes_hruid = $_homonymes_hruid;
    }

    // }}}
    // {{{ title()

    private function title()
    {
        return ($this->warning ? 'alerte alias' : 'robot répondeur');
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
        return "[Polytechnique.org/Support] "
            . ($this->warning ? "Dans une semaine : suppression de l'alias " : "Mise en place du robot")
            . " $email@" . $this->user->mainEmailDomain();
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        return
"
Comme nous t'en avons informé par email il y a quelques temps,
pour respecter nos engagements en terme d'adresses email devinables,
tu te verras bientôt retirer l'alias " . $this->email . "@" . $this->user->mainEmailDomain() . " pour
ne garder que " . $this->user->forlifeEmail() . ".

Toute personne qui écrira à " . $this->email . "@" . $this->user->mainEmailDomain() . " recevra la
réponse d'un robot qui l'informera que " . $this->email . "@" . $this->user->mainEmailDomain() . "
est ambigu pour des raisons d'homonymie et signalera ton email exact.";
    }

    // }}}
    // {{{ function sendmail()

    protected function sendmail($isok)
    {
        if (!$isok) {
            return false;
        }

        Platal::load('admin', 'homonyms.inc.php');
        if ($this->warning) {
            send_warning_homonym($this->user, $this->email);
        } else {
            send_robot_homonym($this->user, $this->email);
        }
    }
    // }}}
    // {{{ function commit()

    public function commit()
    {
        if (!$this->warning) {
            Platal::load('admin', 'homonyms.inc.php');
            fix_homonym($this->user, $this->email);
        }

        return true;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
