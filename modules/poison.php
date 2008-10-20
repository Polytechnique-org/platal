<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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



class PoisonModule extends PLModule
{
    function handlers()
    {
        return array(
            'pe'          => $this->make_hook('poison', AUTH_PUBLIC, 'user', NO_HTTPS),
            'pem'         => $this->make_hook('mailto', AUTH_PUBLIC, 'user', NO_HTTPS),
        //    'per'         => $this->make_hook('rand', AUTH_PUBLIC, 'user', NO_HTTPS),
        );
    }

    function handler_poison(&$page, $seed = null, $count = 20)
    {
        $this->load('poison.inc.php');
        if ($seed == null) {
            $seed = time();
        }
        $emails = get_poison_emails($seed, $count);

        foreach ($emails as $email) {
            echo $email . "\n";
        }
        exit;
    }

    function handler_mailto(&$page, $seed = null, $count = 20)
    {
        global $globals;

        $this->load('poison.inc.php');
        if ($seed == null) {
            $seed = time();
        }
        $emails = get_poison_emails($seed, $count);

        echo '<html><head></head><body>';
        foreach ($emails as $email) {
            echo "<a href=\"mailto:$email\" >$email</a>". "\n";
        }
        echo '<a href="' . $globals->baseurl . '/pem/' . md5($seed) . '">suite</a></body></html>';
        exit;
    }

    function handler_rand(&$page) {
        $this->load('poison.inc.php');
        randomize_poison_file();
        exit;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
