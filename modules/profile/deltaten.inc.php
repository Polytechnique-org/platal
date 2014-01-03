<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

class ProfilePageDeltaten extends ProfilePage
{
    protected $pg_template = 'profile/deltaten.tpl';

    public function __construct(PlWizard $wiz)
    {
        parent::__construct($wiz);
        $this->settings['message'] = null;
    }

    protected function _fetchData()
    {
        $res = XDB::query('SELECT  message
                             FROM  profile_deltaten
                            WHERE  pid = {?}',
                          $this->pid());
        $this->values['message'] = $res->fetchOneCell();
    }

    protected function _saveData()
    {
        if ($this->changed['message']) {
            $message = trim($this->values['message']);
            if (empty($message)) {
                XDB::execute('DELETE FROM  profile_deltaten
                                    WHERE  pid = {?}',
                             $this->pid());
                $this->values['message'] = null;
            } else {
                XDB::execute('INSERT INTO  profile_deltaten (pid, message)
                                   VALUES  ({?}, {?})
                  ON DUPLICATE KEY UPDATE  message = VALUES(message)',
                             $this->pid(), $message);
                $this->values['message'] = $message;
            }
        }
    }

    public function _prepare(PlPage $page, $id)
    {
        $page->assign('hrpid', $this->profile->hrpid);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
