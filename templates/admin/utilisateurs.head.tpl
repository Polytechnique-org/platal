{* $Id: utilisateurs.head.tpl,v 1.1 2004-02-11 20:00:38 x2000habouzit Exp $ *}

{literal}
<script language="javascript" type="text/javascript" src="md5.js"></script>
<style type="text/css" media="screen,print">
  <!-- 
  p.succes {font-weight: bold;}
  table.admin {width: 100%; color: #000000; background-color: #eeeeee;}
  table.admin th.login,th.password,th.perms {border-top: 1px solid black;}
  table.admin th.login,td.login {background-color: #f9e89b;}
  table.admin td.loginr {background-color: #f9e89b; font-weight: bold; text-align: right;}
  table.admin th.action,td.action {background-color: blue; color: yellow;}
  table.admin th.password,th.perms,td.password,td.perms {background-color: #ffc0c0;}
  table.admin th.detail {text-align: center;}
  table.admin th.alias,td.alias { background-color: #F9E89B;}
  table.admin th.polyedu,td.polyedu { border-top: 1px solid black; border-bottom: 1px solid black;}
  table.admin th.alias {text-align: center;}
  -->
</style>

<script language="javascript" type="text/javascript">
<!--
function doAddUser() {
    document.add.hashpass.value = MD5(document.add.password.value);
    document.add.password.value = "";
    document.add.submit();
}
function doEditUser() {
    document.edit.hashpass.value = MD5(document.edit.password.value);
    document.edit.password.value = "";
    document.edit.submit();
}
// -->
</script>
{/literal}

{* vim:set et sw=2 sts=2 sws=2: *}
