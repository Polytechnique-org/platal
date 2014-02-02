<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

// {{{ class EntrReq

class EntrReq extends ProfileValidate
{
    // {{{ properties

    public $id;
    public $name;
    public $acronym = null;
    public $url = null;
    public $email = null;
    public $holdingid = null;
    public $NAF_code = null;
    public $AX_code = null;

    public $tel = null;
    public $fax = null;
    public $address = null;

    public $suggestions;
    public $rules = 'Si l\'entreprise est déjà présente sous un autre nom dans la liste des suggestions, remplacer son nom par celui-ci avant de valider. Laisser les autres champs tels quels.';

    public $requireAdmin = false;

    // }}}
    // {{{ constructor

    public function __construct(User $_user, Profile $_profile, $_id, $_name, $_acronym, $_url, $_email, $_tel, $_fax, $_address, $_stamp = 0)
    {
        parent::__construct($_user, $_profile, false, 'entreprise', $_stamp);
        $this->id       = $_id;
        $this->name     = $_name;
        $this->acronym  = $_acronym;
        $this->url      = $_url;
        $this->email    = $_email;
        $this->tel      = $_tel;
        $this->fax      = $_fax;
        $this->address  = $_address;

        $_name       = preg_replace('/[^0-9a-z]/i', ' ', strtolower(replace_accent($_name)));
        $name        = explode(" ", $_name);
        $name_array  = array_map("trim", $name);
        $length      = count($name_array);
        $where       = "";
        for ($i = 0; $i < $length; $i++) {
            if (strlen($name_array[$i]) > 2) {
                if ($where !== "") {
                    $where .= " OR ";
                }
                $where .= "name LIKE '%" . $name_array[$i] . "%'";
            }
        }
        if ($where != '') {
            $res = XDB::iterator('SELECT  name
                                    FROM  profile_job_enum
                                   WHERE  ' . $where);
            $this->suggestions = "| ";
            while ($sug = $res->next()) {
                $this->suggestions .= $sug['name'] . " | ";
            }
        }
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.entreprises.tpl';
    }

    // }}}
    // {{{ function editor()

    public function editor()
    {
        return 'include/form.valid.edit-entreprises.tpl';
    }

    // }}}
    // {{{ function handle_editor()

    protected function handle_editor()
    {
        foreach (array('name', 'acronym', 'url', 'email', 'NAF_code', 'tel', 'fax', 'address') as $field) {
            $this->$field = (Env::t($field) == '' ? null : Env::t($field));
        }
        foreach (array('AX_code', 'holdingid') as $field) {
            $this->$field = (Env::i($field) == 0 ? null : Env::i($field));
        }

        return true;
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return '[Polytechnique.org/Entreprises] Demande d\'ajout d\'une entreprise';
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  L'entreprise " . $this->name . " vient d'être ajoutée à ta fiche.";
        } else {
            return '  La demande que tu avais faite pour l\'entreprise ' . $this->name . ' a été refusée.';
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        $res = XDB::query('SELECT  id
                             FROM  profile_job_enum
                            WHERE  name = {?}',
                          $this->name);
        if ($res->numRows() != 1) {
            XDB::execute('INSERT INTO  profile_job_enum (name, acronym, url, email, holdingid, NAF_code, AX_code)
                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?})',
                         $this->name, $this->acronym, $this->url, $this->email,
                         $this->holdingid, $this->NAF_code, $this->AX_code);

            $jobid = XDB::insertId();
            $phone = new Phone(array('link_type' => 'hq', 'link_id' => $jobid, 'id' => 0,
                                     'type' => 'fixed', 'display' => $this->tel, 'pub' => 'public'));
            $fax   = new Phone(array('link_type' => 'hq', 'link_id' => $jobid, 'id' => 1,
                                     'type' => 'fax', 'display' => $this->fax, 'pub' => 'public'));
            $address = new Address(array('jobid' => $jobid, 'type' => Address::LINK_COMPANY, 'text' => $this->address));
            $phone->save();
            $fax->save();
            $address->save();
        } else {
            $jobid = $res->fetchOneCell();
        }

        XDB::execute('UPDATE  profile_job
                         SET  jobid = {?}
                       WHERE  pid = {?} AND id = {?}',
                     $jobid, $this->profile->id(), $this->id);
        if (XDB::affectedRows() == 0) {
            return XDB::execute('INSERT INTO  profile_job (jobid, pid, id)
                                      VALUES  ({?}, {?}, {?})',
                                $jobid, $this->profile->id(), $this->id);
        }
        return true;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
