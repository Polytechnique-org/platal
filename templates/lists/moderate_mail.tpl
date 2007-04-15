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

{include file="lists/header_listes.tpl"}

<h1>
  Contenu du mail en attente
</h1>

{$banana|smarty:nodefaults}

<form method='post' action='{$platal->pl_self(1)}'>
  <table class='tinybicol' cellpadding='0' cellspacing='0'>
    <tr>
      <th class='titre'>Modérer le mail</th>
    </tr>
    <tr>
      <td>raison (pour les refus) :
        <textarea cols='50' rows='10' name='reason' id='raison'>
-- 
{$smarty.session.prenom} {$smarty.session.nom} (X{$smarty.session.promo})
</textarea>
      </td>
    </tr>
    <tr>
      <td class='center'>
        <input type='hidden' name='mid' value='{$smarty.get.mid}' />
        <input type='submit' name='mok' value='Accepter !'
          onclick="return confirm('Es-tu sûr de vouloir Envoyer ce mail sur la liste ?')"/>&nbsp;
        <input type='submit' name='mno' value='Refuser !' 
          onclick="return confirm('Es-tu sûr de vouloir Refuser ce mail ?')"/>&nbsp;
        <input type='submit' name='mdel' value='Spam !' style='color:red;'
          onclick="return confirm('Es-tu sûr de vouloir Détruire ce mail ?')"/>
      </td>
    </tr>
  </table>
  <ul>
    <li>« Refuser » rejette le mail avec un message à son auteur (celui que tu tapes dans le cadre)</li>
    <li>
    « Spam » détruit efface le mail sans autre forme de procès, et c'est à utiliser <strong>UNIQUEMENT</strong>
    pour les virus et les courriers indésirables
    </li>
  </ul>
</form>
<p>
En cas de refus, le mail envoyé à l'auteur du mail que tu modères actuellement sera de la forme suivante :
</p>
<pre>{$msg|utf8_encode}</pre>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
