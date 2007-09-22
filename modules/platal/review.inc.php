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

__autoload('PlWizard');

class ReviewPage implements PlWizardPage
{
    public function __construct(PlWizard &$wiz) { }
    public function template() { return 'platal/review.tpl'; }
    public function process() { }

    public function prepare(PlatalPage &$page, $id)
    {
        require_once 'wiki.inc.php';
        $dom = (@$GLOBALS['IS_XNET_SITE'] ? 'ReviewXnet' : 'Review') . '.' . ucfirst($id);
        wiki_require_page($dom);
        $page->assign('cacheExists', wiki_work_dir() . '/cache_' . $dom . '.tpl');
        $page->assign('article', $dom);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
