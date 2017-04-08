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

{assign var=new value="new"|cat:$i}
{assign var=combobox value="combobox"|cat:$i}
<tr{if $class} class="{$class}"{/if}{if t($divId)} id="{$divId}"{/if}>
  <td class="titre">
  {if $name eq "email_directory"}
    Email&nbsp;annuaire&nbsp;AX
  {elseif $name eq "email"}
    Ajouter&nbsp;une&nbsp;adresse&nbsp;email
  {else}
    Email&nbsp;professionnel
  {/if}
  </td>
  {if $name eq "email"}<td></td>{/if}
  <td>
    {if $name neq "email"}<div style="float: left">{/if}
    {if $emails_count neq 0}
    <select name="{$name}" id="{$combobox}">
      {foreach from=$email_lists item=email_list key=key}
      {if $email_list|@count}
      <optgroup label="{$key}">
        {foreach from=$email_list item=email}
        <option value="{$email}" {if $val eq $email}selected="selected"{/if}>{$email}</option>
        {/foreach}
      </optgroup>
      {/if}
      {/foreach}
      <optgroup label="Autres choix">
        <option value="{#Profile::EXAMPLE_EMAIL#}" {if ($val eq '' && !$error && $name eq 'email') || $error}selected="selected"{/if}>Nouvelle adresse email</option>
        {if $name neq "email"}<option value="" {if $val eq '' && !$error}selected="selected"{/if}>Ne pas mettre d'adresse email</option>{/if}
      </optgroup>
    </select>
    {else}
    <input type="text" maxlength="255" {if $error}class="error" value="{$val}"{/if} name="{$name}"/>
    {/if}
    {if $name neq "email"}
    </div>
    <div style="float: right" class="flags">
    {if $name eq "email_directory"}
      <input type="checkbox" disabled="disabled" checked="checked"/>
      {icon name="flag_orange" title="Visible sur l'annuaire"}
    {elseif $name neq "email"}
    {if t($mainField)}
    {include file="include/flags.radio.tpl" name="`$jobpref`[`$prefix`email_pub]" val=$pub
             mainField=$mainField mainId=$mainId subField=$subField subId=$subId}
    {else}
    {include file="include/flags.radio.tpl" name="`$jobpref`[`$prefix`email_pub]" val=$pub}
    {/if}
    {/if}
    </div>
    {/if}
  </td>
  {if $name eq "email"}<td></td>{/if}
</tr>
<tr {if $class}class="{$class} {$new}"{else}class="{$new}"{/if} style="display: none">
  <td></td>
  {if $name eq "email"}<td></td>{/if}
  <td>
    <span class="{$new}" style="display: none">
      <input type="text" maxlength="255" {if $error}class="error" value="{$val}"{/if} name="{if (($name neq "email_directory")
      && ($name neq "email"))}jobs[{$i}][{$prefix}email_new]{else}{$name}_new{/if}"/>
    </span>
    <script type="text/javascript">//<![CDATA[
      {literal}
      $(function() {
        var i = {/literal}{$i}{literal};
        $('select#combobox' + i).change(function() {
          $('.new' + i).hide();
          if ($('select#combobox' + i).val() == '{/literal}{#Profile::EXAMPLE_EMAIL#}{literal}') {
            $('.new' + i).show();
          }
        }).change();
      });
      {/literal}
    // ]]></script>
  </td>
  {if $name eq "email"}<td></td>{/if}
</tr>
{if $name neq "email"}
<tr {if $class}class="{$class} {$new}"{else}class="{$new}"{/if} style="display: none">
  <td colspan="2">
    <small><strong><em>Attention :</em></strong> cette adresse email figurera dans
    {if $name eq "email_directory"}l'annuaire papier{else}tes informations professionnelles
    {/if} mais n'est pas ajoutée à la liste de tes redirections. Nous te conseillons fortement de
    <strong><a href="emails/redirect">l'ajouter là</a></strong>, surtout
    si tu n'en as plus de valide.</small>
  </td>
</tr>
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
