<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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
    /** Visibility levels.
     * none => Can't see anything
     * public => Can see public data
     * ax => Can see AX and public data
     * private => Can see private, AX and public data
     * hidden => Can only be seen by admins
     */
    const VIS_NONE    = 'none';
    const VIS_PUBLIC  = 'public';
    const VIS_AX      = 'ax';
    const VIS_PRIVATE = 'private';
    const VIS_HIDDEN  = 'hidden';

    private $level;

    static private $v_levels = array(
        self::VIS_NONE      => array(),
        self::VIS_PUBLIC    => array(self::VIS_PUBLIC),
        self::VIS_AX        => array(self::VIS_AX, self::VIS_PUBLIC),
        self::VIS_PRIVATE   => array(self::VIS_PRIVATE, self::VIS_AX, self::VIS_PUBLIC),
        self::VIS_HIDDEN    => array(self::VIS_HIDDEN, self::VIS_PRIVATE, self::VIS_AX, self::VIS_PUBLIC),
    );

    public function __construct($level = null)
    {
        $this->level = $level;
    }

    public function level()
    {
        if ($this->level == null) {
            return self::VIS_PUBLIC;
        } else {
            return $this->level;
        }
    }

    public static function defaultForRead($max_level = null)
    {
        if (!S::logged()) {
            $vis = new ProfileVisibility(self::VIS_PUBLIC);
        } else {
            $vis = S::user()->readVisibility();
        }
        if ($max_level != null) {
            return $vis->restrict($max_level);
        } else {
            return $vis;
        }
    }

    public static function defaultForEdit($max_level = null)
    {
        if (!S::logged()) {
            $vis = new ProfileVisibility(self::VIS_NONE);
        } else {
            $vis = S::user()->editVisibility();
        }
        if ($max_level != null) {
            return $vis->restrict($max_level);
        } else {
            return $vis;
        }
    }

    /** Retrieve a 'restricted' version of the current ProfileVisibility.
     *
     * @param $level The visibility level to restrict to
     * @return A new ProfileVisibility instance, whose level is min($this->level, $level)
     */
    public function restrict($level = null)
    {
        if ($level != null && !$this->isVisible($level)) {
            $level = $this->level();
        } else {
            $level = $this->level();
        }

        return new ProfileVisibility($level);
    }

    public function levels()
    {
        return self::$v_levels[$this->level()];
    }

    public function isVisible($visibility)
    {
        return in_array($visibility, $this->levels());
    }

    static public function comparePublicity($a, $b)
    {
        $a_pub = new ProfileVisibility($a['pub'], true);
        $b_pub = new ProfileVisibility($b['pub'], true);

        return !$a_pub->isVisible($b_pub->level());
    }
}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
