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
        $Id: valid.epouses.tpl,v 1.4 2004-11-17 10:14:32 x2000habouzit Exp $
 ***************************************************************************}

{config_load file="mails.conf" section="valid_epouses"}
{subject text="[Polytechnique.org/EPOUSE] Changement de nom de mariage de $forlife"}
{from full=#from#}
{to addr="$forlife@polytechnique.org"}
{cc full=#cc#}
{if $answer eq "yes"}
Chère camarade,

  La demande de changement de nom de mariage que tu as demandée vient d'être effectuée.

{if $oldepouse}  Les alias {$oldepouse}@polytechnique.org et {$oldepouse}@m4x.org ont été supprimés.
{/if}
  De plus, les alias {$epouse}@polytechnique.org et {$epouse}@m4x.org ont été créés.

Cordialement,
L'équipe X.org
{elseif $answer eq 'no'}
Chère camarade,

  La demande de changement de nom de mariage que tu avais faite a été refusée.
{if $smarty.request.motif}
La raison de ce refus est :
{$smarty.request.motif}
{/if}

Cordialement,
L'équipe X.org
{/if}
{* vim:set et sw=2 sts=2 sws=2: *}
