{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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
  Lettre de Polytechnique.org de {$nl->_date|date_format:"%B %Y"}
</h1>

{if !$art}

<p>
[<a href="{"admin/newsletter.php"|url}">liste</a>]
[<a href="{"newsletter/show.php"|url}?nid={$nl->_id}">visualiser</a>]
</p>

<form action='{$smarty.server.PHP_SELF}?nid={$nl->_id}' method='post'>
  <table class="bicol" cellpadding="3" cellspacing="0">
    <tr>
      <th colspan='2'>
        Propriétés de la newsletter
      </th>
    </tr>
    <tr>
      <td class='titre'>
        ID
      </td>
      <td>
        {$nl->_id}
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Titre
      </td>
      <td>
        <input type='text' size='60' name='title' value="{$nl->title()}" />
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Date d'envoi
      </td>
      <td>
        <input type='text' size='60' name='date' value="{$nl->_date}" />
      </td>
    </tr>
    <tr>
      <td class='titre'>
        Intro de la lettre
      </td>
      <td>
        <textarea name='head' cols='60' rows='6'>{$nl->head()}</textarea>
      </td>
    </tr>
    <tr class='center'>
      <td colspan='2'>
        <input type='submit' name='update' value='sauver' />
      </td>
    </tr>
  </table>
</form>

<br />

<table class="bicol" cellpadding="3" cellspacing="0">
  <tr>
    <td>
      Créer un nouvel article ...
    </td>
    <td style='vertical-align:middle; border-left: 1px gray solid'>
      [<a href="{$smarty.server.PHP_SELF}?nid={$nl->_id}&amp;edit_aid=-1#edit">créer</a>]
    </td>
  </tr>
  {foreach from=$nl->_arts item=arts key=cat}
  <tr>
    <th>
      {$nl->_cats[$cat]|default:"[no cat]"}
    </th>
    <th></th>
  </tr>
  {foreach from=$arts item=art}
  <tr class="{cycle values="impair,pair"}">
    <td>
      <pre>{$art->toText($smarty.session.prenom,$smarty.session.nom,$smarty.session.femme)|smarty:nodefaults}</pre>
    </td>
    <td style='vertical-align:middle; border-left: 1px gray solid'>
      <strong>Pos: {$art->_pos}</strong><br />
      [<a href="{$smarty.server.PHP_SELF}?nid={$nl->_id}&amp;edit_aid={$art->_aid}#edit">edit</a>]<br />
      [<a href="{$smarty.server.PHP_SELF}?nid={$nl->_id}&amp;del_aid={$art->_aid}">delete</a>]
    </td>
  </tr>
  {/foreach}
  {/foreach}
</table>

{else}

<p>
[<a href="?nid={$nl->_id}">retour</a>]
</p>

{if !$art->check()}<p class='erreur'>article trop long !</p>{/if}
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

<form action="{$smarty.server.REQUEST_URI}#edit" method="post">
  <table class='bicol'>
    <tr>
      <th colspan='2'>
        <a id='edit'></a>Editer un article
        <input type='hidden' name='aid' value='{$smarty.get.edit_aid}' />
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
          {foreach from=$nl->_cats item=text key=cid}
          <option value='{$cid}' {if $art->_cid eq $cid}selected="selected"{/if}>{$text}</option>
          {/foreach}
        </select>
      </td>
    </tr>
    <tr class="impair">
      <td class='titre'>Position</td>
      <td>
        <input type='text' value='{$art->_pos}' name='pos' />
      </td>
    </tr>
    <tr class="pair">
      <td class='titre'>Contenu</td>
      <td>
        <textarea cols="68" rows="10" name='body'>{$art->body()}</textarea>
      </td>
    </tr>
    <tr class="impair">
      <td class='titre'>Ajouts (emails, contacts, tarifs, site web, ...)</td>
      <td>
        <textarea cols="68" rows="6" name='append'>{$art->append()}</textarea>
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


{* vim:set et sw=2 sts=2 sws=2: *}
