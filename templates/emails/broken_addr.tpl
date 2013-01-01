{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2013 Polytechnique.org                             *}
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

<h1>Pattes cassées</h1>

<p>
  La liste de pattes cassées (adresses de redirections en panne)
  doit comprendre un email par ligne.
</p>

<form method="post" action="{$platal->ns}admin/emails/broken" enctype="multipart/form-data">
  {xsrf_token_field}
  <div>
  <textarea name="list" cols="60" rows="30">{if $valid_emails || $invalid_emails}{foreach from=$valid_emails item=email}{$email}
{/foreach}

{foreach from=$invalid_emails item=email}{$email}
{/foreach}{/if}</textarea><br />
  <input type="submit" name="sort_broken"
         value="Trie et supprime les doublons de la liste d'adresses" /><br />
  <input type="submit" name="process_broken"
         value="Traite les adresses en panne et met à jour la BDD" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
