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

<fieldset>
  <legend style="border: 1px #993333 solid; background: #ff9c9c">Annuaire migré </legend>
  <p>L'annuaire des X a été migré à une nouvelle adresse: <a href="https://ax.polytechnique.org/">https://ax.polytechnique.org/</a>.</p>
{if $smarty.session.auth}
  <p>Si tu y as déjà activé ton compte, tu peux y modifier ton profil en cliquant sur <a href="https://ax.polytechnique.org/my-profile/preferences/contact-info">ce lien.</a></p>

  <p>Pour y activer ton compte, rends-toi sur <a href="https://ax.polytechnique.org/">https://ax.polytechnique.org/</a> et clique sur le bouton "Je me connecte". Les données de connexion sont les mêmes que sur polytechnique.org.</p>
{/if}
</fieldset>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
