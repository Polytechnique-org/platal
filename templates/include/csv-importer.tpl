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

{if $form_title}
<h1>{$form_title}</h1>
{/if}

<script type="text/javascript">//<![CDATA[
{literal}
  function showValue(key, box)
  {
    var span_value = document.getElementById('csv_user_value_span[' + key + ']');
    var span_cond  = document.getElementById('csv_cond_value_span[' + key + ']');
    var i    = box.selectedIndex;
    if (box.options[i].value == "user_value") {
      span_value.style.display = "";
      span_cond.style.display = "none";
    } else if(box.options[i].value == "cond_value") {
      span_value.style.display = "none";
      span_cond.style.display = "";
    } else {
      span_value.style.display = "none";
      span_conf.style.display = "none";
    }
  }
  function showCond(key, box)
  {
    var line = document.getElementById('csv_cond_value[' + key + ']');
    var i    = box.selectedIndex;
    if (box.options[i].value == "defined") {
      line.style.display = "none";
    } else {
      line.style.display = "";
    }
  }
  function gotoPage(page)
  {
    document.getElementById('csv_next_page').value = page;
    document.getElementById('csv_form').submit();
    return false;
  }
{/literal}
//]]></script>
<form action="{$csv_path}" method="post" id="csv_form">
<table class="cadre_a_onglet" cellpadding="0" cellspacing="0" style="width: 98%; margin-left:1%;">
  <tr>
    <td>
      <ul id="onglet">
        {if $csv_page eq 'source'}
        <li class="actif">1 - Choisir<br />la source</li>
        {else}
        <li><a href="{$csv_path}" onclick="return gotoPage('source');">1 - Choisir<br />la source</a></li>
        {/if}
        {if $csv_page eq 'values'}
        <li class="actif">2 - Définir<br />les valeurs</li>
        {elseif $smarty.session.csv}
        <li><a href="{$csv_path}" onclick="return gotoPage('values');">2 - Définir<br />les valeurs</a></li>
        {else}
        <li>2 - Définir<br />les valeurs</li>
        {/if}
        {if $csv_page eq 'valid'}
        <li class="actif">3 - Vérifier<br />et valider</li>
        {elseif $csv_action}
        <li><a href="{$csv_path}" onclick="return gotoPage('valid');">3 - Vérifier<br />et valider</a></li>
        {else}
        <li>3 - Vérifier<br />et valider</li>
        {/if}
      </ul>
    </td>
  </tr>
  <tr>
    <td class="conteneur_tab">
      <table style="width: 100%">
        <tr>
          <td>
    {if $csv_page eq 'source'}
      <textarea name="csv_source" rows="20" cols="80">{$smarty.session.csv|default:$smarty.session.csv_source}</textarea><br />
      Entrez les données sous la forme suivante (avec
      <input type="text" name="csv_separator" value="{$smarty.session.csv_separator|default:";"}" maxlength="1" size="1" />
      comme séparateur) :<br/>
      <pre class="center">TITRE1{$smarty.session.csv_separator|default:";"}TITRE2{$smarty.session.csv_separator|default:";"}...
