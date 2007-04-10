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

/** Class to store per user and per category files
 */
class PlUpload
{
    private $forlife;
    private $category;
    private $file_id;

    private $filename;
    private $type;

    /** For images
     */
    private $x;
    private $y;

    public function __construct($forlife, $category, $filename = null)
    {
        $this->file_id  = $filename;
        $this->category = $category;
        $this->forlife  = $forlife;
        $this->filename = $this->makeFilename($this->file_id);
        $this->checkContentType();
    }

    private function makeFilename($file_id)
    {
        global $globals;
        $filename = $globals->spoolroot . '/spool/uploads/temp/';
        if (!file_exists($filename)) {
            if (!mkdir($filename)) {
                trigger_error('can\'t create upload directory: ' . $filename, E_USER_ERROR);
            }
        }
        $filename .= $this->forlife . '-' . $this->category;
        if ($file_id) {
            $filename .= '-' . $file_id;
        }
        return $filename;
    }

    private function checkContentType()
    {
        if ($this->exists()) {
            $this->type = trim(mime_content_type($this->filename));
        }
    }

    public function upload(array &$file)
    {
        if (!is_uploaded_file($file['tmp_name'])) {
            return false;
        } else if (!move_uploaded_file($file['tmp_name'], $this->filename)) {
            return false;
        }
        $this->checkContentType(); 
        return true;
    }

    public function copyFrom($filename)
    {
        if (!copy($filename, $this->filename)) {
            return false;
        }
        $this->checkContentType();
        return true;
    }

    public function download($url)
    {
        if (!$url || @parse_url($url) === false) {
            trigger_error('malformed URL given', E_USER_NOTICE);
            return false;
        }
        $data = file_get_contents($url);
        if (!$data) {
            return false;
        }
        if (!file_put_contents($this->filename, $data)) {
            return false;
        }
        $this->checkContentType();
        return true;
    }

    static public function &get(array &$file, $forlife, $category, $uniq = false)
    {
        $upload = new PlUpload($forlife, $category, $uniq ? null : $file['name']);
        if (!$upload->upload($file)) {
            $upload = null;
        }
        return $upload;
    }

    public function rm()
    {
        @unlink($this->filename);
        @clearstatcache();
    }

    public function rename($fn)
    {
        if (!$this->file_id) {
            return false;
        }
        $filename = $this->makeFilename($fn);
        if (rename($this->filename)) {
            $this->filename = $filename;
            $this->file_id  = $fn;
            clearstatcache();
            return true;
        }
        return false;
    }

    public function exists()
    {
        return file_exists($this->filename);
    }

    static public function listRawFiles($forlife = '*', $category = '*', $uniq = false, $basename = false)
    {
        global $globals;
        $filename = $globals->spoolroot . '/spool/uploads/temp/';
        $filename .= $forlife . '-' . $category;
        if (!$uniq) {
            $filename .= '-*';
        }
        $files = glob($filename);
        if ($basename) {
            $files = array_map('basename', $files);
        }
        return $files;
    }

    static public function listFilenames($forlife = '*', $category = '*')
    {
        $files = PlUpload::listRawFiles($forlife, $category, false, true);
        foreach ($files as &$name) {
            list($forlife, $cat, $fn) = explode('-', $name, 3);
            $name = $fn;
        }
        return $files;
    }

    static public function &listFiles($forlife = '*', $category = '*', $uniq = false)
    {
        $res   = array();
        $files = PlUpload::listRawFiles($forlife, $category, $uniq, true);
        foreach ($files as $name) {
            list($forlife, $cat, $fn) = explode('-', $name, 3);
            $res[$fn] = new PlUpload($forlife, $cat, $fn);
        }
        return $res;
    }

    static public function clear($user = '*', $category = '*', $uniq = false)
    {
        $files = PlUpload::listRawFiles($user, $category, $uniq, false);
        array_map('unlink', $files);
    }

    public function contentType()
    {
        return $this->type;
    }

    public function isType($type, $subtype = null)
    {
        list($mytype, $mysubtype) = explode('/', $this->type);
        if ($mytype != $type || ($subtype && $mysubtype != $subtype)) {
            return false;
        }
        return true;
    }

    public function imageInfo()
    {
        static $map;
        if (!isset($map)) {
            $tmpmap = array (IMG_GIF => 'gif', IMG_JPG => 'jpeg', IMG_PNG => 'png', IMG_WBMP => 'bmp', IMG_XPM => 'xpm');
            $map = array();
            $supported = imagetypes();
            foreach ($tmpmap as $type=>$mime) {
                if ($supported & $type) {
                    $map[$type] = $mime;
                }
            }
        }
        $array = getimagesize($this->filename);
        $array[2] = @$map[$array[2]];
        if (!$array[2]) {
            trigger_error('unknown image type', E_USER_NOTICE);
            return null;
        }
        return $array;
    }

    public function resizeImage($max_x = -1, $max_y = -1, $min_x = 0, $min_y = 0, $maxsize = -1)
    {
        if (!$this->exists() || strpos($this->type, 'image/') !== 0) {
            trigger_error('not an image', E_USER_NOTICE);
            return false;
        }
        $image_infos = $this->imageInfo();
        if (!$image_infos) {
            trigger_error('invalid image', E_USER_NOTICE);
            return false;
        }
        list($this->x, $this->y, $mimetype) = $image_infos;
        if ($max_x == -1) {
            $max_x = $this->x;
        }
        if ($max_y == -1) {
            $max_y = $this->y;
        }
        if ($maxsize == -1) {
            $maxsize = filesize($this->filename);
        }
        if (filesize($this->filename) > $maxsize || $this->x > $max_x || $this->y > $max_y
                                                 || $this->x < $min_x || $this->y < $min_y) {
            $img = imagecreatefromstring(file_get_contents($this->filename));
            if (!$img) {
                trigger_error('too large image, can\'t be resized', E_USER_NOTICE);
                return false;
            }

            $nx = $this->x;
            $ny = $this->y;
            if ($nx > $max_x) {
                $ny = intval($ny*$max_x/$nx);
                $nx = $max_x;
            }
            if ($ny > $max_y) {
                $nx = intval($nx*$max_y/$ny);
                $ny = $max_y;
            }
            if ($nx < $min_x) {
                $ny = intval($ny*$min_x/$nx);
                $nx = $min_x;
            }
            if ($ny < $min_y) {
                $nx = intval($nx * $min_y/$ny);
                $ny = $min_y;
            }

            $comp = 90;
            do {
                $img2 = imagecreatetruecolor($nx, $ny);
                imagecopyresampled($img2, $img, 0, 0, 0, 0, $nx, $ny, $this->x, $this->y);
                imagejpeg($img2, $this->filename, $comp);
                $comp --;
                clearstatcache();
            } while (filesize($this->filename) > $maxsize && $comp > 0);
            $this->type = 'image/jpeg';
            $this->x    = $nx;
            $this->y    = $ny;
        }
        return true;
    }

    public function getContents()
    {
        if ($this->exists()) {
            return file_get_contents($this->filename);
        }
        return null;
    }
}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
