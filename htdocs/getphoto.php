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
new_skinned_page('login.tpl', AUTH_PUBLIC);

if (Env::has('x')) {
    if (Env::get('req') == "true") {
        include 'validations.inc.php';
        $res = $globals->xdb->query("SELECT id FROM aliases WHERE alias = {?}", Env::get('x'));
	$myphoto = PhotoReq::get_request($a = $res->fetchOneCell());
        Header('Content-type: image/'.$myphoto->mimetype);
	echo $myphoto->data;
    } else {
        $res = $globals->xdb->query(
                "SELECT  attachmime, attach
                   FROM  photo   AS p
             INNER JOIN  aliases AS a ON p.uid=a.id
                  WHERE  alias={?}", Env::get('x'));

	if( list($type,$data) = $res->fetchOneRow() ) {
	    Header(  "Content-type: image/$type");
	    echo $data;
	} else {
	    Header(  'Content-type: image/png');
	    echo file_get_contents(dirname(__FILE__).'/images/none.png');
	}
    }
}
?>
