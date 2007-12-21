{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}

{assign var="step" value=$smarty.session.sub_state.step}
<div class="descr center">
  <strong>Procédure d'inscription&nbsp;:</strong>
  {if !$step}<span class="erreur">{/if}Charte{if !$step}</span>{/if} »
  {if $step eq 1 || $step eq 2}<span class="erreur">{/if}Identification{if $step eq 1 || $step eq 2}</span>{/if} »
  {if $step eq 4 || $step eq 3}<span class="erreur">{/if}Pré-Inscription{if $step eq 4 || $step eq 3}</span>{/if} »
  {if $step eq 5}<span class="erreur">{/if}Validation{if $step eq 5}</span>{/if}
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
