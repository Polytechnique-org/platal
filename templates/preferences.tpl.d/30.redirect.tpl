{***************************************************************************
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
    $Id: 30.redirect.tpl,v 1.1 2004-11-24 10:12:48 x2000habouzit Exp $
 ***************************************************************************}

<h3><a href="{rel}/carva_redirect.php}">Ma redirection de page WEB</a></h3>
<div class='explication'>
  Tu peux configurer tes redirections WEB
  http://www.carva.org/{dyn s=$smarty.session.bestalias}
  et http://www.carva.org/{dyn s=$smarty.session.forlife}
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
