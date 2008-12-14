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

// {{{ class EntrReq

class EntrReq extends Validate
{
    // {{{ properties

    public $id;
    public $name;
    public $acronym;
    public $url;
    public $email;
    public $holdingid;
    public $NAF_code;
    public $AX_code;

    public $tel;
    public $fax;

    public $suggestions;
    //TODO: addresses

    // }}}
    // {{{ constructor

    public function __construct(User &$_user, $_id, $_name, $_acronym, $_url, $_email, $_tel, $_fax, $_stamp = 0)
    {
        parent::__construct($_user, false, 'entreprise', $_stamp);
        $this->id       = $_id;
        $this->name     = $_name;
        $this->acronym  = $_acronym;
        $this->url      = $_url;
        $this->email    = $_email;
        $this->tel      = $_tel;
        $this->fax      = $_fax;

        $separators  = array("&", "(", ")", "-", "_", ",", ";", ".", ":", "/", "\\", "\'", "\"");
        $replacement = array(" ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ");
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
        $res = XDB::iterator("SELECT  name
                             FROM  profile_job_enum
                            WHERE  "
                          . $where);
        $this->suggestions = "| ";
        while ($sug = $res->next()) {
            var_dump($sug);
            $this->suggestions .= $sug['name'] . " | ";
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
        if (Env::has('holdingid')) {
            $this->holdingid = trim(Env::v('holdingid'));
        }
        if (Env::has('name')) {
            $this->name = trim(Env::v('name'));
            if (Env::has('acronym')) {
                $this->acronym = trim(Env::v('acronym'));
                if (Env::has('url')) {
                    $this->url = trim(Env::v('url'));
                    if (Env::has('NAF_code')) {
                        $this->NAF_code = trim(Env::v('NAF_code'));
                        if (Env::has('AX_code')) {
                            $this->AX_code = trim(Env::v('AX_code'));
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org/Entreprises] Demande d'ajout d'une entreprise : " . $this->name;
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  L'entreprise " . $this->name . " vient d'être ajoutée à ta fiche.";
        } else {
            return "  La demande que tu avais faite pour l'entreprise " . $this->name .
                   " a été refusée, car elle figure déjà dans notre base.";
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
            require_once("profil.func.inc.php");

            XDB::execute('INSERT INTO  profile_job_enum (name, acronym, url, email, holdingid, NAF_code, AX_code)
                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?})',
                         $this->name, $this->acronym, $this->url, $this->email,
                         $this->holdingid, $this->NAF_code, $this->AX_code);
            $jobid = XDB::insertId();
            $display_tel = format_display_number($this->tel, $error_tel);
            $display_fax =format_display_number($this->fax, $error_fax);
            XDB::execute('INSERT INTO  profile_phones (uid, link_type, link_id, tel_id, tel_type,
                                       search_tel, display_tel, pub)
                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}),
                                       ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                         $jobid, 'hq', $this->id, 0, 'fixed', format_phone_number($this->tel), $display_tel, 'public', 
                         $jobid, 'hq', $this->id, 1, 'fax', format_phone_number($this->fax), $display_fax, 'public');
        } else {
            $jobid = $res->fetchOneCell();
            $success = true;
        }
        return XDB::execute('UPDATE  profile_job
                                SET  jobid = {?}
                              WHERE  uid = {?} AND id = {?}',
                            $jobid, $this->user->id(), $this->id);
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
