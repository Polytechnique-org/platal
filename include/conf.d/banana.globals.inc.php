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
    $Id: banana.globals.inc.php,v 1.2 2004/12/01 14:25:44 x2000habouzit Exp $
 ***************************************************************************/

// {{{ class SkinConfig

class BananaConfig
{
    var $server       = 'localhost';
    var $port         = 119;
    var $password     = '***';
    var $web_user     = '***';
    var $web_pass     = '***';

    var $table_prefix = 'banana_';
}

// }}}

$this->banana = new BananaConfig;

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
