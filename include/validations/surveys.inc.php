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

// {{{ class SurveyReq

class SurveyReq extends Validate
{
    // {{{ properties
    public $title;
    public $description;
    public $end;
    public $mode;
    public $promos;
    public $questions;
    // }}}
    // {{{ constructor

    public function __construct($_title, $_description, $_end, $_mode, $_promos, $_questions, $_uid)
    {
        parent::__construct($_uid, false, 'surveys');
        $this->title       = $_title;
        $this->description = $_description;
        $this->end         = $_end;
        $this->mode        = $_mode;
        $this->promos      = $_promos;
        $this->questions   = $_questions;
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.surveys.tpl';
    }

    // }}}
    // {{{ function _mail_subj
    
    protected function _mail_subj()
    {
        return "[Polytechnique.org/SONDAGES] Proposition de sondage";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  Le sondage que tu avais proposé vient d'être validé.";
        } else {
            return "  Le sondage que tu avais proposé a été refusé.";
        }
    }

    // }}}
    // {{{ function updateReq()

    public function updateReq($_title, $_description, $_end, $_mode, $_promos, $_questions)
    {
        $this->title       = $_title;
        $this->description = $_description;
        $this->end         = $_end;
        $this->mode        = $_mode;
        $this->promos      = $_promos;
        $this->questions   = $_questions;
        return $this->update();
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        $sql = 'INSERT INTO survey_surveys
                        SET questions={?},
                            title={?},
                            description={?},
                            author_id={?},
                            end={?},
                            mode={?},
                            promos={?}';
        return XDB::execute($sql, serialize($this->questions), $this->title, $this->description, $this->uid, $this->end, $this->mode, $this->promos);
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
