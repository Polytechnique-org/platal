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
        $Id: search.result.public.tpl,v 1.6 2004-10-12 19:54:36 x2000habouzit Exp $
 ***************************************************************************}

<div class="nom">
  {if $result.epouse}{$result.epouse} {$result.prenom} <br />({$result.nom} {$result.prenom}){else}{$result.nom} {$result.prenom}{/if}
  {if $result.decede == 1}(décédé){/if}
</div>
<div class="appli">
  {strip}
  (X {$result.promo}
  {if $result.app0text},
    {applis_fmt type=$result.app0type text=$result.app0text url=$result.app0url}
  {/if}
  {if $c.app1text},
    {applis_fmt type=$result.app1type text=$result.app1text url=$result.app1url}
  {/if})
  {/strip}
</div>
{* vim:set et sw=2 sts=2 sws=2: *}
