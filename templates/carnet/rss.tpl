{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

{if $article->data}
<p>{profile user=$article->user promo=false link=false} a mis à jours les données suivantes&nbsp;:</p>
<ul>
{foreach from=$article->data item=f}
  <li>{$f}</li>
{/foreach}
</ul>
{/if}
{if !$article->contact and !$article->dead}
<a href="{#globals.baseurl#}/carnet/contacts?action=ajouter&amp;user={$article->hruid}&amp;token={$rss_hash}">
  {icon name=add title="Ajouter" full=true} Ajouter &agrave; mes contacts
</a><br />
{/if}
{if !$article->dead}
<a href="{#globals.baseurl#}/vcard/{$article->profile}.vcf">
  {icon name=vcard title="Carte de visite" full=true} T&eacute;l&eacute;charger la carte de visite &eacute;lectronique
</a>
{/if}
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
