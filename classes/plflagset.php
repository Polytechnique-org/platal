<?php
/*
 * Copyright (C) 2003-2008 Polytechnique.org
 * http://opensource.polytechnique.org/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


/** class for describing flags
 */
class PlFlagSet
{
    /** string that holds the PlFlagSet */
    private $values = array();

    /** the boundary between flags */
    private $sep;


    /** set flag
     * @param $flags services FROM coupures
     * @return VOID
     */
    public function __construct($flags = '', $sep = ',')
    {
        $this->sep = $sep;
        $splitted = explode($sep, $flags);
        foreach ($splitted as $part) {
            $this->values[$part] = true;
        }
    }


    /** add flag
     * @param $flag XXX
     * @return VOID
     */
    public function addFlag($flag)
    {
        if (empty($flag)) {
            return;
        }
        $this->values[$flag] = true;
    }


    /** test si flag ou pas
     * @param $flag XXX
     * @return 1 || 0
     */
    public function hasFlag($flag)
    {
        return !empty($flag) && isset($this->values[$flag]) && $this->values[$flag];
    }

    /** test flag combination
     */
    public function hasFlagCombination($flag)
    {
        $perms = explode(',', $flag);
        foreach ($perms as $perm)
        {
            $ok = true;
            $rights = explode(':', $perm);
            foreach ($rights as $right) {
                if (($right{0} == '!' && $this->hasFlag(substr($right, 1))) || !$this->hasFlag($right)) {
                    $ok = false;
                }
            }
            if ($ok) {
                return true;
            }
        }
        return false;
    }

    /** remove flag
     * @param $flag XXX
     * @return VOID
     */
    public function rmFlag($flag)
    {
        if (empty($flag)) {
            return;
        }
        if (isset($this->values[$flag])) {
            unset($this->values[$flag]);
        }
    }


    /** return the PlFlagSet
     */
    public function flags()
    {
        $flags = '';
        foreach ($this->values as $key=>$value) {
            if (!empty($flags)) {
                $flags .= $this->sep;
            }
            if ($value) {
                $flags .= $key;
            }
        }
        return $flags;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
