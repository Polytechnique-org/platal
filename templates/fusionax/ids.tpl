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

<h2><a href="fusionax">Fusion des annuaires X.org - AX<a> / Identifiants</h2>

<p>Le préalable à toute fusion de renseignements pour une personne entre ce
que contient la base AX et ce que contient ce site est bien évidemment de
trouver une correspondance entre les personnes renseignés dans ces annuaires.</p>

{if $nbMissingInAX}
<h3>Anciens manquants à l'AX</h3>

<p><a href="fusionax/ids/missingInAX">{$nbMissingInAX} ancien{if $nbMissingInAX > 1}s{/if}</a>.</p>
{/if}

{if $nbMissingInXorg > 0}
<h3>Anciens manquants à x.org</h3>

<p><a href="fusionax/ids/missingInXorg">{$nbMissingInXorg} ancien{if $nbMissingInXorg > 1}s{/if}</a>.</p>
{/if}

<h3>Mettre en correspondance</h3>
<form action="fusionax/ids/lier" method="get">
	Matricule AX : <input name="matricule_ax" value""/><br/>
	User ID X.org : <input name="user_id" value=""/><br/>
	<input type="submit" value="Lier"/>
</form>

<p></p>
<h3 id="autolink" name="autolink">Mise en correspondance automatique</h3>
{if $easyToLink}
<p>Ces anciens sont probablement les mêmes (mêmes nom, prénom, promo)</p>
{include file="fusionax/listFusion.tpl" fusionList=$easyToLink fusionAction="fusionax/ids/link" name="lier"}
<p><a href="fusionax/ids/linknext">Lier toutes les fiches affichées</a></p>
{else}
<p>Aucune correspondance automatique n'a été trouvée (mêmes nom, prénom, promo d'étude).</p>
{/if}
