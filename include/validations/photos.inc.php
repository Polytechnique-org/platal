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

// {{{ class PhotoReq

class PhotoReq extends ProfileValidate
{
    // {{{ properties

    public $mimetype;
    public $data;
    public $x;
    public $y;

    public $unique = true;
    public $valid  = false;

    public $rules = "Refuser les photos copyrightées, de mineurs, ou ayant
        un caractère pornographique, violent&hellip; Si une photo est mal
        cadrée (20% de photo et 80% de blanc par exemple), si c'est un
        camarade antique, on lui arrange sinon on lui
        refuse en lui expliquant gentiment le problème. Idem si les dimensions de
        la photo sont archi trop grandes ou archi trop petites.";

    // }}}
    // {{{ constructor

    public function __construct(User $_user, Profile $_profile, PlUpload $upload, $_stamp = 0)
    {
        parent::__construct($_user, $_profile, true, 'photo', $_stamp);
        $this->read($upload);
    }

    // }}}
    // {{{ function read

    private function read(PlUpload $upload)
    {
        $this->valid = $upload->resizeImage(240, 300, 160, 0, SIZE_MAX);
        if (!$this->valid) {
            $this->trigError('Le fichier que tu as transmis n\'est pas une image valide, ou est trop gros pour être traité.');
        }
        $this->data = $upload->getContents();
        list($this->x, $this->y, $this->mimetype) = $upload->imageInfo();
        $upload->rm();
    }

    // }}}
    // {{{ function isValid()

    public function isValid()
    {
        return $this->valid;
    }

    // }}}
    // {{{ function get_request()

    static public function get_request($pid)
    {
        return parent::get_typed_request($pid, 'photo');
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.photos.tpl';
    }

    // }}}
    // {{{ function editor()

    public function editor()
    {
        return 'include/form.valid.edit-photo.tpl';
    }

    // }}}
    // {{{ function handle_editor()

    protected function handle_editor()
    {
        if (isset($_FILES['userfile'])) {
            $upload =& PlUpload::get($_FILES['userfile'], S::user()->login(), 'photo');
            if (!$upload) {
                $this->trigError('Une erreur est survenue lors du téléchargement du fichier.');
                return false;
            }
            $this->read($upload);
            return $this->valid;
        }
        return false;
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org/PHOTO] Changement de photo";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "Le changement de photo que tu as demandé vient d'être effectué.";
        } else {
            return "La demande de changement de photo que tu avais faite a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        XDB::execute('INSERT INTO  profile_photos (pid, attachmime, attach, x, y)
                           VALUES  ({?}, {?}, {?}, {?}, {?})
          ON DUPLICATE KEY UPDATE  attachmime = VALUES(attachmime), attach = VALUES(attach),
                                   x = VALUES(x), y = VALUES(y)',
                     $this->profile->id(), $this->mimetype, $this->data, $this->x, $this->y);

        return true;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