val1_1{$smarty.session.csv_separator|default:";"}val1_2{$smarty.session.csv_separator|default:";"}...
val2_1{$smarty.session.csv_separator|default:";"}val2_2{$smarty.session.csv_separator|default:";"}...
val3_1{$smarty.session.csv_separator|default:";"}val3_2{$smarty.session.csv_separator|default:";"}...</pre>
    {elseif $csv_page eq 'values'}
  <div class="center">
    Action à effectuer si l'entrée existe : 
    <select name="csv_action" onchange="this.form.submit()">
      <option value="insert" {if $smarty.session.csv_action eq 'insert'}selected="selected"{/if}>
        ne rien faire
      </option>
      <option value="replace" {if $smarty.session.csv_action eq 'replace'}selected="selected"{/if}>
        remplacer par la nouvelle entrée
      </option>
      {if $csv_key}
      <option value="update" {if $smarty.session.csv_action eq 'update'}selected="selected"{/if}>
        mettre à jour les champs sélectionnés
      </option>
      {/if}
    </select>
  </div>
  <table class="bicol">
    <tr>
      <th>Champ</th>
      <th colspan="2">Valeur</th>
      {if $smarty.session.csv_action eq 'update'}
      <th>MàJ</th>
    {/if}
    </tr>
    {foreach from=$csv_fields item=f}
    <tr class="{cycle values="pair,impair"}">
      <td>{$csv_field_desc[$f]|default:$f}</td>
      <td>
        <select name="csv_value[{$f}]" onchange="showValue('{$f}', this);">
          <option value="" {if !$smarty.session.csv_value[$f]}selected="selected"{/if}>
            Vide
          </option>
          <option value="user_value" {if $smarty.session.csv_value[$f] eq "user_value"}selected="selected"{/if}>
            Entrer la valeur
          </option>
          <option value="cond_value" {if $smarty.session.csv_value[$f] eq "cond_value"}selected="selected"{/if}>
            Valeur conditionnelle
          </option>
          <optgroup label="Colonnes du CSV">
            {foreach from=$csv_index item=col}
            <option value="{$col}" {if $smarty.session.csv_value[$f] eq $col}selected="selected"{/if}>{$col}</option>
            {/foreach}
          </optgroup>
          {if $csv_functions|count}
          <optgroup label="Fonctions">
            {foreach from=$csv_functions key=func item=desc}
            <option value="{$func}" {if $smarty.session.csv_value[$f] eq $func}selected="selected"{/if}>{$desc.desc}</option>
            {/foreach}
          </optgroup>
          {/if}
        </select>
      </td>
      <td>
        <span id="csv_user_value_span[{$f}]" {if $smarty.session.csv_value[$f] neq "user_value"}style="display: none"{/if}>
          <input type="text" name="csv_user_value[{$f}]" value="{$smarty.session.csv_user_value[$f]}" />
        </span>
        <span id="csv_cond_value_span[{$f}]" {if $smarty.session.csv_value[$f] neq "cond_value"}style="display: none"{/if}>
          Si
          <select name="csv_cond_field[{$f}]">
            {foreach from=$csv_index item=col}
            <option value="{$col}" {if $smarty.session.csv_cond_field_value[$f] eq $col}selected="selected"{/if}>
              {$col}
            </option>
            {/foreach}
          </select>
          <select name="csv_cond[{$f}]" onchange="showCond('{$f}', this)">
            <option value="defined" {if $smarty.session.csv_cond[$f] eq "defined"}selected="selected"{/if}>
              défini
            </option>
            <option value="equals" {if $smarty.session.csv_cond[$f] eq "equals"}selected="selected"{/if}>
              est égale à
            </option>
            <option value="contains" {if $smarty.session.csv_cond[$f] eq "contains"}selected="selected"{/if}>
              contient
            </option>
            <option value="contained" {if $smarty.session.csv_cond[$f] eq "contained"}selected="selected"{/if}>
              est contenu dans
            </option>
            <option value="greater" {if $smarty.session.csv_cond[$f] eq "greater"}selected="selected"{/if}>
              supérieur à
            </option>
            <option value="greater_or_equal" {if $smarty.session.csv_cond[$f] eq "greater_or_equal"}selected="selected"{/if}>
              supérieur ou égal à
            </option>
            <option value="lower" {if $smarty.session.csv_cond[$f] eq "lower"}selected="selected"{/if}>
              inférieur à
            </option>
            <option value="lower_or_equal" {if $smarty.session.csv_cond[$f] eq "lower_or_equal"}selected="selected"{/if}>
              inférieur ou égal à
            </option>
          </select>
          <span id="csv_cond_value[{$f}]" {if $smarty.session.csv_cond[$f] eq "defined" || !$smarty.session.csv_cond[$f]}style="display: none"{/if}>
            <input type="text" name="csv_cond_value[{$f}]" value="{$smarty.session.csv_cond_value[$f]}" />
          </span>
          <br />Alors <input type="text" name="csv_cond_then[{$f}]" value="{$smarty.session.csv_cond_then[$f]}" />
          <br />Sinon <input type="text" name="csv_cond_else[{$f}]" value="{$smarty.session.csv_cond_else[$f]}" />
        </span>
      </td>
      {if $smarty.session.csv_action eq 'update'}
      <td class="center">
        <input type="checkbox" name="csv_update[{$f}]" {if $smarty.session.csv_update[$f]}checked="checked"{/if} />
      </td>
      {/if}
    </tr>
    {/foreach}
  </table>
  {elseif $csv_page eq 'valid'}
  {if !$csv_done}
  <table class="bicol">
    <tr>
      {foreach from=$csv_fields item=f}
      <th>{$csv_field_desc[$f]|default:$f}</th>
      {/foreach}
    </tr>
    {foreach from=$csv_preview item=assoc}
    <tr class="{cycle values="pair,impair"}">
      {foreach from=$csv_fields item=f}
      <td>{$assoc[$f]}</td>
      {/foreach}
    <tr>
    {/foreach}
  </table>
  {else}
  Les données ont été ajoutées.
  {/if}
  {/if}
    </td>
  </tr>

  {if !$csv_done}
  <tr>
    <td class="center">
    <input type="hidden" name="csv_page" value="{$csv_page}" />
    <input type="hidden" id="csv_next_page" name="csv_next_page" value="{$csv_page}" />
    {if $csv_page eq 'source'}
    <input type="submit" name="csv_valid" value="Changer le CSV" />
    {elseif $csv_page eq 'values'}
    <input type="submit" name="csv_valid" value="Aperçu" />
    {elseif $csv_page eq 'valid'}
    <input type="submit" name="csv_valid" value="Valider" />
    {/if}
    </td>
  </tr>
  {/if}
  </table>
    </td>
  </tr>
</table>
</form>

{* vim:set et sws=2 sts=2 sw=2 enc=utf-8: *}
