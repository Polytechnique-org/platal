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

// should be at global level (and not in a function)
// as we need to include lots of globals vars in the external wiki
if ($globals->wiki->wikidir)
{
    ob_start();
    require_once($globals->spoolroot.$globals->wiki->wikidir.'/pmwiki.php');

    $wikiAll = ob_get_clean();
    $i = strpos($wikiAll, "<!--/HeaderText-->");
    $j = strpos($wikiAll, "<!--/PageLeftFmt-->", $i);

    $wikiHeaders = substr($wikiAll, 0, $i);

    $wikiMenu = substr($wikiAll, $i, $j-$i);

    $wikiTxt = substr($wikiAll, $j);

}

?>
