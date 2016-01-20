#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

require_once dirname(__FILE__) . '/connect.db.inc.php';

global $globals;

$opt = getopt('p:o:h');

if(empty($opt['p']) || empty($opt['o']) || isset($opt['h'])) {
    echo <<<EOF
usage: lists.create_promo.php -p promo -o owner
       create mailing list for promo "promo" with initial owner "owner"

EOF;
    exit;
}

// Retrieves list parameters.
$promo = intval($opt['p']);
$owner = $opt['o'];

$owner_user = User::getSilent($owner);
if (!$owner_user) {
    echo "Supplied owner is not valid, aborting.\n";
    exit(1);
}

// Creates the list.
$req = new ListeReq($owner_user, false, "promo", $promo . '.' . $globals->mail->domain, "Liste de la promotion $promo",
                    1 /*private*/, 2 /*moderate*/, 0 /*free subscription*/,
                    array($owner), array());
$req->submit();
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
