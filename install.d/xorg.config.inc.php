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
    $Id: xorg.config.inc.php,v 1.1 2004-11-22 06:49:07 x2000habouzit Exp $
 ***************************************************************************/

// {{{ BDD

/** BDD overrides */
#$globals->dbhost='djali.polytechnique.org';
$globals->debug  = true;
$globals->dbuser = '***';
$globals->dbpwd  = '***';
// }}}
// {{{ Paths

/* URL de la racine pour les mails contenant des URL (pas de slash final!) */
$globals->baseurl   = "http://dev.m4x.org/~x2000habouzit";
$globals->root      = "/home/x2000habouzit/dev/public/";
$globals->spoolroot = "/home/x2000habouzit/dev/public/";

// }}}
// {{{ Extra

# JPF
$globals->econfiance = '***';

/* les parametres pour se connecter au serveur NNTP */
if (!isset($news_server)) $news_server="localhost";
if (!isset($news_port)) $news_port=119;
if (!isset($news_auth_pass)) $news_auth_pass="***";
$news_web_user = '***';
$news_web_pass = '***';

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
