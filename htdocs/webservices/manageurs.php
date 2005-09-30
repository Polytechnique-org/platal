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
require_once('webservices/manageurs.server.inc.php');

$ips = array_flip(explode(' ',$globals->manageurs->authorized_ips));
if($ips && isset($ips[$_SERVER['REMOTE_ADDR']])){
  $server = xmlrpc_server_create();

  xmlrpc_server_register_method($server, "get_annuaire_infos", "get_annuaire_infos");
  xmlrpc_server_register_method($server, "get_nouveau_infos", "get_nouveau_infos");

  $request = $GLOBALS['HTTP_RAW_POST_DATA'];
  $response = xmlrpc_server_call_method($server, $request, null);
  header('Content-Type: text/xml');
  print $response;
  xmlrpc_server_destroy($server);

}
?>
