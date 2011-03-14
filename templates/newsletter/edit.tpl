{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

<h1>
  {$nl->name} de {$issue->date|date_format:"%B %Y"}
</h1>

{if !$art}

<p>
[<a href="{$nl->adminPrefix()}">liste</a>]
[<a href="{$nl->prefix()}/show/{$issue->id()}">visualiser</a>]

</p>

<form action='{$nl->adminPrefix()}/edit/{$issue->id(true)}/update' method='post'>
  <table class="bicol" cellpadding="3" cellspacing="0">
    <tr>
      <th colspan='2'>
        Propriétés de la newsletter
      </th>
    </tr>
    <tr>
      <td class='titre'>
        État
      </td>
      <td>
{if $issue->isPending()}
  En attente d'envoi
  {if $nl->automaticMailingEnabled()}
    [<a href="{$nl->adminPrefix()}/edit/cancel/{$issue->id()}?token={xsrf_token}" onclick="return confirm('Es-tu sûr de vouloir annuler l\'envoi de ce message&nbsp;?');">{*
    *}{icon name=delete} Annuler l'envoi</a>]
  {/if}
{elseif $issue->isEditable()}
  En cours d'édition

  {if $nl->automaticMailingEnabled()}
    [<a href="{$nl->adminPrefix()}/edit/valid/{$issue->id()}?token={xsrf_token}" onclick="return confirm('Es-tu sûr de vouloir déclencher l\'envoi de ce message&nbsp;? Tu ne pourras plus le modifier après cela.');">{*
    *}{icon name=tick} Valider l'envoi</a>]
  {/if}

  [<a href="{$nl->adminPrefix()}/edit/delete/{$issue->id()}?token={xsrf_token}" onclick="return confirm('Es-tu sûr de vouloir supprimer cette lettre&nbsp;? Toutes les données en seront perdues.');">{*
  *}{icon name=cross} Supprimer</a>]
{else}
  Envoyée
{/if}
      </td>
    </tr>
    <tr>
      <td class='titre'>
        ID
      </td>
      <td>
        {$issue->id}
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Nom
      </td>
      <td>
        {if $issue->isEditable()}
          <input type='text' size='16' name='shortname' value="{$issue->shortname}" />
          <span class="smaller">(Ex&nbsp;: 2006-06 pour la NL de juin 2006)</span>
        {else}
          {$issue->shortname}
        {/if}
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Titre de l'email
      </td>
      <td>
        <input type='text' size='60' name='title_mail' value="{$issue->title(true)}" />
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Titre
      </td>
      <td>
        <input type='text' size='60' name='title' value="{$issue->title()}" />
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Date
      </td>
      <td>
      {if $issue->isEditable()}
        {valid_date name="date" value=$issue->date from=0 to=60}
      {else}
        {$issue->date}
      {/if}
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Intro de la lettre<br />(ou contenu pour les lettres exceptionnelles)
      </td>
      <td>
        <textarea name='head' cols='60' rows='20'>{$issue->head()}</textarea>
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Signature de la lettre
      </td>
      <td>
        <input type='text' size='60' name='signature' value="{$issue->signature}" />
      </td>
    </tr>
    {if $nl->automaticMailingEnabled() && ($issue->isEditable() || $issue->isPending())}
    <tr>
      <td class='titre'>
        Date d'envoi
      </td>
      <td>
        {if $issue->isEditable()}
        Le {valid_date name="send_before_date" value=$issue->getSendBeforeDate() from=3 to=15} vers {html_select_time prefix="send_before_time_" time=$issue->getSendBeforeTime() display_hours=true display_minutes=false display_seconds=false display_meridian=false use_24_hours=true} heures
        {else}
        Le {$issue->send_before|date_format:"%d/%m/%Y vers %Hh"}
        {/if}
      </td>
    </tr>
    {/if}
    {if $issue->isEditable()}
      {if $nl->criteria->hasFlag('promo')}
      <tr>
        <td class='titre'>
          Promotions
        </td>
        <td>
          <script type="text/javascript">/*<![CDATA[*/
            {literal}
            function updatepromofields(egal1) {
              var f = egal1.form;
              f.egal2.disabled = f.promo2.disabled = egal1.value == '=';
              f.egal2.readOnly = true;
              if (f.egal1.value == '>=') {
                f.egal2.value = '<=';
              } else {
                f.egal2.value = '>=';
              }
            }
            $(function() { updatepromofields($('select[name=egal1]')[0]); });
            {/literal}
          /*]]>*/</script>
          <select name="egal1" onchange="updatepromofields(this)" style="text-align:center">
            <option value="=" {if $smarty.request.egal1 eq "="}selected="selected"{/if}>&nbsp;=&nbsp;</option>
            <option value="&gt;=" {if $smarty.request.egal1 eq "&gt;="}selected="selected"{/if}>&nbsp;&gt;=&nbsp;</option>
            <option value="&lt;=" {if $smarty.request.egal1 eq "&lt;="}selected="selected"{/if}>&nbsp;&lt;=&nbsp;</option>
          </select>
          <input type="text" name="promo1" size="4" maxlength="4" value="{$smarty.request.promo1}" />
          &nbsp;et&nbsp;
          <input type="text" name="egal2" size="1" style="text-align:center" value="{if t($smarty.request.egal2) eq '&lt;'}&lt;{else}&gt;{/if}" readonly="readonly" />
          <input type="text" name="promo2" size="4" maxlength="4" value="{$smarty.request.promo2}" />
        </td>
      </tr>
      {/if}
      {if $nl->criteria->hasFlag('axid')}
      <tr>
        <td>Matricule AX</td>
        <td>
          <textarea name="axid" rows="10" cols="12">{$smarty.request.axid}</textarea>
          <br />
          <i>Entrer une liste de matricules AX (un par ligne)</i><br />
          <input type="checkbox" name="axid_reversed" id="axid_reversed" {if $smarty.request.axid_reversed}checked="checked"{/if} value="1" />
          Inverser la sélection <i>(sélectionner dans l'intervalle de promotions, à l'exception des matricules indiqués)</i>
        </td>
      </tr>
      {/if}
    {/if}
    <tr class='center'>
      <td colspan='2'>
        <input type='submit' name='submit' value='Sauver' />
      </td>
    </tr>
  </table>
</form>

<br />

<table class="bicol" cellpadding="3" cellspacing="0">
  <tr>
    <td>
      Créer un nouvel article&hellip;
    </td>
    <td style='vertical-align:middle; border-left: 1px gray solid' class="center">
      <a href="{$nl->adminPrefix()}/edit/{$issue->id}/new#edit">{icon name=add title="créer"}</a>
    </td>
  </tr>
  {foreach from=$issue->arts item=arts key=cat}
  <tr>
    <th>
      {$issue->category($cat)|default:"[no category]"}
    </th>
    <th></th>
  </tr>
  {foreach from=$arts item=art}
  <tr class="{cycle values="impair,pair"}">
    <td>
      <pre>{$art->toText('%hash%','%login%')}</pre>
    </td>
    <td style="vertical-align: middle; border-left: 1px gray solid; text-align: center">
      <small><strong>Pos:&nbsp;{$art->pos}</strong></small><br />
      <a href="{$nl->adminPrefix()}/edit/{$issue->id}/{$art->aid}/edit#edit">
        {icon name="page_edit" title="Editer"}
      </a>
      <br /><br /><br />
      <a href="{$nl->adminPrefix()}/edit/{$issue->id}/{$art->aid}/delete"
         onclick="return confirm('Es-tu sûr de vouloir supprimer cet article&nbsp;?')">
        {icon name="delete" title="Supprimer"}
      </a>
    </td>
  </tr>
  {/foreach}
  {/foreach}
</table>

<br />

<form action="{$nl->adminPrefix()}/edit/{$issue->id(true)}/blacklist_check" method="post">
  <table class="bicol" cellpadding="3" cellspacing="0">
    <tr>
      <th colspan="2">
        Vérifier les url et adresses emails sur Spamhaus
      </th>
    </tr>
    {if $ips_to_check|@count > 0}
    {foreach from=$ips_to_check item=ip_list key=title}
    {foreach from=$ip_list item=domain key=ip}
    <tr>
      <td>{$title}</td>
      <td><a href="{#globals.mail.blacklist_check_url#}{$ip}">{$domain}</a></td>
    </tr>
    {assign var=title value=''}
    {/foreach}
    {/foreach}
    {else}
    <tr class="center">
      <td colspan="2">
        <input type="submit" value="Vérifier" />
      </td>
    </tr>
    {/if}
  </table>
</form>

{else}

<p>
[<a href="{$nl->adminPrefix()}/edit/{$issue->id}">retour</a>]
</p>

<table class='bicol'>
  <tr><th>Version texte</th></tr>
  <tr id='text'>
  <td><pre>{$art->toText()}</pre></td>
  </tr>
  <tr><th>Version html</th></tr>
  <tr id='html'>
    <td>
      <div class='nl'>
        {$art->toHtml()|smarty:nodefaults}
      </div>
    </td>
  </tr>
</table>

<br />

<form action="{$nl->adminPrefix()}/edit/{$issue->id}/{$art->aid}/edit#edit" method="post">
  <table class='bicol'>
    <tr>
      <th colspan='2'>
        <a id='edit'></a>Éditer un article
      </th>
    </tr>
    <tr class="impair">
      <td class='titre'>Sujet</td>
      <td>
        <input size='60' type='text' value="{$art->title()}" name='title' />
      </td>
    </tr>
    <tr class="impair">
      <td class='titre'>Catégorie</td>
      <td>
        <select name='cid'>
          <option value='0'>-- none --</option>
          {foreach from=$nl->cats item=text key=cid}
          <option value='{$cid}' {if $art->cid eq $cid}selected="selected"{/if}>{$text}</option>
          {/foreach}
        </select>
      </td>
    </tr>
    <tr class="impair">
      <td class='titre'>Position</td>
      <td>
        <input type='text' value='{$art->pos}' name='pos' />
      </td>
    </tr>
    <tr class="pair">
      <td class='titre'>Contenu</td>
      <td>
        <textarea cols="68" rows="10" name='body'>{$art->body()}</textarea>
      </td>
    </tr>
    <tr class="impair">
      <td class='titre'>Ajouts (emails, contacts, tarifs, site web&hellip;)</td>
      <td>
        <textarea cols="68" rows="6" name='append'>{$art->append()}</textarea>
      </td>
    </tr>
    <tr class="pair smaller">
      <td></td>
      <td>
        <a href="wiki_help/notitle" class="popup3">{icon name=information} Voir la documentation du wiki</a>
      </td>
    </tr>
    <tr class='pair'>
      <td colspan='2' class='center'>
        <input type='submit' value='visualiser' />
        <input type='submit' name='save' value='Sauver' />
      </td>
    </tr>
  </table>
</form>

{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
