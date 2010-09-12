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

class ProfileVisibility
{
    static private $v_values = array(self::VIS_PUBLIC  => array(self::VIS_PUBLIC),
                                     self::VIS_AX      => array(self::VIS_AX, self::VIS_PUBLIC),
                                     self::VIS_PRIVATE => array(self::VIS_PRIVATE, self::VIS_AX, self::VIS_PUBLIC));

    const VIS_PUBLIC  = 'public';
    const VIS_AX      = 'ax';
    const VIS_PRIVATE = 'private';

    private $level;

    public function __construct($level = null)
    {
        $this->setLevel($level);
    }

    public function setLevel($level = self::VIS_PUBLIC)
    {
        if ($level != null && $level != self::VIS_PRIVATE && $level != self::VIS_AX && $level != self::VIS_PUBLIC) {
            Platal::page()->kill("Invalid visibility: " . $level);
        }

        // Unlogged or not allowed to view directory_ax or requesting public
        // => public view
        if (!S::logged() || !S::user()->checkPerms('directory_ax') || $level == self::VIS_PUBLIC) {
            $level = self::VIS_PUBLIC;
        // Not allowed to view directory_private or requesting ax
        } else if (!S::user()->checkPerms('directory_private') || $level == self::VIS_AX) {
            $level = self::VIS_AX;
        } else {
            $level = self::VIS_PRIVATE;
        }

        if ($this->level == null || $this->level == self::VIS_PRIVATE) {
            $this->level = $level;
        } else if ($this->level == self::VIS_AX && $level == self::VIS_PRIVATE) {
            return;
        } else {
            $this->level = self::VIS_PUBLIC;
        }
    }

    public function level()
    {
        if ($this->level == null) {
            return self::VIS_PUBLIC;
        } else {
            return $this->level;
        }
    }

    public function levels()
    {
        return self::$v_values[$this->level()];
    }

    public function isVisible($visibility)
    {
        return in_array($visibility, $this->levels());
    }
}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
