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

require_once('xorg.inc.php');
new_nonhtml_page('rss.tpl', AUTH_PUBLIC);

require_once('rss.inc.php');

$rss = $globals->xdb->iterator(
        'SELECT  e.id, e.titre, e.texte, e.creation_date
           FROM  evenements AS e
          WHERE  FIND_IN_SET(flags, "valide") AND peremption >= NOW()
                 AND (e.promo_min = 0 || e.promo_min <= {?})
                 AND (e.promo_max = 0 || e.promo_max >= {?})
       ORDER BY  (e.promo_min != 0 AND e.promo_max != 0) DESC, e.peremption',
       Env::getInt('promo', 3000), Env::getInt('promo'));
$page->assign('rss', $rss);

header('Content-Type: text/xml; charset=utf-8');
$page->run();
?> 
