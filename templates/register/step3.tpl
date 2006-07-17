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


{if $smarty.session.sub_state.forlife}

<h1>Formulaire de pré-inscription</h1>

<form action="register" method="post">
  {if $smarty.session.sub_state.mailorg2}
  <p>
  Tu n'as pour le moment aucun homonyme dans notre base de données, nous allons
  donc te donner l'adresse <strong>{$smarty.session.sub_state.bestalias}@polytechnique.org</strong>,
  en plus de ton adresse à vie <strong>{$smarty.session.sub_state.forlife}@polytechnique.org</strong>.
  Sache que tu peux perdre l'adresse <strong>{$smarty.session.sub_state.bestalias}@polytechnique.org</strong> 
  si un homonyme s'inscrit (même si cela reste assez rare).
  </p>
  {else}
  <p>
  Tu as déjà un homonyme inscrit dans notre base de données mais dans une autre promotion, nous allons
  donc te donner l'adresse <strong>{$smarty.session.sub_state.bestalias}@polytechnique.org</strong>, en plus
  de ton adresse à vie <strong>{$smarty.session.sub_state.forlife}@polytechnique.org</strong>.
  </p>
  {/if}
  
  <p>
  Ces adresses sont des redirections vers des adresses e-mail de ton choix.
  Indique-s-en un pour commencer (tu pourras indiquer les autres une fois l'inscription terminée) et pouvoir
  terminer ton inscription.
  </p>
  <p>
  Attention, il doit <strong>impérativement être correct</strong> pour que nous puissions 
  t'envoyer ton mot de passe.
  </p>

  <table class="bicol">
    <tr>
      <th colspan="2">
        Contact et sécurité
      </th>
    </tr>
    <tr>
      <td class="titre">
        E-mail<br />
        <span class="smaller">(Première redirection)</span>
      </td>
      <td>
        <input type="text" size="35" maxlength="50" name="email" value="{$smarty.post.email}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Date de naissance<br />
        <span class="smaller">(Format JJMMAAAA)</span>
      </td>
      <td>
        <input type="text" size="8" maxlength="8" name="naissance"  value="{$smarty.post.naissance}" />
        (demandée si perte de mot de passe)
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Terminer la pré-inscription" />
      </td>
    </tr>
  </table>
</form>

{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
