{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

{include file="register/breadcrumb.tpl"}

<h1>Pré-inscription réussie</h1>

<p>
La pré-inscription que tu viens de soumettre a été enregistrée.
</p>
<p>
Les instructions te permettant de valider ton inscription viennent de t'être envoyées
à l'adresse <strong>{$smarty.session.subState.email}</strong>.
</p>
<p>
Tu n'as que quelques jours pour suivre ces instructions. Ensuite, la pré-inscription
est effacée automatiquement de notre base de données et il faut tout recommencer.
</p>
<p>
Si tu ne reçois rien, vérifie bien l'adresse <strong>{$smarty.session.subState.email}</strong>.
En particulier, vérifie si l'email n'a pas été rejeté par ton filtre anti-spam. L'adresse
d'expéditeur est <code>register@polytechnique.org</code>.
</p>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
