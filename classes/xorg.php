<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

class Xorg extends Platal
{
    public function __construct()
    {
        parent::__construct(
            'auth',

            'admin',
            'api',
            'axletter',
            'bandeau',
            'carnet',
            'comletter',
            'deltaten',
            'email',
            'epletter',
            'events',
            'forums',
            'fusionax',
            'fxletter',
            'gadgets',
            'geoloc',
            'googleapps',
            'lists',
            'marketing',
            'newsletter',
            'openid',
            'payment',
            'platal',
            'poison',
            'profile',
            'register',
            'reminder',
            'search',
            'sharingapi',
            'stats',
            'survey',
            'urlshortener',
            'wats4u'
        );
    }

    public function find_hook()
    {
        if ($this->path{0} >= 'A' && $this->path{0} <= 'Z') {
            return self::wiki_hook();
        }
        return parent::find_hook();
    }

    public function force_login(PlPage $page)
    {
        global $globals;
        if (!empty($globals->xorgauth->secret)) {
            // Use auth.polytechnique.org if it is configured
            $redirect = S::v('loginX');
            if (!$redirect) {
                $page->trigError('Impossible de s\'authentifier. Problème de configuration de plat/al.');
                return;
            }
            http_redirect($redirect);
        } else {
            // Deprecated local authentication
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            if (S::logged()) {
                $page->changeTpl('core/password_prompt_logged.tpl');
            } else {
                $page->changeTpl('core/password_prompt.tpl');
            }
            $page->assign_by_ref('platal', $this);
            $page->run();
        }
    }

    public function setup_raven()
    {
        $sentry_dsn = self::globals()->core->sentry_dsn;

        if (strlen($sentry_dsn) == 0) {
            return null;
        }

        require_once('raven/lib/Raven/Autoloader.php');

        Raven_Autoloader::register();

        return new Raven_Client($sentry_dsn);
    }

    protected function report_error($error)
    {
        parent::report_error($error);

        $raven = $this->setup_raven();
        if ($raven != null) {
            $raven->captureException($error);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
