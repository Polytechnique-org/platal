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
  Lettre de Polytechnique.org du {$nl->_date|date_format}
</h1>

<p>
[<a href='nl'>liste des lettres</a>]
{if $smarty.get.text}
[<a href='nl/show/{$nl->id()}'>version HTML</a>]
{else}
[<a href='nl/show/{$nl->id()}?text=1'>version Texte</a>]
{/if}
{if $smarty.session.perms eq admin}
[<a href='admin/newsletter/edit/{$nl->id()}'>Editer</a>]
{/if}
</p>

<form method="post" action="{$platal->path}">
  <div class='center'>
    <input type='submit' value="me l'envoyer" name='send' />
  </div>
</form>

<fieldset>
<legend>{$nl->title(true)}</legend>
  {if $smarty.get.text}
  <pre style="width : 72ex; margin: auto">{$nl->toText($smarty.session.prenom, $smarty.session.nom, $smarty.session.femme)}
  </pre>
  {else}
  <div class='nl'>
    {$nl->toHtml($smarty.session.prenom, $smarty.session.nom, $smarty.session.femme,
                     false, true)|smarty:nodefaults}
  </div>
  {/if}
</fieldset>

{* vim:set et sw=2 sts=2 sws=2: *}
