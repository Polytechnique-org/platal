<?php
require("diogenes.core.globals.inc.php");

class XorgGlobals extends DiogenesCoreGlobals {
  var $page = 'XorgPage';
  var $session = 'XorgSession';

  var $dbdb = 'x4dat';
  var $table_auth = 'auth_user_md5';
  var $table_log_actions = 'logger.actions';
  var $table_log_sessions = 'logger.sessions';
  var $table_log_events = 'logger.events';

}
?>
