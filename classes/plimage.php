<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

class PlImage {
    protected $mime = null;
    protected $x = null;
    protected $y = null;

    protected $data = null;
    protected $file = null;

    private function __construct()
    {
    }

    public function send()
    {
        pl_cached_dynamic_content_headers($this->mime);
        if (empty($this->data)) {
            readfile($this->file);
        } else {
            echo $this->data;
        }
        exit;
    }

    public function path()
    {
        if (empty($this->data)) {
            return $file;
        } else {
            $name = md5($this->data);
            $GLOBALS['img' . $name] = $this->data;
            return 'var://img' . $name;
        }
    }

    public function width()
    {
        return $this->x;
    }

    public function height()
    {
        return $this->y;
    }

    public function mimeType()
    {
        return $this->mime;
    }

    public static function fromData($data, $mime, $x = null, $y = null)
    {
        $image = new PlImage();
        $image->data = $data;
        $image->mime = $mime;
        $image->x    = $x;
        $image->y    = $y;
        return $image;
    }

    public static function fromFile($path, $mime, $x = null, $y = null)
    {
        $image = new PlImage();
        $image->file = $path;
        $image->mime = $mime;
        $image->x    = $x;
        $image->y    = $y;
        return $image;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
