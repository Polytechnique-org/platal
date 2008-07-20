{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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
  Contenu de l'email en attente
</h1>

{$banana|smarty:nodefaults}

<form method='post' action='{$platal->pl_self(1)}'>
  <table class='tinybicol' cellpadding='0' cellspacing='0'>
    <tr>
      <th class='titre'>Modérer l'email</th>
    </tr>
    <tr>
      <td>raison (pour les refus)&nbsp;:
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
          onclick="return confirm('Es-tu sûr de vouloir Envoyer cet email sur la liste ?')"/>&nbsp;
        <input type='submit' name='mno' value='Refuser !' 
          onclick="return confirm('Es-tu sûr de vouloir Refuser cet email ?')"/>&nbsp;
        <input type='submit' name='mdel' value='Spam !' style='color:red;'
          onclick="return confirm('Es-tu sûr de vouloir Détruire cet email ?')"/>
      </td>
    </tr>
  </table>
  <ul>
    <li>« Refuser » rejette l'email avec un message à son auteur (celui que tu tapes dans le cadre).</li>
    <li>
    « Spam » détruit l'email sans autre forme de procès, à utiliser <strong>UNIQUEMENT</strong>
    pour les virus et les courriers indésirables.
    </li>
  </ul>
</form>
<p>
En cas de refus, l'email envoyé à l'auteur de l'email que tu modères actuellement sera de la forme suivante&nbsp;:
</p>
<pre>{$msg|smarty:nodefaults|utf8_encode}</pre>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
