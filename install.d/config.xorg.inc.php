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
        $Id: config.xorg.inc.php,v 1.3 2004-08-31 11:19:51 x2000habouzit Exp $
 ***************************************************************************/

/* $Id: config.xorg.inc.php,v 1.3 2004-08-31 11:19:51 x2000habouzit Exp $ */

/* URL de la racine pour les mails contenant des URL (pas de slash final!) */
if (!isset($baseurl)) $baseurl="http://dev.m4x.org";

/* pour empêcher les mises à jour de la base de donnée depuis le site */
$no_update_bd = false;
/* les parametres pour se connecter à la BDD */
$globals->dbhost='localhost';
$globals->dbdb = 'x4dat';
$globals->dbuser =  $no_update_bd ? 'webro' : 'web';
$globals->dbpwd="*******";
$globals->root="...";
$globals->libroot="...";

/* les parametres pour se connecter au serveur NNTP */
if (!isset($news_server)) $news_server="localhost";
if (!isset($news_port)) $news_port=119;
if (!isset($news_auth_pass)) $news_auth_pass="***";

/* acces a la page marketing */
$marketing_admin = array();

/* définir sur le site de dev */
if (!isset($site_dev)) $site_dev=true;

$globals->spoolroot="***";

// legacy
$dbhost = $globals->dbhost;  // recherche.php : 303
$dbuser = $globals->dbuser;
$dbpwd = $globals->dbpwd;
$xdat = $globals->dbdb;

/* domaines et mails utilisés */
$globals->domaine_mail='domaine.org' ;
$globals->domaine_mail_alias=array('alias.net','alias.org') ; // seul le 1er apparaît dans la BD
$globals->addr_mail_valid_alias='validation+alias@'.$globals->domaine_mail ;

?>
