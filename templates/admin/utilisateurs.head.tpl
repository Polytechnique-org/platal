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
        $Id: utilisateurs.head.tpl,v 1.3 2004-08-31 11:25:39 x2000habouzit Exp $
 ***************************************************************************}


{literal}
<script type="text/javascript" src="md5.js"></script>
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

<script type="text/javascript">
<!--
function doAddUser() {
    document.forms.add.hashpass.value = MD5(document.forms.add.password.value);
    document.forms.add.password.value = "";
    document.forms.add.submit();
}
function doEditUser() {
    document.forms.edit.hashpass.value = MD5(document.forms.edit.password.value);
    document.forms.edit.password.value = "";
    document.forms.edit.submit();
}
// -->
</script>
{/literal}

{* vim:set et sw=2 sts=2 sws=2: *}
