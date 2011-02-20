#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

require_once './connect.db.inc.php';
require_once 'newsletter.inc.php';
ini_set('memory_limit', '128M');

$issues = NewsLetter::getIssuesToSend();
foreach ($issues as $issue) {
    if ($issue->isEmpty()) {
        echo "Lettre \"{$issue->title()}\" (Groupe {$issue->nl->group}) ignorée car vide.";
    } else {
        echo "Envoi de la lettre \"{$issue->title()}\" (Groupe {$issue->nl->group})\n\n";
        echo ' ' . date("H:i:s") . " -> début de l'envoi\n";
        $emailsCount = $issue->sendToAll();
        echo ' ' . date("H:i:s") . " -> fin de l'envoi\n\n";
        echo $emailsCount . " emails ont été envoyés lors de cet envoi.\n\n";
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
