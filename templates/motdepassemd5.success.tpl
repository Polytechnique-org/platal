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
        $Id: motdepassemd5.success.tpl,v 1.3 2004-08-31 11:25:39 x2000habouzit Exp $
 ***************************************************************************}


<div class="rubrique">
  Changer de mot de passe
</div>

<p>
<strong>Mot de passe enregistré le {dyn s=$smarty.now|date_format:"%x"}</strong>
</p>
<p>
  <strong>Attention!</strong> Il est crypté irréversiblement,
  donc <strong>non récupérable</strong>. Pour retrouver un accès au site
  consécutivement à une perte de mot de passe, la procédure
  est longue et laborieuse...
</p>


{* vim:set et sw=2 sts=2 sws=2: *}
