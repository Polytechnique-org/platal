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

{include file="lists/header_listes.tpl" on=moderate}

<h1>
  Inscriptions en attente de modération
</h1>

{if $subs|@count}
<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>Nom</th>
    <th>Adresse</th>
    <th></th>
  </tr>
  {foreach from=$subs item=s}
  <tr class='{cycle values="pair,impair"}'>
    <td>{$s.name}{if $s.login}
      <a href="profile/{$s.login}" class="popup2">{*
        *}{icon name=user_suit title="Afficher la fiche"}</a>
      {/if}
    </td>
    <td>{$s.addr}</td>
    <td class='action'>
      <a href='{$platal->pl_self(1)}?sadd={$s.id}&amp;token={xsrf_token}'>{icon name=tick title="Valider l'inscription"}</a>
      <a href='{$platal->pl_self(1)}?sid={$s.id}'>{icon name=delete title="Refuser l'inscription"}</a>
    </td>
  </tr>
  {/foreach}
</table>
{else}
<p>Il n'y a pas d'inscriptions en attente de modération.</p>
{/if}

<h1>
  Emails en attente de modération
</h1>

{if $mails|@count}
<ul>
  <li>
  <strong>{icon name=tick}accepter&nbsp;:</strong> l'email est immédiatement libéré, et envoyé à la
  liste.
  </li>
  <li>
  <strong>{icon name=magnifier}refuser&nbsp;:</strong> pour refuser un email, suivre le lien {icon name=magnifier} et
  remplir le formulaire en bas de page.
  </li>
  <li>
  <strong>{icon name=delete}spam&nbsp;:</strong> l'email est effacé sans autre forme de procès.
  N'utiliser <strong>QUE</strong> pour les virus et les emails indésirables.<br/>
  </li>
</ul>
<p>
  S'il y a trop d'indésirables, il est probablement plus rapide pour la suite de les
  jeter directement et non de les modérer en modifant le réglage de
  l'<a href="{$platal->ns}lists/options/{$platal->argv[1]}#antispam">antispam</a>.
</p>

<script type="text/javascript">//<![CDATA[
{literal}
var toggleState = false;
function toggleAll() {
  toggleState = !toggleState;
  var boxes = $(":checkbox.moderate_email");
  if (toggleState) {
    boxes.attr("checked", "checked");
  } else {
    boxes.removeAttr("checked");
  }
}
{/literal}
//]]></script>

<form method="post" action="{$platal->pl_self(1)}">
{xsrf_token_field}
{if $with_fromx}
<table class="bicol" style="margin-bottom: 1ex">
  <tr>
    <th colspan="2"></th>
    <th>Email</th>
    <th>Infos</th>
    <th colspan="2"></th>
  </tr>
  <tr>
    <th class="smaller" colspan="6">
      Les emails suivants proviennent d'adresses identifiées comme étant celles de camarades.
    </th>
  </tr>
  {foreach from=$mails item=m name=mail}
  {if $m.fromx}
  <tr class='{cycle values="pair,impair"}'>
    <td class="checkboxToggle">
      <input type="checkbox" name="select_mails[{$m.id}]" {if $smarty.foreach.mail.total eq 1}checked="checked"{/if}/>
    </td>
    <td class="checkboxToggle">
      <strong>De&nbsp;:</strong><br />
      <strong>Sujet&nbsp;:</strong>
    </td>
    <td class="checkboxToggle">
      {$m.sender}<br />
      {$m.subj|hdc|smarty:nodefaults}
    </td>
    <td class='right checkboxToggle'>
      <small>le {$m.stamp|date_format:"%x"} à {$m.stamp|date_format:"%X"}<br />
      {$m.size} octets</small>
    </td>
    <td class='action'>
      <a href='{$platal->pl_self(1)}?mid={$m.id}&amp;mok=1&amp;token={xsrf_token}'>{icon name=tick title="Accepter le message"}</a>
    </td>
    <td class='action'>
      <a href='{$platal->pl_self(1)}?mid={$m.id}'>{icon name=magnifier title="Voir le message"}</a><br/>
      <a href='{$platal->pl_self(1)}?mid={$m.id}&amp;mdel=1&amp;token={xsrf_token}'>{icon name=delete title="Spam !"}</a>
    </td>
  </tr>
  {/if}
  {/foreach}
</table>
{/if}

{if $with_nonfromx}
<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>
      <a href="javascript:toggleAll()">{icon name="arrow_refresh" title="Tout (dé)cocher"}</a>
    </th>
    <th></th>
    <th>Email</th>
    <th>Infos</th>
    <th colspan="2"></th>
  </tr>
  {foreach from=$mails item=m name=mail}
  {if !$m.fromx}
  <tr class='{cycle values="pair,impair"}'>
    <td class="checkboxToggle">
      <input type="checkbox" class="moderate_email" name="select_mails[{$m.id}]" {if $smarty.foreach.mail.total eq 1}checked="checked"{/if}/>
    </td>
    <td class='checkboxToggle'>
      <strong>De&nbsp;:</strong><br />
      <strong>Sujet&nbsp;:</strong>
    </td>
    <td class='checkboxToggle'>
      {$m.sender}<br />
      {$m.subj|hdc|smarty:nodefaults}
    </td>
    <td class='right checkboxToggle'>
      <small>le {$m.stamp|date_format:"%x"} à {$m.stamp|date_format:"%X"}<br />
      {$m.size} octets</small>
    </td>
    <td class='action'>
      <a href='{$platal->pl_self(1)}?mid={$m.id}&amp;mok=1&amp;token={xsrf_token}'>{icon name=tick title="Accepter le message"}</a>
    </td>
    <td class='action'>
      <a href='{$platal->pl_self(1)}?mid={$m.id}'>{icon name=magnifier title="Voir le message"}</a><br/>
      <a href='{$platal->pl_self(1)}?mid={$m.id}&amp;mdel=1&amp;token={xsrf_token}'>{icon name=delete title="Spam !"}</a>
    </td>
  </tr>
  {/if}
  {/foreach}
</table>
{/if}

<script type="text/javascript">//<![CDATA[
{literal}
$('.checkboxToggle').click(function (event)
  {
    // Don't uncheck the checkbox when clicking it
    if (event.target.tagName === 'INPUT') {
      return;
    }

    var checkbox = $(this).parent().find(':checkbox');

    checkbox = checkbox.attr('checked', !checkbox.attr('checked'));

    event.stopPropagation();
  });
{/literal}
//]]></script>

<p class="center desc">
  Utilise ces boutons pour appliquer une action à tous les emails sélectionnés.<br />
  <input type="hidden" name="moderate_mails" value="1" />
  <input type="submit" name="mok" value="Accepter" />
  <input type="submit" name="mdel" value="Spam !" />
</p>
</form>
{else}
<p>Il n'y a pas d'emails en attente de modération.</p>
{/if}


{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
