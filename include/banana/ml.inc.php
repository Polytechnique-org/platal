<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

require_once 'banana/banana.inc.php';
require_once 'banana/hooks.inc.php';

function hook_makeLink($params)
{
    global $globals, $platal;
    $base = $globals->baseurl . '/' . $platal->ns . 'lists/archives/' . MLBanana::$listname;
    return $base . hook_platalMessageLink($params);
}

class MLBanana extends Banana
{
    static public $listname;
    static public $domain;

    function __construct($params = null)
    {
        Banana::$spool_boxlist = false;
        Banana::$msgedit_canattach = true;
        array_push(Banana::$msgparse_headers, 'x-org-id', 'x-org-mail');
         
        MLBanana::$listname = $params['listname'];
        MLBanana::$domain   = $params['domain'];
        $params['group'] = $params['listname'] . '@' . $params['domain'];
        parent::__construct($params, 'MLArchive');
    }

    public function run()
    {
        global $platal, $globals;

        $nom  = S::v('prenom') . ' ' . S::v('nom');
        $mail = S::v('bestalias') . '@' . $globals->mail->domain;
        $sig  = $nom . ' (' . S::v('promo') . ')';
        Banana::$msgedit_headers['X-Org-Mail'] = S::v('forlife') . '@' . $globals->mail->domain;

        // Build user profile
        Banana::$profile['headers']['From']         = utf8_encode("$nom <$mail>");
        Banana::$profile['headers']['Organization'] = 'Utilisateur de Polytechnique.org';
        Banana::$profile['signature']               = utf8_encode($sig);
        
        // Page design
        Banana::$page->killPage('forums');
        Banana::$page->killPage('subscribe'); 

        // Run Banana
        return parent::run();
    }
}

require_once('banana/mbox.inc.php');

class BananaMLArchive extends BananaMBox
{
    public function name()
    {
        return 'MLArchives';
    }

    public function filename()
    {
        return MLBanana::$domain . '_' . MLBanana::$listname;
    }

    protected function getFileName()
    {
        global $globals;
        $base = $globals->lists->spool;
        $file = MLBanana::$domain . $globals->lists->vhost_sep . MLBanana::$listname . '.mbox';
        return "$base/$file/$file";
    }
}

?>
