{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: mescontacts.tpl,v 1.4 2004/11/18 15:17:42 x2000habouzit Exp $
 ***************************************************************************}


{dynamic}
<p class="erreur">{$erreur}</p>

<h1>
  Ma liste personnelle de contacts
</h1>

<form action="{$smarty.server.REQUEST_URI}" method="post">
<p>
  Ajouter la personne suivante à ma liste de contacts (prenom.nom) :<br />
  <input type="hidden" name="action" value="ajouter" />
  <input type="text" name="user" size="20" maxlength="70" />&nbsp;
  <input type="submit" value="Ajouter" />
</p>
</form>
<p>
  Tu peux également rajouter des camarades dans tes contacts lors d'une recherche dans l'annuaire : 
  il te suffit de cliquer sur l'icône <img src="{"images/ajouter.gif"|url}" alt="ajout contact" /> en face de son nom dans les résultats !
</p>  

{if $nb_contacts || $trombi}
<p>
  Pour récupérer ta liste de contacts dans un PDF imprimable :<br />
  [<a href="mescontacts_pdf.php/mes_contacts.pdf?order=promo" class='popup'><strong>Triée par promo</strong></a>]
  [<a href="mescontacts_pdf.php/mes_contacts.pdf" class='popup'><strong>Triée par noms</strong></a>]
</p>

{if $trombi}

<h1>
  Mon trombino de contacts
</h1>

<p>
Pour afficher la liste détaillée de tes contacts: [<a href="{$smarty.server.PHP_SELF}"><strong>vue classique</strong></a>]
</p>

{$trombi->show()|smarty:nodefaults}

{else}

<h1>
  Vue classique des contacts
</h1>

<p>
Pour afficher le trombi de tes contacts : [<a href="?trombi=1"><strong>vue sous forme de trombi</strong></a>]
</p>

<br />

<div class="contact-list">
{foreach item=contact from=$contacts}
{include file=include/minifiche.tpl c=$contact show_action="retirer"}
{/foreach}
</div>

{/if}

{else}
<p>Actuellement ta liste de contacts est vide...</p>
{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
