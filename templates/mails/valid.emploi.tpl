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
        $Id: valid.emploi.tpl,v 1.2 2004-08-31 11:25:41 x2000habouzit Exp $
 ***************************************************************************}

{config_load file="mails.conf" section="valid_emploi"}
{subject text="[Polytechnique.org/EMPLOI] Annonce emploi $entreprise"}
{from full=#from#}
{cc full=#cc#}
{if $answer eq "yes"}
Bonjour,

L'annonce « {$titre} » a été acceptée par les modérateurs. Elle apparaîtra dans le forum emploi du site.

Nous vous remercions d'avoir proposé cette annonce

Cordialement,
L'équipe Polytechnique.org
{elseif $answer eq 'no'}
Bonjour,

L'annonce « {$titre} » a été refusée par les modérateurs.

Cordialement,
L'équipe Polytechnique.org
{/if}
{* vim:set et sw=2 sts=2 sws=2: *}
