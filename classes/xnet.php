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

class Xnet extends Platal
{
    function Xnet()
    {
        $modules = func_get_args();
        call_user_func_array(array(&$this, 'Platal'), $modules);

        global $globals;
        if ($globals->asso()) {
            if ($p = strpos($this->path, '/')) {
                $this->ns   = substr($this->path, 0, $p).'/';
                $this->path = '%grp'.substr($this->path, $p);
            } else {
                $this->ns   = $this->path.'/';
                $this->path = '%grp';
            }
        }
    }

    function find_nearest_key($key, &$array)
    {
        global $globals;
        $k = parent::find_nearest_key($key, $array);
        if (is_null($k) && in_array('%grp', array_keys($array)) && $globals->asso()) {
            return '%grp';
        }
        return $k;
    }

    function near_hook()
    {
        $link = parent::near_hook();
        if (strpos($link, '%grp') !== false) {
            global $globals;
            return str_replace('%grp', $globals->asso('diminutif'), $link);
        }
        return $link;
    }

    function find_hook()
    {
        $ans = parent::find_hook();
        if ($ans && $this->ns) {
            $this->path    = $this->ns . substr($this->path, 5);
            $this->argv[0] = $this->ns . substr($this->argv[0], 5);
        }
        return $ans;
    }

    function force_login(&$page)
    {
        http_redirect(S::v('loginX'));
    }
}

?>
