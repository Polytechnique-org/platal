function doChallengeResponse() {
    str = document.forms.login.username.value + ":" +
        MD5(document.forms.login.password.value) + ":" +
        document.forms.loginsub.challenge.value;

    document.forms.loginsub.response.value = MD5(str);
    document.forms.loginsub.username.value = document.forms.login.username.value;
    document.forms.login.password.value = "";
    document.forms.loginsub.submit();
}
