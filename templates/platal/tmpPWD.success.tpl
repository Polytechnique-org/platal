{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

<h1>Récupération de mot de passe</h1>

<p>
<strong>Mot de passe enregistré le {$smarty.now|date_format}</strong>
</p>
<p>
  Cette procédure n'est pas sécurisée. Ton mot de passe est certes protégé par
  une opération cryptographique irréversible, mais le certificat envoyé par
  mail permet à toute personne pouvant lire ton email (qui n'est pas chiffré),
  de changer ton mot de passe. C'est pourquoi, dans ton intérêt, il est
  préférable que tu ne perdes pas ton mot de passe&nbsp;!!!
</p>
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
