{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

    <tr>
      <td class="titre">Titre</td>
      <td><input type="text" name="survey_question[title]" size="50" maxlength="200" value="{$survey_current.title}"/></td>
    </tr>
    <tr>
      <td class="titre">Commentaire</td>
      <td><textarea name="survey_question[description]" rows="5" cols="60">{$survey_current.description}</textarea></td>
    </tr>
    <tr>
      <td></td>
      <td class="smaller">
        <a href="wiki_help/notitle" class="popup3">
          {icon name=information title="Syntaxe wiki"} Voir la syntaxe wiki autorisée pour le commentaire du sondage
        </a>
      </td>
    </tr>
    <tr>
      <td class="titre">Date de fin</td>
      <td>
        {valid_date name="survey_question[end]" value=$survey_current.end to=90}
    <script type="text/javascript">//<![CDATA[
      {literal}
      $(document).ready(function() {
        function hidePromo(value) {
          if (value == "0" || value == "") {
            $("#ln_promo").hide();
            $("#ln_promo_exp").hide();
          } else {
            $("#ln_promo").show();
            $("#ln_promo_exp").show();
          }
        }
        $("[name='survey_question[mode]']").change(function() { hidePromo(this.value); });
        hidePromo({/literal}"{$survey_current.mode}"{literal});
      });
      {/literal}
    //]]></script>
      </td>
    </tr>
    <tr>
      <td class="titre">Type de sondage</td>
      <td>
        <select name="survey_question[mode]">
          {foreach from=$survey_modes item=text key=name}
          <option value="{$name}" {if $name eq $survey_current.mode}selected="selected"{/if}>{$text}</option>
          {/foreach}
        </select>
      </td>
    </tr>
    <tr id="ln_promo">
      <td class="titre">Promotions</td>
      <td><input type="text" name="survey_question[promos]" size="50" maxlength="200" value="{$survey_current.promos}"/></td>
    </tr>
    <tr id="ln_promo_exp">
      <td></td>
      <td class="smaller">
        Exemple&nbsp;: 1954,1986-1989,-1942,2000-&nbsp;&nbsp;&nbsp;restreindra le sondage à toutes les promotions suivantes&nbsp;:<br/>
        1954, 1986 à 1989, toutes jusqu'à 1942 et toutes à partir 2000 (les bornes sont systématiquement incluses).<br />
        Pour sélectionner toutes les promotions, laisser le vide.
      </td>
    </tr>

{* vim:set et sw=2 sts=2 ts=8 fenc=utf-8: *}
