{* $Id: password_prompt_logged.head.tpl,v 1.6 2004-08-24 22:57:20 x2000habouzit Exp $ *}

    <script src="{"javascript/md5.js"|url}" type="text/javascript"></script>
    <script type="text/javascript">//<![PCDATA[
      <!--
      function doChallengeResponse() {ldelim}
        str = "{$smarty.cookies.ORGlogin}:" +
        MD5(document.login.password.value) + ":" +
        document.loginsub.challenge.value;

        document.loginsub.response.value = MD5(str);
        document.login.password.value = "";
        document.loginsub.submit();
      {rdelim}
      //]]>
    </script>

{* vim:set et sw=2 sts=2 sws=2: *}
