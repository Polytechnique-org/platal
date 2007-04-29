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

<h1>Validation</h1>
 

{if $vit->total()}

{counter print=false start=0 assign=hidden}

{iterate item=valid from=$vit}
{assign var=type value=$valid->type}
{if !$hide_requests[$type]}
<br />
<table class="bicol">
  <tr>
    <th colspan="2"><a id="valid{$valid->id()}"></a>{$valid->type}</th>
  </tr>
  <tr>
    <td class="titre" style="width: 20%">Demandeur&nbsp;:</td>
    <td>
      <a href="profile/{$valid->bestalias}" class="popup2">
        {$valid->prenom} {$valid->nom} (X{$valid->promo})
      </a>
    </td>
  </tr>
  <tr>
    <td class="titre" style="width: 20%">Date de demande&nbsp;:</td>
    <td>
      {$valid->stamp|date_format}
    </td>
  </tr>
  {include file=$valid->formu()}
  {if $valid->editor()}
  <tr>
    <td colspan="2" class="center">
      {if $preview_id == $valid->id()}
      <form enctype="multipart/form-data" action="{$platal->pl_self()}#valid{$valid->id()}" method="post">
        <div>
          {include file=$valid->editor()}
          <input type="hidden" name="uid"    value="{$valid->uid}" />
          <input type="hidden" name="type"   value="{$valid->type}" />
          <input type="hidden" name="stamp"  value="{$valid->stamp}" />
          <br />
          <input type="submit" name="edit"   value="Editer" />
        </div>
      </form>
      {else}
      <span class="smaller">
        <a href="admin/validate/edit/{$valid->id()}#valid{$valid->id()}">{icon name=page_edit}Editer cette demande avant validation</a>
      </span>
      {/if}
    </td>
  </tr>
  {/if}
  <tr><th colspan='2'>Commentaires</th></tr>
  {foreach from=$valid->comments item=c}
  <tr class="{cycle values="impair,pair"}">
    <td class="titre">
      <a href="profile/{$c[0]}" class="popup2">{$c[0]}</a>
    </td>
    <td>{$c[1]|nl2br}</td>
  </tr>
  {/foreach}
  <tr>
    <td colspan='2' class='center'>
      <form action="admin/validate" method="post">
        <div>
          <input type="hidden" name="uid"    value="{$valid->uid}" />
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
  <tr><th colspan='2'>Réponse</th></tr>
  <tr>
    <td colspan='2' {popup caption="Règles de validation" text=$valid->ruleText()}>
      <form action="admin/validate" method="post">
        <div>
          Réponse préremplie :
          <select onchange="this.form.comm.value=this.value">
            <option value=""></option>
            {foreach from=$valid->answers() item=automatic_answer}
              <option value="{$automatic_answer.answer}">{$automatic_answer.title}</option>
            {/foreach}
          </select>
          <a href="admin/validate/answers">{icon name="page_edit" title="Editer les réponses automatiques"}</a>
        </div>
        <div class='center'>
          Ajouté dans l'email :<br />
          <textarea rows="5" cols="50" name="comm"></textarea><br />

          <input type="hidden" name="uid"    value="{$valid->uid}" />
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
  Afficher seulement les validation suivantes :
</p>

<form action="admin/validate" method="post">
  {foreach from=$categories item=type}
    <div style="float:left;width:33%"><input type="checkbox" name="{$type}" id="hide_{$type}"{if !$hide_requests[$type]} checked="checked"{/if}/>
    <label for="hide_{$type}">{$type}</label></div>
  {/foreach}
  <div class="center" style="clear:left"><input type="submit" name="hide" value="Valider" /></div>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
