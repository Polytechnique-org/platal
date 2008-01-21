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

__autoload('PlWizard');
require_once dirname(__FILE__) . '/xnetevents.inc.php';


// Generic page template {{{1

abstract class XNetEventEditPage implements PlWizardPage
{
    protected $pg_template;
    public $event;

    public function __construct(PlWizard &$wiz)
    {
        if (!isset($_SESSION['new_xnetevent'])) {
            $_SESSION['new_xnetevent'] = new XNetEvent();
        }
        $this->event =& $_SESSION['new_xnetevent'];
    }

    public function template()
    {
        return 'xnetevents/edit.tpl';
    }

    public function prepare(PlatalPage &$page, $id)
    {
        $this->_prepare($page, $id);
        $page->assign('edit_event_page', $this->pg_template);
    }

    protected function _prepare(PlatalPage &$page, $id)
    {
    }
}


// Welcome page {{{1

class XNetEventEditStart extends XNetEventEditPage
{
    protected $pg_template = 'xnetevents/edit-start.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
    }

    public function process()
    {
        return PlWizard::NEXT_PAGE;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
