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
        $Id: xorg.table-editor.inc.php,v 1.4 2004-10-08 11:30:10 x2000habouzit Exp $
 ***************************************************************************/

require_once('diogenes.table-editor.inc.php');

class XOrgAdminTableEditor extends DiogenesTableEditor {
    function XOrgTableEditor($table,$idfield,$editid=false) {
        $this->DiogenesTableEditor($table,$idfield,$editid);
    }

    function assign($var_name, $contenu) {
        global $page;
        $page->assign($var_name, $contenu);
    }
    
    function run() {
        global $page;
        parent::run($page);
        $page->run();
    }
}

?>
