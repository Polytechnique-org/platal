{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

{include file=skin/common.doctype.tpl}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"
    integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ=="
    crossorigin="anonymous">
    {include file=skin/common.header.tpl}
  </head>
  <body>
    {include core=plpage.devel.tpl}
    {if !$simple}
      {include file=skin/common.bandeau.tpl}
    {/if}
    {if t($smarty.session.suid)}
    <div id="suid">
      <a href="exit">
        Quitter le SU sur {$smarty.session.hruid} ({$smarty.session.perms->flags()})
      </a>
    </div>
    {/if}

  {if $simple}

    <div id="content">
      {include core=plpage.content.tpl}
    </div>

  {else}

  <div id="body" class="container">
    <header>
      {include file="g15/common/header.tpl"}
    </header>
    <section>
      <nav id="body-menu">
        {include file=skin/common.menu.tpl}
      </nav>
      <article id="content">
        {include core=plpage.content.tpl}
      </article>
    </section>
    <footer id="body-bottom">
      {include file=skin/common.footer.tpl}
    </footer>
  </div>

  {/if}

  </body>
</html>
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
