{* $Id: password_prompt_logged.head.tpl,v 1.3 2004-01-26 19:40:51 x2000habouzit Exp $ *}

{literal}
    <style type="text/css" media="screen">
      <!--
      .login {font-weight: bold;}
      div.warning {margin-top: 0.4em; color: red; font-weight: bold;}
      div.explication {font-size: smaller; font-weight: bold;}
      #pwd.bicol { width: 60%; margin-left: 20%; }
      -->
    </style>
{/literal}
    <script language="javascript" src="{"javascript/md5.js"|url}" type="text/javascript"></script>
{literal}
    <script language="javascript"type="text/javascript">
      <!--
      function doChallengeResponse() {
        str = "{/literal}{$smarty.cookies.ORGlogin}{literal}:" +
        MD5(document.login.password.value) + ":" +
        document.loginsub.challenge.value;

        document.loginsub.response.value = MD5(str);
        document.login.password.value = "";
        document.loginsub.submit();
      }
      // -->
    </script>
{/literal}


{* vim:set et sw=2 sts=2 sws=2: *}
