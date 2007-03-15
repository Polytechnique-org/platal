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
<h1>Sondage : erreur</h1>

{if !is_null($survey_errors) && is_array($survey_errors)}
<table class="bicol">
  <tr>
    <th colspan='2'>Une ou plusieurs erreurs sont survenues</th>
  </tr>
  {foreach from=$survey_errors item=survey_error}
  <tr class="{cycle values="impair,pair"}">
    <td>&bull; {$survey_error.error}</td>
    <td><a href="survey/edit/question/{$survey_error.question}">corriger</a></td>
  </tr>
  {/foreach}
</table>
{elseif $survey_message neq ""}
  {$survey_message}
{else}
Une erreur inconnue est survenue dans l'&#233;dition de ce sondage. N'hésite pas &#226; <a href='send_bug'>signaler ce bug</a> si il persiste.
{/if}
<br/>
<a href="{$survey_link}">Retour</a>

{* vim:set et sw=2 sts=2 ts=8: *}
