{* $Id: motdepassemd5.head.tpl,v 1.3 2004-08-25 09:52:08 x2000habouzit Exp $ *}

<script type="text/javascript" src="{"javascript/md5.js"|url}"></script>
{literal}
<script type="text/javascript">
//<![CDATA[
  function EnCryptedResponse() {
    pw1 = document.forms.changepass.nouveau.value;
    pw2 = document.forms.changepass.nouveau2.value;
    if (pw1 != pw2) {
      alert ("\nErreur : les deux champs ne sont pas identiques !")
      return false;
      exit;
    }
    if (pw1.length < 6) {
      alert ("\nErreur : le nouveau mot de passe doit faire au moins 6 caractères !")
      return false;
      exit;
    }
    str = MD5(document.forms.changepass.nouveau.value);
    document.forms.changepass2.response2.value = str;
    alert ("Le mot de passe que tu as rentré va être chiffré avant de nous parvenir par Internet ! Ainsi il ne circulera pas en clair.");
    document.forms.changepass2.submit();
    return true;
  }
//]]>
</script>
{/literal}

{* vim:set et sw=2 sts=2 sws=2: *}
