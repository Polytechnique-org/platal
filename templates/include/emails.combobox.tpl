{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2009 Polytechnique.org                             *}
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
{if !$isjob}
<tr {if $class}class="{$class}"{/if}>
  <td>
{/if}
    <span class="titre">{if $name eq "email_directory"}Email annuaire AX
    {elseif $name eq "email"}Ajouter une adresse email{else}
    Email professionnel{/if}</span>
{if !$isjob}
  </td>
  {if $name eq "email"}<td></td>{/if}
  <td>
{else}
  <br />
  <span class="flags">
    {include file="include/flags.radio.tpl" name="`$jobpref`[`$prefix`email_pub]" val=$pub}
  </span>
  <br />
{/if}
    <select name="{$name}" id="{$combobox}">
      {if $email_type eq "directory"}
        <optgroup label="Email annuaire AX">
          <option value="{$email_directory}" {if
          $val eq $email_directory}selected="selected"{/if}>{$email_directory}</option>
        </optgroup>
      {/if}
      {if $name eq "email_directory"}
        <optgroup label="Emails polytechniciens">
          {if $melix}
            <option value="{$melix}@{#globals.mail.alias_dom#}" {if
            $val eq $melix|cat:'@'|cat:#globals.mail.alias_dom#}selected="selected"{/if}>
            {$melix}@{#globals.mail.alias_dom#}</option>
            <option value="{$melix}@{#globals.mail.alias_dom2#}" {if
            $val eq $melix|cat:'@'|cat:#globals.mail.alias_dom2#}selected="selected"{/if}>
            {$melix}@{#globals.mail.alias_dom2#}</option>
          {/if}
          {foreach from=$list_email_X item=email}
            <option value="{$email.alias}@{#globals.mail.domain#}" {if
            $val eq $email.alias|cat:'@'|cat:#globals.mail.domain#}selected="selected"{/if}>
            {$email.alias}@{#globals.mail.domain#}</option>
            <option value="{$email.alias}@{#globals.mail.domain2#}" {if
            $val eq $email.alias|cat:'@'|cat:#globals.mail.domain2#}selected="selected"{/if}>
            {$email.alias}@{#globals.mail.domain2#}</option>
          {/foreach}
        </optgroup>
      {/if}
      {if (($name neq "email") && ($list_email_redir|@count neq 0))}
        <optgroup label="Redirections">
          {foreach from=$list_email_redir item=email}
            <option value="{$email}" {if $val eq $email}selected="selected"{/if}>{$email}</option>
          {/foreach}
        </optgroup>
      {/if}
      {if $list_email_pro|@count neq 0}
        <optgroup label="Emails professionels">
          {foreach from=$list_email_pro item=email}
            <option value="{$email}" {if
            $val eq $email}selected="selected"{/if}>{$email}</option>
          {/foreach}
        </optgroup>
      {/if}
      <optgroup label="Autres choix">
        <option value="new@example.org" {if $error}selected="selected"{/if}>Utiliser une autre adresse email</option>
        <option value="" {if (($val eq '') && (!$error))}selected="selected"{/if}>{if
        $name neq "email"}Ne pas mettre d'adresse email{else}&nbsp;{/if}</option>
      </optgroup>
    </select>
    {if $name eq "email_directory"}
      <input type="checkbox" disabled="disabled" checked="checked"/>
      {icon name="flag_orange" title="Visible sur l'annuaire"}
    {elseif ($name neq "email") && (!$isjob)}
      <span class="flags">
        {include file="include/flags.radio.tpl" name="`$jobpref`[`$prefix`email_pub]" val=$pub}
      </span>
    {/if}
    <br />
    <span class="{$new}" style="display: none">
      <input type="text" maxlength="60" {if $error}class="error" value="{$val}"{/if} name="{if (($name neq "email_directory")
      && ($name neq "email"))}jobs[{$i}][{$prefix}email_new]{else}{$name}_new{/if}"/>
    </span>
    <script type="text/javascript">//<![CDATA[
      {literal}
      $(function() {
        var i = {/literal}{$i}{literal};
        $('select#combobox' + i).change(function() {
          $('.new' + i).hide();
          if ($('select#combobox' + i).val() == "new@example.org") {
            $('.new' + i).show();
          }
        }).change();
      });
      {/literal}
    // ]]></script>
{if !$isjob}
  </td>
  {if $name eq "email"}<td></td>{/if}
</tr>
{else}
<br />
{/if}
{if $name neq "email"}
{if !$isjob}
  <tr {if $class}class="{$class} {$new}"{else}class="{$new}"{/if} style="display: none">
    <td colspan="2">
{else}
  <div class="{$new}" style="display: none">
{/if}
      <p><small><strong><em>Attention :</em></strong> cette adresse email figurera dans
      {if $name eq "email_directory"}l'annuaire papier{else}tes informations professionnelles
      {/if} mais n'est pas ajoutée à la liste de tes redirections. Nous te conseillons fortement de
      <strong><a href="emails/redirect">l'ajouter là</a></strong>, surtout
      si tu n'en as plus de valide.</small></p>
{if !$isjob}
    </td>
  </tr>
{else}
  </div>
{/if}
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
