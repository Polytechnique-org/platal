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


{if $etat_naissance == 'ok'}
<script language="javascript" type="text/javascript">
  <!--
  alert ("\nDate de naissance enregistrée.\n\nTu peux maintenant modifier ton profil.");
  // -->
</script>
{else}

<h1>Date de naissance</h1>

<form action="profile/edit" method="post">
  <p>
  Avant d'accéder à ton profil pour la première fois, tu dois donner ta date de naissance au format JJMMAAAA.
  Elle ne sera plus demandée par la suite et ne pourra être changée.
  Elle servira en cas de perte du mot de passe comme sécurité supplémentaire, et uniquement à cela.
  Elle n'est jamais visible ou lisible.
  </p>
  <br />
  <table class="tinybicol" cellpadding="4" cellspacing="0" summary="Formulaire de naissance">
    <tr>
      <th colspan="2">
      Date de naissance
      </th>
    </tr>
    <tr>
      <td>
        <strong>Date</strong> (JJMMAAAA)
      </td>
      <td>
        <input type="text" size="8" maxlength="8" name="birth" />
      </td>
    </tr>
    <tr>
      <td class="center" colspan="2">
        <input type="submit" value="Enregistrer" name="submit" />
      </td>
    </tr>
  </table>
</form>


{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
