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



<h1>
  Ma liste personnelle de contacts
</h1>

<form action="carnet/contacts" method="post">
<p>
  Ajouter la personne suivante à ma liste de contacts (prenom.nom) :<br />
  <input type="hidden" name="action" value="ajouter" />
  <input type="text" name="user" size="20" maxlength="70" />&nbsp;
  <input type="submit" value="Ajouter" />
</p>
</form>
<p>
  Tu peux également rajouter des camarades dans tes contacts lors d'une recherche dans l'annuaire : 
  il te suffit de cliquer sur l'icône {icon name=add} en face de son nom dans les résultats !
</p>  

{if $plset_count || $smarty.request.quick}
<p>
Pour récupérer ta liste de contacts dans un PDF imprimable :<br />
(attention, les photos font beaucoup grossir les fichiers !)
</p>
<ul>
  <li>avec les photos :
  [<a href="carnet/contacts/pdf/promo/photos/mescontacts.pdf" class='popup'><strong>tri par promo</strong></a>]
  [<a href="carnet/contacts/pdf/photos/mescontacts.pdf" class='popup'><strong>tri par noms</strong></a>]
  </li>
  <li>sans les photos :
  [<a href="carnet/contacts/pdf/promo/mescontacts.pdf" class='popup'><strong>tri par promo</strong></a>]
  [<a href="carnet/contacts/pdf/mescontacts.pdf" class='popup'><strong>tri par noms</strong></a>]
  </li>
</ul>

<p>
  Tu peux télécharger des informations sur tes contacts :
</p>
<ul>
  <li>
    {icon name=calendar_view_day title='Anniversaires'} 
    <a href="carnet/contacts/ical/{$smarty.session.forlife}/{$smarty.session.core_rss_hash}/anniv-x.ics">
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
      <input type="text" size="30" name="quick" class="quick_search"
             value="{$smarty.request.quick|default:'recherche dans tes contacts'}"
             onfocus="if (this.value == 'recherche dans tes contacts') this.value=''"
             onblur="if (this.value == '') this.value='recherche dans tes contacts'"/>
      <a href="carnet/contacts">{icon name=cross title='Annuler la recherche'}</a>
    </form>
  </div>
  Tu peux faire une recherche sur tes contacts :
</p>

{include file="core/plset.tpl"}

{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
