<?php
/*
 * Copyright (C) 2003-2004 Polytechnique.org
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
class flagset {
  /** string that holds the flagset */
  var $value;

  /** the boundary between flags */
  var $sep = ",";


  /** set flag
   * @param $flags services FROM coupures
   * @return VOID
   */
  function flagset( $flags="" ) {
    $this->value = $flags;
  }


  /** add flag
   * @param $flag XXX
   * @return VOID
   */
  function addflag($flag) {
    if (!$flag) return;
    if (!$this->hasflag($flag)) {
      if ($this->value)
        $this->value .= $this->sep;
      $this->value .= $flag;
    }
  }


  /** test si flag ou pas
   * @param $flag XXX
   * @return 1 || 0
   */
  function hasflag($flag) {
    $tok = strtok($this->value,$this->sep);
    while ($tok) {
      if ($tok==$flag) return 1;
      $tok = strtok($this->sep);
    }
    return 0;
  }


  /** remove flag
   * @param $flag XXX
   * @return VOID
   */
  function rmflag($flag) {
    if (!$flag) return;
    $newvalue = "";
    $tok = strtok($this->value,$this->sep);
    while ($tok) {
      if ($tok!=$flag) {
        if ($newvalue)
         $newvalue .= $this->sep;
        $newvalue .= $tok;
      }
      $tok = strtok($this->sep);
    }
    $this->value=$newvalue;
  }

} 

?>
