var popup=null;
function popupWin(theURL,theSize) {
    if (theURL.indexOf('?')==-1)
        a = '?';
    else
        a = '&';
    theURL += <?php echo (isset($_COOKIE[session_name()]) ? "\"\"" : "a +\"".SID."\"");?>;
    window.open(theURL,'_blank',theSize);
    window.name="main";
    if(popup != null) {
        popup.location=popupURL;
        if(navigator.appName.substring(0,8)=="Netscape") {
            popup.location=popupURL;
            popup.opener=self;
        }
        if(navigator.appName=="Netscape" ) {
            popup.window.focus();
        }
        self.name="main";
    }
}
function popWin(theURL) {
    popupWin(theURL,'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=700,height=600');
}
function popWin2(theURL) {
    popupWin(theURL,'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=200,height=100');
}
function x() { return; }
