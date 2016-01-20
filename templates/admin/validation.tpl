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

{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}

<script type="text/javascript">//<![CDATA[
{literal}
function toggleField(name, id, obj) {
  $("#" + name + "_" + id).toggle();
}
{/literal}
//]]></script>

<h1>Validation</h1>


{if $vit->total()}

{counter print=false start=0 assign=hidden}

{iterate item=valid from=$vit}
{assign var=type value=$valid->type}
{if !t($hide_requests[$type]) && !($valid->requireAdmin && !$isAdmin)}
<br />
<table class="bicol">
  <tr>
    <th colspan="2"><a id="valid{$valid->id()}"></a>{$valid->type}</th>
  </tr>
  <tr>
    <td class="titre" style="width: 20%">Demandeur&nbsp;:</td>
    <td>
      {if $valid->user->hasProfile()}
      {assign var="profile" value=$valid->user->profile()}
      <a href="profile/{$profile->hrpid}" class="popup2">
      {/if}
        {$valid->user->fullName("promo")}
      {if $valid->user->hasProfile()}
      </a>
      {/if}
    </td>
  </tr>
  {if t($valid->profile) && !$valid->userIsProfileOwner}
  <tr>
    <td class="titre" style="width: 20%">Profil concerné&nbsp;:</td>
    <td>
      <a href="profile/{$valid->profile->hrpid}" class="popup2">
        {$valid->profile->fullName("promo")}
      </a>
    </td>
  </tr>
  {/if}
  <tr>
    <td class="titre" style="width: 20%">Date de demande&nbsp;:</td>
    <td>
      {$valid->stamp|date_format}
    </td>
  </tr>
  {include file=$valid->formu()}
  {if $valid->editor()}
  <tr onclick="toggleField('edit', '{$valid->id()}')" style="cursor: pointer">
    <th colspan="2">
      {if $preview_id neq $valid->id()}
      <div style="float: left">
        {icon name="add"}
      </div>
      {/if}
      Éditer
    </th>
  </tr>
  <tr {if $preview_id neq $valid->id()}style="display: none"{/if} id="edit_{$valid->id()}">
    <td colspan="2" class="center">
      <form enctype="multipart/form-data" action="{$platal->pl_self(0)}/edit/{$valid->id()}#valid{$valid->id()}" method="post">
        {xsrf_token_field}
        <div>
          {include file=$valid->editor()}
          <input type="hidden" name="uid"    value="{$valid->user->id()}" />
          <input type="hidden" name="type"   value="{$valid->type}" />
          <input type="hidden" name="stamp"  value="{$valid->stamp}" />
          <br />
          <input type="submit" name="edit"   value="Éditer" />
        </div>
      </form>
    </td>
  </tr>
  {/if}
  <tr onclick="toggleField('comment', '{$valid->id()}')" style="cursor: pointer">
    <th colspan='2'>
      {if $valid->comments|@count eq 0}
      <div style="float: left">
        {icon name="add"}
      </div>
      {/if}
      Commentaires
    </th>
  </tr>
  {foreach from=$valid->comments item=c}
  <tr class="{cycle values="impair,pair"}">
    <td class="titre">
      <a href="profile/{$c[0]}" class="popup2">{$c[0]}</a>
    </td>
    <td>{$c[1]|nl2br}</td>
  </tr>
  {/foreach}
  <tr {if $valid->comments|@count eq 0}style="display: none"{/if} id="comment_{$valid->id()}">
    <td colspan='2' class='center'>
      <form action="admin/validate" method="post">
        {xsrf_token_field}
        <div>
          <input type="hidden" name="uid"    value="{$valid->user->id()}" />
          <input type="hidden" name="type"   value="{$valid->type}" />
          <input type="hidden" name="stamp"  value="{$valid->stamp}" />
          <input type="hidden" name="formid" value="{0|rand:65535}" />
          <textarea rows="3" cols="50" name="comm"></textarea>
          <br />
          <input type="submit" name="hold"   value="Commenter" />
        </div>
      </form>
    </td>
  </tr>
  <tr>
    <th colspan='2'>
      {if $preview_id neq $valid->id()}
      <div style="float: left">
        {icon name="null"}
      </div>
      {/if}
      Réponse
    </th>
  </tr>
  <tr>
    <td colspan='2' {popup caption="Règles de validation" text=$valid->ruleText()}>
      <form action="admin/validate" method="post">
        {xsrf_token_field}
        <div>
          Réponse préremplie&nbsp;:
          <select onchange="this.form.comm.value=this.value">
            <option value="">&nbsp;</option>
            {foreach from=$valid->answers() item=automatic_answer}
              <option value="{$automatic_answer.answer}">{$automatic_answer.title}</option>
            {/foreach}
          </select>
          <a href="admin/validate/answers">{icon name="page_edit" title="Éditer les réponses automatiques"}</a>
        </div>
        <div class='center'>
          Ajouté dans l'email&nbsp;:<br />
          <textarea rows="5" cols="50" name="comm"></textarea><br />

          <input type="hidden" name="uid"    value="{$valid->user->id()}" />
          <input type="hidden" name="type"   value="{$valid->type}" />
          <input type="hidden" name="stamp"  value="{$valid->stamp}" />
          <input type="submit" name="accept" value="Accepter" />
          {if $valid->refuse}<input type="submit" name="refuse" value="Refuser" />{/if}
          <input type="submit" name="delete" value="Supprimer" />
        </div>
      </form>
    </td>
  </tr>
</table>
{else}
{counter print=false assign=hidden}
{/if}
{/iterate}

{if $hidden}
<p>{$hidden} validation{if $hidden > 1}s ont été masquées{else} a été masquée{/if}.</p>
{/if}

{else}

<p>Rien à valider</p>

{/if}

<p>
  Afficher seulement les validations suivantes&nbsp;:
</p>

<form action="admin/validate" method="post">
  {foreach from=$categories item=type}
    <div style="float:left;width:33%"><input type="checkbox" name="{$type}" id="hide_{$type}"{if !t($hide_requests[$type])} checked="checked"{/if}/>
    <label for="hide_{$type}">{$type}</label></div>
  {/foreach}
  <div class="center" style="clear:left"><input type="submit" name="hide" value="Valider" /></div>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
