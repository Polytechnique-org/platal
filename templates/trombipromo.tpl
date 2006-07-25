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

<h1>
  Trombinoscope promo
</h1>
<p>
Cette page te permet de voir l'album photo d'une promotion
donnée.
</p>
<p>
Fais attention, si tu as une connexion à internet lente (par
exemple si tu es sur un vieux modem), la page que tu vas télécharger
en validant peut être longue à afficher.
</p>

<form action="trombi/" method="post">
  <table class="tinybicol" cellpadding="3" summary="Saisie promo" style="width: 30%; margin-left:35%">
    <tr>
      <th colspan="2">
        Trombinoscope
      </th>
    </tr>
    <tr>
      <td class="titre" style="vertical-align: middle;">
        Promotion
      </td>
      <td>
        <input type="text" name="xpromo" size="4" maxlength="4" />
        <input type="submit" value="Ok"
          onclick='this.form.action = this.form.action + this.form.xpromo.value' />
      </td>
    </tr>
  </table>
</form>

{if $trombi}

<h1>
  Album photo
  {if $platal->argv[1] eq -1}
  {#globals.core.sitename#}
  {else}
  promotion {$platal->argv[1]}
  {/if}
</h1>

{$trombi->show()|smarty:nodefaults}

{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
