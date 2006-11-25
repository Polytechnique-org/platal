<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

class PhotoReq extends Validate
{
    // {{{ properties

    var $mimetype;
    var $data;
    var $x;
    var $y;

    var $unique = true;

    var $rules = "Refuser les photos copyrightées, de mineurs, ou ayant
    un caractère pornographique, violent, etc... Si une photo est mal
    cadrée (20% de photo et 80% de blanc par exemple), si c'est un
    camarade antique, on lui arrange sinon on lui
    refuse en lui expliquant gentiment le problème. Idem si les dimensions de
    la photo sont archi trop grandes ou archi trop petites.";

    // }}}
    // {{{ constructor

    function PhotoReq($_uid, $_data, $_stamp=0)
    {
        $this->Validate($_uid, true, 'photo', $_stamp);
        $this->_get_image($_data);
    }

    // }}}
    // {{{ function _get_image()

    function _get_image($_data)
    {
        global $page;

        VarStream::init();

        // calcul de la taille de l'image
        $GLOBALS['photoreq'] = $_data;
        $image_infos = getimagesize('var://photoreq');
        unset ($GLOBALS['photoreq']);

        if (empty($image_infos)) {
            $page->trig("Image invalide");
            return false;
        }
        list($this->x, $this->y, $this->mimetype) = $image_infos;

        switch ($this->mimetype) {
            case 1: $this->mimetype = "gif";    break;
            case 2: $this->mimetype = "jpeg";   break;
            case 3: $this->mimetype = "png";    break;
            default:
                $page->trig("Type d'image invalide");
                return false;
        }

        if (strlen($_data) > SIZE_MAX)  {
            $img = imagecreatefromstring($_data);
            if (!$img) {
                $page->trig("image trop grande et impossible à retailler automatiquement");
                return false;
            }

            $nx = $x = imagesx($img);
            $ny = $y = imagesy($img);

            if ($nx > 240) { $ny = intval($ny*240/$nx); $nx = 240; }
            if ($ny > 300) { $ny = intval($nx*300/$nx); $ny = 300; }
            if ($nx < 160) { $ny = intval($ny*160/$nx); $nx = 160; }

            $comp = '90';
            $file = tempnam('/tmp', 'photo');

            while (strlen($_data) > SIZE_MAX) {
                $img2  = imagecreatetruecolor($nx, $ny);
                imagecopyresampled($img2, $img, 0, 0, 0, 0, $nx, $ny, $x, $y);
                imagejpeg($img2, $file, $comp);
                $_data = file_get_contents($file);
                $this->mimetype = 'jpeg';

                $comp --;
            }

            unlink($file);
        }
        $this->data = $_data;
        return true;
    }

    // }}}
    // {{{ function get_request()

    function get_request($uid)
    {
        return parent::get_typed_request($uid,'photo');
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.photos.tpl'; }

    // }}}
    // {{{ function editor()

    function editor()
    {
        return 'include/form.valid.edit-photo.tpl';
    }

    // }}}
    // {{{ function handle_editor()

    function handle_editor()
    {
        if (isset($_FILES['userfile']['tmp_name'])) {
            $file = $_FILES['userfile']['tmp_name'];
            if ($data = file_get_contents($file)) {
                if ($this->_get_image($data)) {
                    return true;
                }
            } else {
                $page->trig('Fichier inexistant ou vide');
            }
        }
        return false; 
    }

    // }}}
    // {{{ function _mail_subj

    function _mail_subj()
    {
        return "[Polytechnique.org/PHOTO] Changement de photo";
    }

    // }}}
    // {{{ function _mail_body

    function _mail_body($isok)
    {
        if ($isok) {
            return "Le changement de photo que tu as demandé vient d'être effectué.";
        } else {
            return "La demande de changement de photo que tu avais faite a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()

    function commit()
    {
        XDB::execute('REPLACE INTO  photo (uid, attachmime, attach, x, y)
                                      VALUES  ({?},{?},{?},{?},{?})',
                                      $this->uid, $this->mimetype, $this->data, $this->x, $this->y);
	require_once('notifs.inc.php');
	register_watch_op($this->uid,WATCH_FICHE);
        return true;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
