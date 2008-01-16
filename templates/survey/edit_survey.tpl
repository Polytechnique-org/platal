{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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
<h1>Sondage&nbsp;: {if $survey_type == 'root'}nouveau sondage{else}nouvelle question{/if}</h1>

<form action="{$survey_formaction}" method="post">
  <input type="hidden" name="survey_action" value="{$survey_action}"/>
  <input type="hidden" name="survey_qid" value="{$survey_qid}"/>
  <table class="bicol" id="survey_form">
    {include file="survey/edit_$survey_type.tpl"}
  </table>
  <div class="center">
    <input type="submit" name="survey_submit" value="{if $survey_type == 'newsurvey'}Continuer{else}Valider{/if}"/>
    <input type="reset" name="survey_reset" value="R&#233;initialiser"/>
    <input type="submit" name="survey_cancel" value="Annuler"/>
  </div>
</form>

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}
