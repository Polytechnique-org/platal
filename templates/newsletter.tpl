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
        $Id: newsletter.tpl,v 1.8 2004-09-04 21:58:22 x2000habouzit Exp $
 ***************************************************************************}


{dynamic}

<p class="erreur">{$erreur}</p>

{if $nl_titre}

<p class="center">
[<a href="{$smarty.server.REQUEST_URI}&amp;send_mail=1">me l'envoyer par mail</a>]
</p>
<table class="bicol" summary="Archives de la NL">
  <tr>
    <th>
      {$nl_titre} - {$nl_date|date_format:"%d/%m/%Y"}
    </th>
  </tr>
  <tr>
    <td style="padding: 1em;">
      <tt style="white-space:pre;">{$nl_text|smarty:nodefaults|replace:"<u>":"<span style='text-decoration:underline;'>"|replace:"</u>":"</span>"|nl2br}</tt>
    </td>
  </tr>
</table>
<p class="center">
[<a href="{$smarty.server.PHP_SELF}">retour à la liste</a>]
</p>

{else}

<div class="rubrique">
  Lettre de Polytechnique.org
</div>
<p>
Tu trouveras ici les archives de la lettre d'information de Polytechnique.org.  Pour t'abonner à
cette lettre, il te suffit de te <a href="listes/">rendre sur la page des listes</a> et de cocher la
case "newsletter". Enfin, <strong>pour demander l'ajout d'une annonce dans la prochaine lettre
  mensuelle</strong>, <a href="mailto:info_newsletter@polytechnique.org">écris-nous !</a>
</p>

{include file=include/newsletter.list.tpl nl_list=$nl_list}

{/if}

{/dynamic}
{* vim:set et sw=2 sts=2 sws=2: *}
