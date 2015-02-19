<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

function get_poison_emails($seed, $count)
{
    global $globals;

    $fd   = fopen($globals->poison->file, 'r');
    $size = fstat($fd);
    $size = $size['size'];
    $seed = crc32($seed . date('m-Y')) % $size;
    if ($seed < 0) {
        $seed = $size + $seed;
    }

    fseek($fd, $seed);
    fgets($fd);
    $emails = array();
    $i = 0;
    while (!feof($fd) && $i < $count) {
        $line = trim(fgets($fd));
        if (strlen($line) > 0) {
            $emails[] = $line;
            ++$seed;
        }
        ++$i;
    }
    fclose($fd);
    return $emails;
}

function randomize_poison_file()
{
    global $globals;

    $fd = fopen($globals->poison->file, 'r');
    $entries = array();
    while (!feof($fd)) {
        $line = trim(fgets($fd));
        if (strlen($line) > 0) {
            $m1 = $line . '@' . $globals->mail->domain;
            $entries[$m1] = md5($m1);
            $m2 = $line . '@' . $globals->mail->domain2;
            $entries[$m2] = md5($m2);
        }
    }
    fclose($fd);

    asort($entries);
    $fd = fopen($globals->poison->file . '.rand', 'w');
    foreach ($entries as $key => $value) {
        fwrite($fd, "$key\n");
    }
    fclose($fd);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
