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

class Visibility
{
    /** Visibility levels.
     * The VIEW_* constants describe the access level
     * The EXPORT_* constants describe the degree of confidentiality of the data.
     */
    const VIEW_NONE = 'none';
    const VIEW_PUBLIC = 'public';
    const VIEW_AX = 'ax';
    const VIEW_PRIVATE = 'private';
    const VIEW_ADMIN = 'admin';

    const EXPORT_PUBLIC = 'public';
    const EXPORT_AX = 'ax';
    const EXPORT_PRIVATE = 'private';
    const EXPORT_HIDDEN = 'hidden';

    /** Map each VIEW_ level to the list of EXPORT_ levels it can view.
     */
    static private $view_levels = array(
        self::VIEW_NONE    => array(),
        self::VIEW_PUBLIC  => array(self::EXPORT_PUBLIC),
        self::VIEW_AX      => array(self::EXPORT_AX, self::EXPORT_PUBLIC),
        self::VIEW_PRIVATE => array(self::EXPORT_PRIVATE, self::EXPORT_AX, self::EXPORT_PUBLIC),
        self::VIEW_ADMIN   => array(self::EXPORT_HIDDEN, self::EXPORT_PRIVATE, self::EXPORT_AX, self::EXPORT_PUBLIC),
    );

    static private $display_levels = array(
        self::EXPORT_PUBLIC => 0,
        self::EXPORT_AX => 1,
        self::EXPORT_PRIVATE => 2,
        self::EXPORT_HIDDEN => 3,
    );

    private $level;

    private function __construct($level)
    {
        $this->level = $level;
    }

    static private $vis_list = array();
    public static function get($level)
    {
        Platal::assert(array_key_exists($level, self::$view_levels), "Invalid visibility access level $level.");
        if (!array_key_exists($level, self::$vis_list)) {
            self::$vis_list[$level] = new Visibility($level);
        }
        return self::$vis_list[$level];
    }

    public function level()
    {
        return $this->level;
    }

    public static function defaultForRead($max_level = null)
    {
        if (!S::logged()) {
            $vis = self::get(self::VIEW_PUBLIC);
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
            $vis = self::get(self::VIEW_NONE);
        } else {
            $vis = S::user()->editVisibility();
        }
        if ($max_level != null) {
            return $vis->restrict($max_level);
        } else {
            return $vis;
        }
    }

    /** Retrieve a 'restricted' version of the current Visibility.
     *
     * @param $level The visibility level to restrict to
     * @return A new Visibility instance, whose level is min($this->level, $level)
     */
    public function restrict($level = null)
    {
        if ($level != null && !$this->isVisible($level)) {
            $level = $this->level();
        }

        return self::get($level);
    }

    public function isVisible($visibility)
    {
        return in_array($visibility, self::$view_levels[$this->level()]);
    }

    public function equals($visibility)
    {
        return $visibility !== null && $this->level() == $visibility->level();
    }

    static public function isLessRestrictive($level_a, $level_b)
    {
        // self::$display_levels is order from least restrictive
        // to most restrictive.
        return self::$display_levels[$level_a] >= self::$display_levels[$level_b];
    }

    /** Compare the visibility of two fields.
     * Returns:
     *   >0 if $a is less restrictive than $b,
     *   <0 if $a is more restrictive than $b,
     *   0  if $a is equal to $b.
     */
    static public function cmpLessRestrictive($a, $b)
    {
        $a_pub = self::$display_levels[$a];
        $b_pub = self::$display_levels[$b];
        /* self::$display_levels is ordered from least restrictive to
         * most restrictive.
         * This will be 0 if both levels are equal, < 0 if $b_pub is less
         * than $a_pub, thus less restrictive, which means that $a comes
         * before $b in descending restrictiveness order.
         */
        return $b_pub - $a_pub;
    }

    static public function comparePublicity($a, $b)
    {
        return self::cmpLessRestrictive($a['pub'], $b['pub']);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
