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


{if $survey.promo}
  <input type="checkbox" name="survey{$survey_id}_{$survey.id}_promo" value="1" id="survey{$survey_id}_{$survey.id}_promo" {if $survey_mode eq 'edit'}disabled="disabled"{/if}/><label for="survey{$survey_id}_{$survey.id}_promo">Je veux indiquer ma promotion</label><br/>
{/if}
{if $survey.name}
  <input type="checkbox" name="survey{$survey_id}_{$survey.id}_name" value="1" id="survey{$survey_id}_{$survey.id}_name" {if $survey_mode eq 'edit'}disabled="disabled"{/if}/><label for="survey{$survey_id}_{$survey.id}_name">Je veux indiquer mon nom et mon pr&#233;nom</label><br/>
{/if}
  <strong>Attention, cocher cette(ces) case(s) d&#233;truit totalement ou en partie l'anonymat de ta r&#233;ponse.</strong>

{* vim:set et sw=2 sts=2 sws=2: *}

