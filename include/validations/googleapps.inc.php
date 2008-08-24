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

class GoogleAppsUnsuspendReq extends Validate
{
    private $account;
    public $rules = "Bien faire attention à la raison de la suspension. Si le compte a été suspendu par Google,
                  alors la raison s'affichera (mais seulement 24-48h après la suspension).
                  Si l'utilisateur a désactivé lui-même son compte, la raison sera toujours vierge.";

    public function __construct(User $_user)
    {
        parent::__construct($_user, true, 'gapps-unsuspend');
    }

    public function sendmail($isok)
    {
        // Using overloading to prevent the validation from sending emails, as a valid
        // unsuspend will automatically generate an email when commited through
        // the Google Apps provisioning API.
        if (!$isok) {
            parent::sendmail($isok);
        }
    }

    public function formu()
    {
        return 'include/form.valid.gapps-unsuspend.tpl';
    }

    protected function _mail_subj()
    {
        return "[Polytechnique.org] Demande de réactivation de ton compte Google Apps";
    }

    protected function _mail_body($isok)
    {
        if (!$isok) {
            return "  La demande que tu avais faite de réactivation de compte Google Apps a été refusée.";
        }
    }

    public function commit()
    {
        require_once dirname(__FILE__) . '/../googleapps.inc.php';
        $account = new GoogleAppsAccount($this->user);
        return $account->do_unsuspend();
    }

    public function suspension_reason()
    {
        $res = XDB::query(
            "SELECT  g_suspension
               FROM  gapps_accounts
              WHERE  g_account_name = {?}",
            $this->user->login());
        return $res->fetchOneCell();
    }
}

/* vim: set expandtab shiftwidth=4 tabstop=4 softtabstop=4 foldmethod=marker enc=utf-8: */
?>
