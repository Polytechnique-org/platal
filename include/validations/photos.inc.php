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

// {{{ class PhotoReq

class PhotoReq extends Validate
{
    // {{{ properties
    
    var $mimetype;
    var $data;
    var $x;
    var $y;

    var $unique = true;

    var $rules = "Il faut interdire les photos : (1) protégées par des droits d'auteurs (2) de mineurs.";

    // }}}
    // {{{ constructor
   
    function PhotoReq($_uid, $_data, $_stamp=0)
    {
        global $globals, $page;

        $this->Validate($_uid, true, 'photo', $_stamp);
        
        // calcul de la taille de l'image
        require_once('xorg.varstream.inc.php');
        $GLOBALS['photoreq'] = $_data;
        $image_infos = getimagesize('var://photoreq');
        unset ($GLOBALS['photoreq']);

        if (empty($image_infos)) {
            $page->trig("Image invalide");
            return ($this = null);
        }
        list($this->x, $this->y, $this->mimetype) = $image_infos;

        switch ($this->mimetype) {
            case 1: $this->mimetype = "gif";    break;
            case 2: $this->mimetype = "jpeg";   break;
            case 3: $this->mimetype = "png";    break;
            default:
                $page->trig("Type d'image invalide");
                return ($this = null);
        }

        if (strlen($_data) > SIZE_MAX)  {
            $img = imagecreatefromstring($_data);
            if (!$img) {
                $page->trig("image trop grande et impossible à retailler automatiquement");
                return ($this = null);
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
    }
    
    // }}}
    // {{{ function get_request()

    function get_request($uid)
    {
        return parent::get_request($uid,'photo');
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.photos.tpl'; }

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
            return "  La demande de changement de photo que tu as demandée vient d'être effectuée.";
        } else {
            return "  La demande de changement de photo que tu avais faite a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()
    
    function commit()
    {
        global $globals;
        
        $globals->xdb->execute('REPLACE INTO  photo (uid, attachmime, attach, x, y)
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
