function doChallengeResponse() {
    str = document.login.username.value + ":" +
        MD5(document.login.password.value) + ":" +
        document.loginsub.challenge.value;

    document.loginsub.response.value = MD5(str);
    document.loginsub.username.value = document.login.username.value;
    document.login.password.value = "";
    document.loginsub.submit();
}
