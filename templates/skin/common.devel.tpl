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

{if #globals.debug#}
{if $db_trace && $db_trace neq "\n\n"}
  <div id="db-trace">
    <h1>
      {if $db_error}<span style="color: #f00">{/if}
      Trace de l'exécution de cette page sur mysql (hover me)
      {if $db_error}</span>{/if}
    </h1>
    <div class="hide">
      {$db_trace|smarty:nodefaults}
    </div>
  </div>
{/if}

{if $validate}
  <div id="dev">
    @HOOK@
    Validation:
    <a href="http://jigsaw.w3.org/css-validator/validator?uri={#globals.baseurl#}/valid.html">CSS</a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    références :
    <a href="http://www.w3schools.com/xhtml/xhtml_reference.asp">XHTML</a>
    <a href="http://www.w3schools.com/css/css_reference.asp">CSS2</a>
  </div>
{/if}
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
