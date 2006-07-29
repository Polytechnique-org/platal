{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<div id="flyspray_report">
<form action="http://trackers.polytechnique.org/" method="post" enctype="multipart/form-data">
	<h1>Signaler un bug ou demander une amélioration</h1>
	<div>
		<input type="hidden" name="do" value="modify"/>
		<input type="hidden" name="action" value="newtask"/>
		<input type="hidden" name="project_id" value="1"/>
		<select name="task_type">
	    <option value="1">Bug</option>
			<option value="2">Wish</option>
		</select>
		<input type="text" name="item_summary" id="flyspray_title" value="sur la page {$smarty.server.REQUEST_URI}" size="50" maxlength="100"/>
		<input type="hidden" name="productcategory" value="1"/><!-- non trié -->
		<input type="hidden" name="itemstatus" value="2"/><!-- New -->
		<input type="hidden" name="assigned_to" value="0"/><!-- No one -->
		<input type="hidden" name="operating_system" value="1"/><!-- All -->
		<input type="hidden" name="task_severity" value="2"/><!-- Low -->
		<input type="hidden" name="task_priority" value="2"/><!-- Normal -->
		<input type="hidden" name="productversion" value=""/><!-- ??? -->
		<input type="hidden" name="closedbyversion" value=""/><!-- Undecided -->
		<input type="hidden" name="due_date" value=""/>
		<textarea name="detailed_desc" id="flyspray_detail" cols="70" rows="10">

** Explique ici le problème ou l'amélioration proposée **
		
Page : {#globals.baseurl#}/?{$smarty.server.QUERY_STRING}
Navigateur : {$smarty.server.HTTP_USER_AGENT}
Skin : {$smarty.session.skin} 
Signalé par {$smarty.session.forlife}</textarea>
		<div id="flyspray_submit">
			<input type="button" value="Fermer" onclick="close_bug(this.form,false)"/>
			<input type="button" value="Signaler le bug" onclick="close_bug(this.form,true)"/>
		</div>
	</div>
</form>
</div>

<div>
  Plat/al <a href="changelog">{#globals.version#}</a> - Copyright © 1999-2006 <a href="http://x-org.polytechnique.org/">Polytechnique.org</a>
  &nbsp;-&nbsp;
  <a href="Docs/ConventionAX">Lien avec l'AX</a>
  &nbsp;-&nbsp;
  <a href="Docs/APropos">A propos de ce site</a>
<br />
  <a href="Docs/Ethique">Services et Ethique</a>
  | <a href="Docs/Charte">Charte</a>
{if $smarty.session.auth ge AUTH_COOKIE}
  | <a href="stats/coupures">Disponibilité</a>
  | <a href="stats">Statistiques</a>
{/if}
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
