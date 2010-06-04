{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

<form id="add_user" action="carnet/contacts" method="post">
  <div style="float: right">
    {xsrf_token_field}
    <input type="hidden" name="action" value="ajouter" />
    <input type="text" size="30" name="user" class="quick_search"
           value="ajouter prenom.nom"
           onfocus="if (this.value == 'ajouter prenom.nom') this.value=''; $('#add_button').show()"
           onblur="if (this.value == '') this.value='ajouter prenom.nom'"
           maxlength="70"/>
    <a id="add_button" href="carnet/contacts/add" style="display: none" onclick="document.getElementById('add_user').submit(); return false;">
      {icon name=add title="Ajouter la personne"}
    </a>
  </div>
<p>
  Ajouter à tes contacts&nbsp;:
</p>
</form>
<p style="clear: both">
  Sur la page de résultats d'une recherche, tu peux ajouter un contact en cliquant sur {icon name=add}.
</p>

{if $plset_count || $smarty.request.quick}
<p>
  Tu peux télécharger des informations sur tes contacts&nbsp;:
</p>
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
    Le calendrier des anniversaires&nbsp;:
    <a href="carnet/contacts/ical/{$smarty.session.hruid}/{$smarty.session.token}/anniv-x.ics">
      {icon name=calendar_view_day title='Anniversaires au format iCal'}
    </a>
    <a href="carnet/contacts/csv/birthday/{$smarty.session.hruid}/{$smarty.session.token}/anniv-x.csv">
      {icon name=outlook title='Anniversaires au format Outlook'}
    </a>
  </li>
  <li>
    La carte de visite électronique&nbsp;:
    <a href="carnet/contacts/vcard/photos/MesContactsXorg.vcf">
      {icon name=vcard title='Carte de visite au format vCard'}
    </a>
    (<a href="carnet/contacts/vcard/MesContactsXorg.vcf">sans les photos</a>)
    <a href="carnet/contacts/csv/{$smarty.session.hruid}/{$smarty.session.token}/MesContactsXorg.csv">
      {icon name=outlook title='Contacts au format Outlook'}
    </a>
  </li>
</ul>

<form action="carnet/contacts/search#plset_content" method="get">
  <div style="float: right">
      <input type="text" size="30" name="quick" class="quick_search"
             value="{$smarty.request.quick|default:'recherche dans tes contacts'}"
             onfocus="if (this.value == 'recherche dans tes contacts') this.value=''"
             onblur="if (this.value == '') this.value='recherche dans tes contacts'"/>
      <a href="carnet/contacts">{icon name=cross title='Annuler la recherche'}</a>
  </div>
</form>
<p>
  Rechercher dans tes contacts&nbsp;:
</p>

{include core=plset.tpl}

{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
