<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

// {{{ class ListeReq

class ListeReq extends Validate
{
    // {{{ properties

    public $liste;
    public $desc;
    public $asso;
    public $domain;

    public $advertise;
    public $modlevel;
    public $inslevel;

    public $owners;
    public $members;

    public $rules = "Refuser les listes de binets si elles ne sont pas datées (oui : apv2002@, non : apv@).
        Refuser également des listes qui pourraient nous servir (admin, postmaster&hellip;)";
    // }}}
    // {{{ constructor

    public function __construct(User $_user, $_asso, $_liste, $_domain, $_desc, $_advertise,
                                $_modlevel, $_inslevel, $_owners, $_members, $_stamp = 0)
    {
        parent::__construct($_user, false, 'liste', $_stamp);

        $this->asso      = $_asso;
        $this->liste     = $_liste;
        $this->domain    = $_domain;
        $this->desc      = $_desc;
        $this->advertise = $_advertise;
        $this->modlevel  = $_modlevel;
        $this->inslevel  = $_inslevel;
        $this->owners    = $_owners;
        $this->members   = $_members;
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.listes.tpl';
    }

    // }}}
    // {{{ function editor()

    public function editor()
    {
        return 'include/form.valid.edit-listes.tpl';
    }

    // }}}
    // {{{ function handle_editor()

    protected function handle_editor()
    {
        global $globals;

        if (Env::has('listname')) {
            $this->liste = Post::t('listname');
        }
        if (Env::has('domainname')) {
            $this->domain = Post::t('domainname');
        }
        if (Env::has('assotype')) {
            $this->asso = Post::t('assotype');
        }
        if (!$this->asso) {
            $this->domain = $globals->mail->domain;
        }
        foreach ($this->owners as $key => &$email) {
            $email = Post::t('owners_' . $key);
        }
        foreach ($this->members as $key => &$email) {
            $email = Post::t('members_' . $key);
        }
        return true;
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org/LISTES] Demande de la liste {$this->liste}@{$this->domain}";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  Suite à ta demande de création de liste de diffusion, nous avons créé l'adresse {$this->liste}@{$this->domain}, qui est maintenant à ta disposition.";
        } else {
            return "  La demande que tu avais faite pour la liste de diffusion {$this->liste}@{$this->domain} a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        require_once 'emails.inc.php';

        if ($this->asso == 'alias') {
            foreach ($this->members as $member) {
                add_to_list_alias($member, $this->liste, $this->domain);
            }
        } else {
            $members = User::getBulkForlifeEmails($this->members, true,
                                                  array('ListsModule', 'no_login_callback'));
            $owners = User::getBulkForlifeEmails($this->owners, true,
                                                 array('ListsModule', 'no_login_callback'));

            // Make sure we send a list (array_values) of unique (array_unique)
            // emails.
            $owners = array_values(array_unique($owners));
            $members = array_values(array_unique($members));

            $success = MailingList::create($this->liste, $this->domain, S::user(),
                $this->desc, $this->advertise,
                $this->modlevel, $this->inslevel,
                $owners, $members);

            if ($success) {
                create_list($this->liste, $this->domain);
            }
            return $success;
        }
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
