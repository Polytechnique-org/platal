<script type="text/javascript" src="javascript/flyspray.js"></script>

<div id="flyspray_report" style="display:none;position:absolute;width:700px;height:470px;left:20%;border:solid 1px;background:white;color:black;text-align:left;padding:10px">
<form action="http://trackers.polytechnique.org/" method="post" enctype="multipart/form-data">
	<h1>Signaler un bug ou demander une amélioration</h1>
	<div style="margin-left:10%;margin-right:10%">
		<input type="hidden" name="do" value="modify"/>
		<input type="hidden" name="action" value="newtask"/>
		<input type="hidden" name="project_id" value="1"/>
		<select name="task_type">
	    <option value="1">Bug</option>
			<option value="2">Wish</option>
		</select>
		&nbsp;&nbsp;Sujet : <input type="text" name="item_summary" id="flyspray_title" value="sur la page {$smarty.server.REQUEST_URI}" size="50" maxlength="100"/>
		<input type="hidden" name="productcategory" value="1"/><!-- non trié -->
		<input type="hidden" name="itemstatus" value="2"/><!-- New -->
		<input type="hidden" name="assigned_to" value="0"/><!-- No one -->
		<input type="hidden" name="operating_system" value="1"/><!-- All -->
		<input type="hidden" name="task_severity" value="2"/><!-- Low -->
		<input type="hidden" name="task_priority" value="2"/><!-- Normal -->
		<input type="hidden" name="productversion" value=""/><!-- ??? -->
		<input type="hidden" name="closedbyversion" value=""/><!-- Undecided -->
		<input type="hidden" name="due_date" value=""/>
		<textarea name="detailed_desc" id="flyspray_detail" cols="70" rows="10" style="width:100%;margin-bottom:10px;height:300px;display:block;">

** Explique ici le problème ou l'amélioration proposée **
		
Page : {$globals->baseurl}/?{$smarty.server.QUERY_STRING}
Navigateur : {$smarty.server.HTTP_USER_AGENT}
Skin : {$smarty.session.skin} 
Signalé par {$smarty.session.forlife}</textarea>
		<div style="text-align:center">
			<input type="button" value="Fermer" onclick="close_bug(this.form,false)"/>
			<input type="button" value="Signaler le bug" onclick="close_bug(this.form,true)"/>
		</div>
	</div>
</form>
</div>
