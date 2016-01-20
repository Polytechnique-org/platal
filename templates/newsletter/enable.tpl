{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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
  Mise en place de la lettre d'informations du groupe {$asso->nom}
</h1>

<p>
Tu peux demander sur cette page la mise en place d'une newsletter pour le groupe.

Tu pourras ensuite envoyer aux membres du groupe qui auront choisi de s'y inscrire,
   avec une structure plus adaptée que l'outil d'envoi de mails.
</p>

<p>
Si le titre par défaut de la newsletter ne te convient pas, n'hésite pas à
le modifier.

<form method="post" action="{$platal->ns}admin/nl/enable">
{xsrf_token_field}
<p>
  <label for='title'>Titre de la newsletter</label>
  <input name='title' id='title' size="40" value="Lettre d'informations du groupe {$asso->nom}" />
</p>
<p>
  <input type="submit" value="Créer" />
</p>
</form>

