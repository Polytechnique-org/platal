{* $Id: password_prompt_logged.head.tpl,v 1.8 2004-08-29 14:58:54 x2000habouzit Exp $ *}

    <script src="{"javascript/md5.js"|url}" type="text/javascript"></script>
    <script type="text/javascript">//<![CDATA[
      function doChallengeResponse() {ldelim}
        str = "{$smarty.cookies.ORGlogin}:" +
        MD5(document.forms.login.password.value) + ":" +
        document.forms.loginsub.challenge.value;

        document.forms.loginsub.response.value = MD5(str);
        document.forms.login.password.value = "";
        document.forms.loginsub.submit();
      {rdelim}
      //]]>
    </script>

{* vim:set et sw=2 sts=2 sws=2: *}
