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
        $Id: postfix.common.tpl,v 1.7 2004-08-31 11:25:39 x2000habouzit Exp $
 ***************************************************************************}


{dynamic}
<p class="erreur">{$erreur}</p>

<div class="rubrique">
{$title}
</div>

<a href="{"admin/"|url}">page d'admin</a> |
<a href="{"admin/postfix_blacklist.php"|url}">blacklist</a> |
<a href="{"admin/postfix_perm.php"|url}">permissions</a> | 
<a href="{"admin/postfix_retardes.php"|url}">mails retardés</a>

<p>
{$expl}
</p>

<form method="post" action="{$smarty.server.PHP_SELF}">
  <div>
    <input type="text" name="nomligne" size="64" />
    <input type="submit" name="add" value="Add" />
  </div>
</form>

{foreach item=line from=$list}
<form method="post" action="{$smarty.server.PHP_SELF}">
  <input type="text" name="nomligne" value="{$line}" size="100" />
  <input type="submit" name="del" value="Del" />
</form>
{/foreach}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
