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

<h2><a href="fusionax">Fusion des annuaires X.org - AX<a> / <a href="fusionax/ids">Identifiants<a> /  Présents dans Xorg avec un matricule_ax ne correspondant à rien
dans la base de l'AX (mises à part les promo 1921 et 1923 qui ne figurent pas dans les données de l'AX)</h2>

<p></p>

{if $wrongInXorg}
{include file='fusionax/listFusion.tpl' fusionList=$wrongInXorg field1='user_id' namefield1='ID X.org'}

<p><a href="fusionax/ids/cleanwronginxorg">Mettre à NULL le matricule_ax de ces camarades pour marquer le fait qu'ils ne figurent pas dans l'annuaire de l'AX</a></p>
{/if}
