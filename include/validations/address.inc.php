<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

// {{{ class AddressReq

class AddressReq extends ProfileValidate
{
    // {{{ properties

    // Address primary field that are not in its formatted array.
    public $key_pid;
    public $key_jobid;
    public $key_groupid;
    public $key_type;
    public $key_id;

    // We need the text given by the user, and the toy version to try to improve
    // the geocoding.
    public $address;
    public $given_text;
    public $toy_text = '';
    public $modified = false;

    public $rules = 'Si la localisation est bonne, refuser. Sinon, si le texte est faux, le corriger. Si la géolocaliastion ne marche toujours pas, utiliser la version jouet qui ne sera pas stockée, mais dont les données de localisation le seront.';

    // }}}
    // {{{ constructor

    public function __construct(User $_user, array $_address, $_pid, $_jobid, $_groupid, $_type, $_id, $_stamp = 0)
    {
        $_profile = Profile::get($_pid);
        parent::__construct($_user, $_profile, false, 'address', $_stamp);
        $this->key_pid = $_pid;
        $this->key_jobid = $_jobid;
        $this->key_groupid = $_groupid;
        $this->key_type = $_type;
        $this->key_id = $_id;
        $this->given_text = $_address['text'];
        $this->address = $_address;
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.address.tpl';
    }

    // }}}
    // {{{ function editor()

    public function editor()
    {
        return 'include/form.valid.edit-address.tpl';
    }

    // }}}
    // {{{ function handle_editor()

    protected function handle_editor()
    {
        $data = Post::v('valid');
        if (isset($data['text']) && $data['text'] != $this->toy_text && $data['text'] != $this->given_text) {
            $this->toy_text = $data['text'];
            $address = new Address(array('changed' => 1, 'text' => $this->toy_text));
            $address->format();
            $this->address = $address->toFormArray();
        }
        $this->modified = isset($data['modified']);

        return true;
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return '[Polytechnique.org/Adresse] Demande d\'amélioration de la localisation d\'une adresse';
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  Nous avons réussi à mieux localiser l'adresse suivante :\n{$this->given_text}.";
        } else {
            return "  L'adresse est suffisemment bien localisée pour les besoins du site (recherche avancée, planisphère), nous avons donc choisi de ne pas la modifier.";
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        $this->address = array_merge($this->address, array(
            'pid'     => $this->key_pid,
            'jobid'   => $this->key_jobid,
            'groupid' => $this->key_groupid,
            'type'    => $this->key_type,
            'id'      => $this->key_id
        ));
        $this->address['text'] = ($this->modified ? $this->toy_text : $this->given_text);;
        $this->address['changed'] = 0;
        $address = new Address($this->address);
        $address->format();
        $address->updateGeocoding();

        return true;
    }

    // }}}
    // {{{ function get_request()

    static public function get_request($pid, $jobid, $groupid, $type, $id)
    {
        $reqs = parent::get_typed_requests($pid, 'address');
        foreach ($reqs as &$req) {
            if ($req->key_pid == $pid && $req->key_jobid == $jobid && $req->key_groupid == $groupid
                && $req->key_type == $type && $req->key_id == $id) {
                return $req;
            }
        }
        return null;
    }

    // }}}
    // {{{ function purge_requests()

    // Purges address localization requests based on deleted addresses.
    static public function purge_requests($pid, $jobid, $groupid, $type)
    {
        $requests = parent::get_all_typed_requests('address');
        foreach ($requests as &$req) {
            if ($req->key_pid == $pid && $req->key_jobid == $jobid && $req->key_groupid == $groupid && $req->key_type == $type) {
                $req->clean();
            }
        }
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
