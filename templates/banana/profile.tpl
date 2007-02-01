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

<br />

<table class="cadre_a_onglet" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td>
      <ul id="onglet">
        <li class="actif">Préférences</li>
        <li><a href="banana/subscription">Abonnements</a></li>
        <li><a href="banana">Les Forums</a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="conteneur_tab">

{if !$smarty.post.action}

      <p class="normal">
        Tu peux régler quelques paramètres qui apparaîtront sur les messages lorsque 
        tu posteras sur les forums. Cela ne te permettra pas d'être anonyme, puisque
        tout le monde pourra remonter à ton identité en regardant ta fiche. L'objectif
        est simplement de permettre plus de convivialité.
      </p>
      <p class="normal">
        Tu pourras voir dans les forums les nouveaux messages mis en valeur (en
        général en gras). Si tu consultes les forums régulièrement, tu peux en avoir
        assez de voir tout le contenu du forum : la dernière option te permet de
        n'afficher que les fils de discussion contenant des messages lus.
      </p>

      <form action="banana/profile" method="post">
        <table class="bicol" cellpadding="3" cellspacing="0" summary="Configuration de Banana">
          <tr>
            <th colspan="2">
              Profil Banana
            </th>
          </tr>
          <tr class="pair">
            <td class="bicoltitre">
              Nom
            </td>
            <td>
              <input type="text" name="banananame" value="{$nom}" />
            </td>
          </tr>
          <tr class="impair">
            <td class="bicoltitre">
              Adresse électronique
            </td>
            <td>
              <input type="text" name="bananamail" value="{$mail}" />
            </td>
          </tr>
          <tr class="pair">
            <td class="bicoltitre">
              Signature
            </td>
            <td>
              <textarea name="bananasig" cols="50" rows="4">{$sig}</textarea>
            </td>
          </tr>
          <tr class="impair">
            <td class="bicoltitre">
              Affichage des fils de discussion
            </td>
            <td>
              <input type="radio" name="bananadisplay" value="0" 
              {if !$disp}checked="checked"{/if} /> Afficher tous 
              les messages
              <br />
              <input type="radio" name="bananadisplay" value="1" 
              {if $disp}checked="checked"{/if} /> Afficher 
              seulement les fils de discussion contenant des messages non lus 
            </td>
          </tr>
          <tr class="pair">
            <td class="bicoltitre">
              Mise à jour des messages non lus
            </td>
            <td>
              <input type="radio" name="bananaupdate" value="1" 
              {if $maj}checked="checked"{/if} /> Automatique
              <br />
              <input type="radio" name="bananaupdate" value="0" 
              {if !$maj}checked="checked"{/if} /> Manuelle
            </td>
          </tr>
          <tr class="impair">
            <td class="bouton" colspan="2">
              <input type="submit" name="action" value="OK" />
            </td>
          </tr>
        </table>
      </form>

{else}
      <p class="normal">
        Ton profil est enregistré !!!
      </p>
{/if}

    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
