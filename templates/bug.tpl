<div style="width:800px;height:600px">
{if $bug_sent}
<script type="text/javascript">window.close();</script>
{/if}
<form action="send_bug" method="post">
	<h1>Signaler un bug ou demander une amélioration</h1>
	<div style="margin-left:10%;margin-right:10%">
          <select name="task_type">
	    <option value="bug">Bug</option>
            <option value="wish">Wish</option>
          </select>
          &nbsp;&nbsp;Sujet : <input type="text" name="item_summary" id="flyspray_title" value="sur la page {$smarty.server.HTTP_REFERER}" size="50" maxlength="100"/>
          <textarea name="detailed_desc" id="flyspray_detail" cols="70" rows="10" style="width:100%;margin-top:10px;margin-bottom:10px;height:400px;display:block;">

** Explique ici le problème ou l'amélioration proposée **
		
Page : {$smarty.server.HTTP_REFERER}

Navigateur : {$smarty.server.HTTP_USER_AGENT}
Skin : {$smarty.session.skin} 
Signalé par {$smarty.session.forlife}</textarea>
          <div style="text-align:center">
              <input type="button" value="Fermer" onclick="window.close()"/>
              <input type="submit" name="send" value="Signaler le bug"/>
          </div>
    </div>
</form>
</div>
