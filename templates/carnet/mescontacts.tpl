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



<h1>
  Ma liste personnelle de contacts
</h1>

<p>
  <div style="float: right">
  <form id="add_user" action="carnet/contacts" method="post">
    {xsrf_token_field}
    <div>
    <input type="hidden" name="action" value="ajouter" />
    <input type="text" size="30" name="user" class="quick_search"
           value="ajouter prenom.nom"
           onfocus="if (this.value == 'ajouter prenom.nom') this.value=''"
           onblur="if (this.value == '') this.value='ajouter prenom.nom'"
           size="20" maxlength="70"/>
    <a href="" onclick="document.getElementById('add_user').submit(); return false;">
      {icon name=add title="Ajouter la personne"}
    </a>
    </div>
  </form>
  </div>
  Ajouter à tes contacts&nbsp;:
</p>
<p style="clear: both">
    Tu peux également ajouter un(e) camarade à tes contacts en cliquant sur l'icône {icon name=add} en
    face de son nom dans les résultats d'une recherche dans l'annuaire !
</p>

<p>
  Tu peux télécharger des informations sur tes contacts&nbsp;:
</p>
{if $plset_count || $smarty.request.quick}
<ul>
  <li>Tes contacts en PDF, sans les photos&nbsp;:
  [<a href="carnet/contacts/pdf/promo/mescontacts.pdf" class='popup'><strong>tri par promo</strong></a>]
  [<a href="carnet/contacts/pdf/mescontacts.pdf" class='popup'><strong>tri par noms</strong></a>]
  </li>
  <li>Avec les photos (attention fichier plus gros)&nbsp;:
  [<a href="carnet/contacts/pdf/promo/photos/mescontacts.pdf" class='popup'><strong>tri par promo</strong></a>]
  [<a href="carnet/contacts/pdf/photos/mescontacts.pdf" class='popup'><strong>tri par noms</strong></a>]
  </li>
  <li>
    {icon name=calendar_view_day title='Anniversaires'} 
    <a href="carnet/contacts/ical/{$smarty.session.forlife}/{$smarty.session.core_rss_hash}/anniv-x.ics" title="Anniversaires">
      Le calendrier des anniversaires
    </a>
  </li>
  <li>
    {icon name=vcard title='Carte de visite'} 
    <a href="carnet/contacts/vcard/photos/MesContactsXorg.vcf">La carte de visite électronique</a>
    (<a href="carnet/contacts/vcard/MesContactsXorg.vcf">sans les photos</a>)
  </li>
</ul>

<p>
  <div style="float: right">
    <form action="carnet/contacts/search#plset_content" method="get">
      <div>
      <input type="text" size="30" name="quick" class="quick_search"
             value="{$smarty.request.quick|default:'recherche dans tes contacts'}"
             onfocus="if (this.value == 'recherche dans tes contacts') this.value=''"
             onblur="if (this.value == '') this.value='recherche dans tes contacts'"/>
      <a href="carnet/contacts">{icon name=cross title='Annuler la recherche'}</a>
      </div>
    </form>
  </div>
  Rechercher dans tes contacts&nbsp;:
</p>

{include file="core/plset.tpl"}

{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
