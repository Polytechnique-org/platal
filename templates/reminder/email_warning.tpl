{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

Des problèmes sont actuellement recontrés sur tes redirections suivantes&nbsp;:
<ul>
  {foreach from=$smarty.session.mx_failures item=mail}
  <li>
    <span class="erreur">{$mail.mail}</span>&nbsp;:
    <span class="explication">{$mail.text}</span>
  </li>
  {/foreach}
</ul>
<div style="text-align: center"><a href="emails/redirect">Gérer mes adresses de redirection</a></div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
