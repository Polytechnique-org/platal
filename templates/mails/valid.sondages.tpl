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
        $Id: valid.sondages.tpl,v 1.2 2004-08-31 11:25:41 x2000habouzit Exp $
 ***************************************************************************}

{config_load file="mails.conf" section="valid_sondages"}
{subject text="[Polytechnique.org/SONDAGE] Demande de validation du sondage $titre par $username"}
{from full=#from#}
{to addr="$username@polytechnique.org"}
{cc full=#cc#}
{if $answer eq "yes"}
Cher(e) camarade,

  Le sondage {$titre} que tu as composé vient d'être validé.
  Il ne te reste plus qu'à transmettre aux sondés l'adresse où ils pourront voter. Cette adresse est : https://www.polytechnique.org/sondages/questionnaire.php?alias={$alias|escape:'url'}

Cordialement,
L'équipe X.org
{elseif $answer eq 'no'}

Cher(e) camarade,

  Le sondage $titre que tu avais proposé a été refusé.
La raison de ce refus est :
{$smarty.request.motif}

Cordialement,
L'équipe X.org
{/if}
{* vim:set et sw=2 sts=2 sws=2: *}
