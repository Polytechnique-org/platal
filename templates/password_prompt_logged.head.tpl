{* $Id: password_prompt_logged.head.tpl,v 1.4 2004-01-31 10:20:16 x2000habouzit Exp $ *}

    <script language="javascript" src="{"javascript/md5.js"|url}" type="text/javascript"></script>
    <script language="javascript"type="text/javascript">
      <!--
      function doChallengeResponse() {ldelim}
        str = "{$smarty.cookies.ORGlogin}:" +
        MD5(document.login.password.value) + ":" +
        document.loginsub.challenge.value;

        document.loginsub.response.value = MD5(str);
        document.login.password.value = "";
        document.loginsub.submit();
      {rdelim}
      // -->
    </script>

{* vim:set et sw=2 sts=2 sws=2: *}
