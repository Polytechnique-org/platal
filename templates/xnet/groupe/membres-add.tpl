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

<h1>{$asso.nom} : Ajout d'un membre</h1>

<form method="post" action="{rel}/{$platal->ns}member/new/">
  <p class="descr">
  <ul class='descr'>
    <li>
      Pour ajouter un X dans ton groupe, il suffit d'entrer ici une de ses
      adresses mail @polytechnique.org.
    </li>
    <li>
      Pour ajouter un extérieur dans ton groupe, il suffit d'entrer ici son
      adresse mail, tu seras ensuite redirigé vers une page te permettant
      d'éditer son profil (nom, prenom, ...)
    </li>
  </ul>
  </p>
  <div class="center">
    <input type="text" name="email" size="40" value="{$platal->argv[1]}" />
    <input type='submit' value='Ajouter'
      onclick='this.form.action += this.form.email.value' />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
