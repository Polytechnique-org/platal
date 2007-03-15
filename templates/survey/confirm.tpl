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
<h1>Sondage : confirmation</h1>

<form action="{$survey_formaction}" method="post">
  {if is_array($survey_formhidden)}
    {foreach from=$survey_formhidden item=s_value key=s_key}
  <input type="hidden" name="survey_{$s_key}" value="{$s_value}"/>
    {/foreach}
  {/if}
  {if $survey_message neq ""}
    {$survey_message}
  {else}
    Une confirmation est requise
  {/if}
  <br/>
  <input type="submit" name="survey_submit" value="Confirmer"/>
  <input type="submit" name="survey_cancel" value="Annuler"/>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
