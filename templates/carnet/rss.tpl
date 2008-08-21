{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

{if $article->data}{$article->prenom} {$article->nom} a mis à jours les données suivantes&nbsp;:<br />{$article->data}<br />{/if}
{if !$article->contact and !$article->dcd}
<a href="{#globals.baseurl#}/carnet/contacts?action=ajouter&amp;user={$article->bestalias}&amp;token={$rss_hash}">
  {icon name=add title="Ajouter" full=true} Ajouter &agrave; mes contacts
</a><br />
{/if}
{if !$article->dcd}
<a href="{#globals.baseurl#}/vcard/{$article->bestalias}.vcf">
  {icon name=vcard title="Carte de visite" full=true} T&eacute;l&eacute;charger la carte de visite &eacute;lectronique
</a>
{/if}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
