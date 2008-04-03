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

{include file="register/breadcrumb.tpl"}

<h1>Conditions générales</h1>

<p>
L'enregistrement se déroule <strong>en deux étapes</strong>&nbsp;:
</p>
<ul>
  <li>
  tu te pré-inscris, ce qui te prendra moins de 5 minutes ;
  </li>
  <li>
  nous t'envoyons immédiatement un email qui te permettra
  de te connecter au site.
  </li>
</ul>

{include wiki=Reference.Charte public=1}

<form action="register" method="post">
  <div class="center">
    <input type="submit" value="J'accepte ces conditions" name="step1" />
  </div>
</form>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
