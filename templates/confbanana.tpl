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
        $Id: confbanana.tpl,v 1.2 2004-10-24 14:41:11 x2000habouzit Exp $
 ***************************************************************************}
 
<h1>
  Configuration de Banana
</h1>

{dynamic}
{if !$smarty.post}

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
<p class="normal">
  Retour aux <a href="banana/index.php">forums</a>
</p>

<form action="{$smarty.server.PHP_SELF}" method="post">
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
<p class="normal">
  Retour aux <a href="banana/index.php">forums</a>
</p>
{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
