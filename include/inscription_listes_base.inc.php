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
        $Id: inscription_listes_base.inc.php,v 1.3 2004-10-10 13:51:17 x2000habouzit Exp $
 ***************************************************************************/

include('xml-rpc-client.inc.php');

/** inscrit l'uid donnée à la promo
 * @param $uid UID
 * @param $promo promo
 * @return reponse MySQL
 * @see admin/RegisterNewUser.php
 * @see step4.php
 */
function inscription_listes_base($uid,$pass,$promo) {
  global $globals;
  // récupération de l'id de la liste promo
  $client = new xmlrpc_client("http://$uid:$pass@localhost:4949");
  $client->subscribe('polytechnique.org',"promo$promo");
  $client->subscribe_nl();
}

?>
