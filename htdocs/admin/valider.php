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
 ***************************************************************************
        $Id: valider.php,v 1.6 2004-11-22 20:04:36 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
require_once("validations.inc.php");
new_admin_page('admin/valider.tpl');

if(isset($_REQUEST["uid"]) and isset($_REQUEST["type"])
        and isset($_REQUEST["stamp"])) {
    $req = Validate::get_request($_REQUEST["uid"],$_REQUEST['type'],$_REQUEST["stamp"]);
    if($req)
        $page->assign('mail', $req->handle_formu());
}

$it = new ValidateIterator ();

$valids = Array();
while($valids[] = $it->next());
array_pop($valids);

$page->assign_by_ref('valids', $valids);

$page->run();
?>
