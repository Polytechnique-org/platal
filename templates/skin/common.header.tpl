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
        $Id: common.header.tpl,v 1.16 2004-11-13 15:56:47 x2000habouzit Exp $
 ***************************************************************************}


    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="description" content="Le Portail des Polytechniciens" />
    <meta name="keywords" content="Ecole polytechnique, anciens eleves, portail, alumni, AX, X, routage, reroutage, e-mail, email, mail" />
    <link rel="icon" type="image/png" href="images/favicon.png" />
    
    <link rel="index" href="{"index.php"|url}" />
    <link rel="author" href="{"docs/faq.php"|url}" />
    <link rel="search" href="{"search.php"|url}" />
    <link rel="help" href="{"docs/faq.php"|url}" />
    <link rel="bookmark" href="http://www.polytechnique.fr/"        title="| École polytechnique" />
    <link rel="bookmark" href="http://www.polytechnique.edu/"       title="| Institutionnal site" />
    <link rel="bookmark" href="http://www.fondationx.org/"          title="| FX" />
    <link rel="bookmark" href="http://www.polytechniciens.com/"     title="| AX" />
    <link rel="bookmark" href="http://www.polytechnique.org/"       title="| Polytechnique.org" />
    <link rel="bookmark" href="http://www.polytechnique.fr/eleves/" title="| Site d'élèves" />

    <title>
      {if $xorg_title}
      {dynamic}
      {$xorg_title}
      {/dynamic}
      {else}
      Anciens eleves Polytechnique, annuaire email, Alumni
      {/if}
    </title>
    <script type="text/javascript" src="{"javascript/misc.js"|url}"></script>
    <script type="text/javascript" src="{"javascript/popups.js"|url}"></script>

{* vim:set et sw=2 sts=2 sws=2: *}
