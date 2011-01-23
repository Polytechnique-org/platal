/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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
 ***************************************************************************/

var is_IE       = $.browser.msie;

// {{{ function getNow()
var days   = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
var months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
              'août', 'septembre', 'octobre', 'novembre', 'décembre']

function getNow() {
    var dt = new Date();
    var dy = dt.getDay();
    var mh = dt.getMonth();
    var wd = dt.getDate();
    var yr = dt.getYear();
    if (yr<1000) yr += 1900;
    var hr = dt.getHours();
    var mi = dt.getMinutes();
    if (mi < 10) {
        mi = '0' + mi;
    }
    var se = dt.getSeconds();
    if (se < 10) {
        se = '0' + se;
    }

    $(".date-heure").html(days[dy] + ' ' + wd + ' ' + months[mh] + ' ' + yr + '<br />'
                        + hr + ':' + mi + ':' + se);
}

// }}}
// {{{ Search Engine

function canAddSearchEngine()
{
    if (((typeof window.sidebar == "object") && $.isFunction(window.sidebar.addSearchEngine))
        || ((typeof window.external == "object") && $.isFunction(window.external.AddSearchProvider))) {
        return true;
    }
    return false;
}

function addSearchEngine()
{
    var searchURI = "http://www.polytechnique.org/xorg.opensearch.xml";
    if ((typeof window.sidebar == "object") && $.isFunction(window.sidebar.addSearchEngine)) {
        window.sidebar.addSearchEngine(
            searchURI,
            "http://www.polytechnique.org/images/xorg.png",
            "Annuaire Polytechnique.org",
            "Academic");
    } else {
        try {
            window.external.AddSearchProvider(searchURI);
        } catch(e) {
            alert("Impossible d'installer la barre de recherche");
        }
    }
}

// }}}

/***************************************************************************
 * POPUP THINGS
 */

// {{{ function goodiesPopup()

var __goodies_active = true;

var __goodies_ical = {
    default_title: 'Calendrier iCal',
    sites: [
        {'url_prefix': '',
         'img': 'images/icons/calendar_view_day.gif',
         'title': 'Calendrier iCal'},
        {'url_prefix': 'http://www.google.com/calendar/render?cid=',
         'img': 'images/goodies/add-google-calendar.gif',
         'title': 'Ajouter à Google Calendar'},
        {'url_prefix': 'https://www.google.com/calendar/hosted/polytechnique.org/render?cid=',
         'img': 'images/goodies/add-google-calendar.gif',
         'title': 'Ajouter à Google Apps / Calendar'}
    ]
};

var __goodies_rss = {
    default_title: 'Fils RSS',
    sites: [
        {'url_prefix': '',
         'img': 'images/icons/feed.gif',
         'title': 'Fil rss'},
        {'url_prefix': 'http://fusion.google.com/add?feedurl=',
         'img': 'images/goodies/add-google.gif',
         'alt': 'Add to Google',
         'title': 'Ajouter à iGoogle/Google Reader'},
        {'url_prefix': 'http://www.netvibes.com/subscribe.php?url=',
         'img': 'images/goodies/add-netvibes.gif',
         'title': 'Ajouter à Netvibes'},
        {'url_prefix': 'http://add.my.yahoo.com/content?.intl=fr&url=',
         'img': 'images/goodies/add-yahoo.gif',
         'alt': 'Add to My Yahoo!',
         'title': 'Ajouter à My Yahoo!'}
    ]
};

function disableGoodiesPopups() {
    __goodies_active = false;
}

function goodiesPopup(node, goodies) {
    var text = '<div style="text-align: center; line-height: 2.2">';
    for (var site in goodies.sites) {
        var entry = goodies.sites[site];
        var s_alt   = entry["alt"] ? entry["alt"] : "";
        var s_img   = entry["img"];
        var s_title = entry["title"] ? entry["title"] : "";
        var s_url   = entry["url_prefix"].length > 0 ? entry["url_prefix"] + escape(this.href) : this.href;

        text += '<a href="' + s_url + '"><img src="' + s_img + '" title="' + s_title + '" alt="' + s_alt + '"></a><br />';
    }
    text += '<a href="https://www.polytechnique.org/Xorg/Goodies">Plus de bonus</a> ...</div>';

    var title = node.title ? node.title : goodies.default_title;

    $(node)
        .mouseover(
            function() {
                if (__goodies_active) {
                    return overlib(text, CAPTION, title, CLOSETEXT, 'Fermer', DELAY, 800, STICKY, WIDTH, 150);
                }
            }
        )
        .mouseout(nd);
}

// }}}
// {{{ function auto_links()

function auto_links() {
    var url  = document.URL;
    var fqdn = url.replace(/^https?:\/\/([^\/]*)\/.*$/,'$1');
    var light = url.indexOf('display=light') > url.indexOf('?');
    var resource_page = url.contains('rss') || url.contains('ical');

    $("a,link").each(function(i) {
        var node = $(this);
        var href = this.href;
        var matches;
        var rss;
        var ical;

        if(!href || node.hasClass('xdx')
           || href.startsWith('mailto:') || href.startsWith('javascript:')) {
            return;
        }
        if ((!href.contains(fqdn) && !this.className.contains('popup')) || node.hasClass('popup')) {
            node.click(function () {
                window.open(href);
                return false;
            });
        }
        if (href.contains(fqdn) && light) {
            href = href.replace(/([^\#\?]*)\??([^\#]*)(\#.*)?/, "$1?display=light&$2$3");
            this.href = href;
        }
        rss  = href.contains('rss');
        ical = href.contains('ical');
        if (rss || ical) {
            if (!href.startsWith('http')) {
                href = 'http://' + fqdn + '/' + href;
            }
        }
        if (this.nodeName.toLowerCase() == 'a' && !resource_page) {
            if (rss && !href.contains('prefs/rss') && (href.contains('xml') || href.contains('hash'))) {
                goodiesPopup(this, __goodies_rss);
            } else if (ical) {
                goodiesPopup(this, __goodies_ical);
            }
        }
        if(matches = (/^popup_([0-9]*)x([0-9]*)$/).exec(this.className)) {
            var w = matches[1], h = matches[2];
            node.popWin(w, h);
        }
    });
    $('.popup2').popWin(840, 600);
    $('.popup3').popWin(640, 800);
}


// }}}

/***************************************************************************
 * Password check
 */

// {{{ function checkPassword

/* {{{ SHA1 Implementation */

/*
 * A JavaScript implementation of the Secure Hash Algorithm, SHA-1, as defined
 * in FIPS PUB 180-1
 * Version 2.1a Copyright Paul Johnston 2000 - 2002.
 * Other contributors: Greg Holt, Andrew Kepert, Ydnar, Lostinet
 * Distributed under the BSD License
 * See http://pajhome.org.uk/crypt/md5 for details.
 */

/*
 * Configurable variables. You may need to tweak these to be compatible with
 * the server-side, but the defaults work in most cases.
 */
var hexcase = 0;  /* hex output format. 0 - lowercase; 1 - uppercase        */
var b64pad  = ""; /* base-64 pad character. "=" for strict RFC compliance   */
var chrsz   = 8;  /* bits per input character. 8 - ASCII; 16 - Unicode      */

/*
 * These are the functions you'll usually want to call
 * They take string arguments and return either hex or base-64 encoded strings
 */
function hex_sha1(s){return binb2hex(core_sha1(str2binb(s),s.length * chrsz));}
function b64_sha1(s){return binb2b64(core_sha1(str2binb(s),s.length * chrsz));}
function str_sha1(s){return binb2str(core_sha1(str2binb(s),s.length * chrsz));}
function hex_hmac_sha1(key, data){ return binb2hex(core_hmac_sha1(key, data));}
function b64_hmac_sha1(key, data){ return binb2b64(core_hmac_sha1(key, data));}
function str_hmac_sha1(key, data){ return binb2str(core_hmac_sha1(key, data));}

/*
 * Perform a simple self-test to see if the VM is working
 */
function sha1_vm_test()
{
  return hex_sha1("abc") == "a9993e364706816aba3e25717850c26c9cd0d89d";
}

/*
 * Calculate the SHA-1 of an array of big-endian words, and a bit length
 */
function core_sha1(x, len)
{
  /* append padding */
  x[len >> 5] |= 0x80 << (24 - len % 32);
  x[((len + 64 >> 9) << 4) + 15] = len;

  var w = Array(80);
  var a =  1732584193;
  var b = -271733879;
  var c = -1732584194;
  var d =  271733878;
  var e = -1009589776;

  for(var i = 0; i < x.length; i += 16)
  {
    var olda = a;
    var oldb = b;
    var oldc = c;
    var oldd = d;
    var olde = e;

    for(var j = 0; j < 80; j++)
    {
      if(j < 16) w[j] = x[i + j];
      else w[j] = rol(w[j-3] ^ w[j-8] ^ w[j-14] ^ w[j-16], 1);
      var t = safe_add(safe_add(rol(a, 5), sha1_ft(j, b, c, d)),
                       safe_add(safe_add(e, w[j]), sha1_kt(j)));
      e = d;
      d = c;
      c = rol(b, 30);
      b = a;
      a = t;
    }

    a = safe_add(a, olda);
    b = safe_add(b, oldb);
    c = safe_add(c, oldc);
    d = safe_add(d, oldd);
    e = safe_add(e, olde);
  }
  return Array(a, b, c, d, e);

}

/*
 * Perform the appropriate triplet combination function for the current
 * iteration
 */
function sha1_ft(t, b, c, d)
{
  if(t < 20) return (b & c) | ((~b) & d);
  if(t < 40) return b ^ c ^ d;
  if(t < 60) return (b & c) | (b & d) | (c & d);
  return b ^ c ^ d;
}

/*
 * Determine the appropriate additive constant for the current iteration
 */
function sha1_kt(t)
{
  return (t < 20) ?  1518500249 : (t < 40) ?  1859775393 :
         (t < 60) ? -1894007588 : -899497514;
}

/*
 * Calculate the HMAC-SHA1 of a key and some data
 */
function core_hmac_sha1(key, data)
{
  var bkey = str2binb(key);
  if(bkey.length > 16) bkey = core_sha1(bkey, key.length * chrsz);

  var ipad = Array(16), opad = Array(16);
  for(var i = 0; i < 16; i++)
  {
    ipad[i] = bkey[i] ^ 0x36363636;
    opad[i] = bkey[i] ^ 0x5C5C5C5C;
  }

  var hash = core_sha1(ipad.concat(str2binb(data)), 512 + data.length * chrsz);
  return core_sha1(opad.concat(hash), 512 + 160);
}

/*
 * Add integers, wrapping at 2^32. This uses 16-bit operations internally
 * to work around bugs in some JS interpreters.
 */
function safe_add(x, y)
{
  var lsw = (x & 0xFFFF) + (y & 0xFFFF);
  var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
  return (msw << 16) | (lsw & 0xFFFF);
}

/*
 * Bitwise rotate a 32-bit number to the left.
 */
function rol(num, cnt)
{
  return (num << cnt) | (num >>> (32 - cnt));
}

/*
 * Convert an 8-bit or 16-bit string to an array of big-endian words
 * In 8-bit function, characters >255 have their hi-byte silently ignored.
 */
function str2binb(str)
{
  var bin = Array();
  var mask = (1 << chrsz) - 1;
  for(var i = 0; i < str.length * chrsz; i += chrsz)
    bin[i>>5] |= (str.charCodeAt(i / chrsz) & mask) << (32 - chrsz - i%32);
  return bin;
}

/*
 * Convert an array of big-endian words to a string
 */
function binb2str(bin)
{
  var str = "";
  var mask = (1 << chrsz) - 1;
  for(var i = 0; i < bin.length * 32; i += chrsz)
    str += String.fromCharCode((bin[i>>5] >>> (32 - chrsz - i%32)) & mask);
  return str;
}

/*
 * Convert an array of big-endian words to a hex string.
 */
function binb2hex(binarray)
{
  var hex_tab = hexcase ? "0123456789ABCDEF" : "0123456789abcdef";
  var str = "";
  for(var i = 0; i < binarray.length * 4; i++)
  {
    str += hex_tab.charAt((binarray[i>>2] >> ((3 - i%4)*8+4)) & 0xF) +
           hex_tab.charAt((binarray[i>>2] >> ((3 - i%4)*8  )) & 0xF);
  }
  return str;
}

/*
 * Convert an array of big-endian words to a base-64 string
 */
function binb2b64(binarray)
{
  var tab = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
  var str = "";
  for(var i = 0; i < binarray.length * 4; i += 3)
  {
    var triplet = (((binarray[i   >> 2] >> 8 * (3 -  i   %4)) & 0xFF) << 16)
                | (((binarray[i+1 >> 2] >> 8 * (3 - (i+1)%4)) & 0xFF) << 8 )
                |  ((binarray[i+2 >> 2] >> 8 * (3 - (i+2)%4)) & 0xFF);
    for(var j = 0; j < 4; j++)
    {
      if(i * 8 + j * 6 > binarray.length * 32) str += b64pad;
      else str += tab.charAt((triplet >> 6*(3-j)) & 0x3F);
    }
  }
  return str;
}

/* }}} */

function hash_encrypt(a) {
    return hex_sha1(a);
}

var hexa_h = "0123456789abcdef";

function dechex(a) {
    return hexa_h.charAt(a);
}

function hexdec(a) {
    return hexa_h.indexOf(a);
}

function hash_xor(a, b) {
    var c,i,j,k;
    c = "";
    i = a.length;
    j = b.length;
    if (i < j) {
        var d;
        d = a; a = b; b = d;
        k = i; i = j; j = k;
    }
    for (k = 0; k < j; k++)
        c += dechex(hexdec(a.charAt(k)) ^ hexdec(b.charAt(k)));
    for (; k < i; k++)
        c += a.charAt(k);
    return c;
}


function getType(c) {
    if (c >= 'a' && c <= 'z') {
        return 1;
    } else if (c >= 'A' && c <= 'Z') {
        return 2;
    } else if (c >= '0' && c <= '9') {
        return 3;
    } else {
        return 4;
    }
}

function differentTypes(password) {
    var prev = 0;

    for (i = 0 ; i < password.length ; ++i) {
        var type = getType(password.charAt(i));
        if (prev != 0 && prev != type) {
            return true;
        }
        prev = type;
    }
    return false;
}

function passwordStrength(password) {
    var prop = 0;
    var prev = 0;
    var firstType = true;
    var types = Array(0, 0, 0, 0, 0);

    for (i = 0 ; i < password.length ; ++i) {
        var type = getType(password.charAt(i));
        if (prev != 0 && prev != type) {
            prop += 5;
            firstType = false;
        }
        prop += i;
        if (types[type] == 0 && !firstType) {
            prop += 15;
        }
        types[type]++;
        prev = type;
    }
    if (password.length < 6) {
        prop *= 0.75;
    }
    if (firstType) {
        prop *= 0.75;
    }
    if (prop > 100) {
        prop = 100;
    } else if (prop < 0) {
        prop = 0;
    }

    return prop;
}

function checkPassword(box, okLabel) {
    var password = box.value;
    var prop = passwordStrength(password);

    if (prop >= 60) {
        color = "#4f4";
        bgcolor = "#050";
        ok = true;
    } else if (prop >= 35) {
        color = "#ff4";
        bgcolor = "#750";
        ok = true;
    } else {
        color = "#f20";
        bgcolor = "#700";
        ok = false;
    }
    $("#passwords_measure")
           .stop()
           .animate({ width: prop + "%",
                      backgroundColor: color
                    }, 750)
           .parent().stop()
                    .animate({ backgroundColor: bgcolor }, 750);
    var submitButton = $(":submit[name='" + passwordprompt_submit + "']");
    if (ok && password.length >= 6 && differentTypes(password)) {
        submitButton.attr("value", okLabel);
        submitButton.removeAttr("disabled");
    } else {
        submitButton.attr("value", "Mot de passe trop faible");
        submitButton.attr("disabled", "disabled");
    }
}

function hashResponse(password1, password2, hasConfirmation) {
    pw1 = $('[name=' + password1 + ']').val();

    if (hasConfirmation) {
        pw2 = $('[name=' + password2 + ']').val();
        if (pw1 != pw2) {
            alert("\nErreur : les deux champs ne sont pas identiques !");
            return false;
        }
        $('[name=' + password2 + ']').val('');
    } else if (pw1 == '********') {
        return true;
    }

    if (pw1.length < 6) {
        alert("\nErreur : le nouveau mot de passe doit faire au moins 6 caractères !");
        return false;
    }
    if (!differentTypes(pw1)) {
        alert ("\nErreur : le nouveau mot de passe doit comporter au moins deux types de caractères parmi les suivants : lettres minuscules, lettres majuscules, chiffres, caractères spéciaux.");
        return false;
    }

    alert("Le mot de passe que tu as rentré va être chiffré avant de nous parvenir par Internet ! Ainsi il ne circulera pas en clair.");
    $('[name=' + password1 + ']').val('');
    $('[name=pwhash]').val(hash_encrypt(pw1));
    return true;
}

function correctUserName() {
    var u = document.forms.login.username;
    // login with no space
    if (u.value.indexOf(' ') < 0) return true;
    var mots = u.value.split(' ');
    // jean paul.du pont -> jean-paul.du-pont
    if (u.value.indexOf('.') > 0) { u.value = mots.join('-'); return true; }
    // jean dupont  -> jean.dupont
    if (mots.length == 2) { u.value = mots[0]+"."+mots[1]; return true; }
    // jean dupont 2001 -> jean.dupont.2001
    if (mots.length == 3 && mots[2] > 1920 && mots[2] < 3000) { u.value = mots.join('.'); return true; }
    // jean de la vallee -> jean.de-la-vallee
    if (mots[1].toUpperCase() == 'DE') { u.value = mots[0]+"."+mots.join('-').substr(mots[0].length+1); return true; }
    // jean paul dupont -> jean-paul.dupont
    if (mots.length == 3 && mots[0].toUpperCase() == 'JEAN') { u.value = mots[0]+"-"+mots[1]+"."+mots[2]; return true; }

    alert('Ton email ne doit pas contenir de blanc.\nLe format standard est\n\nprenom.nom.promotion\n\nSi ton nom ou ton prenom est composé,\nsépare les mots par des -');

    return false;
}

function doChallengeResponse() {

    if (!correctUserName()) return false;

    var new_pass = hash_encrypt(document.forms.login.password.value);
    var old_pass = hash_encrypt(document.forms.login.password.value.substr(0, 10));

    str = document.forms.login.username.value + ":" +
        new_pass + ":" +
        document.forms.loginsub.challenge.value;

    document.forms.loginsub.response.value = hash_encrypt(str);
    if (new_pass != old_pass) {
        document.forms.loginsub.xorpass.value = hash_xor(new_pass, old_pass);
    }
    document.forms.loginsub.username.value = document.forms.login.username.value;
    document.forms.loginsub.remember.value = document.forms.login.remember.checked;
    document.forms.loginsub.domain.value = document.forms.login.domain.value;
    document.forms.login.password.value = "";
    document.forms.loginsub.submit();
}

function doChallengeResponseLogged() {
    var new_pass = hash_encrypt(document.forms.login.password.value);

    str = document.forms.loginsub.username.value + ":" +
        hash_encrypt(document.forms.login.password.value) + ":" +
        document.forms.loginsub.challenge.value;

    document.forms.loginsub.response.value = hash_encrypt(str);
    document.forms.loginsub.remember.value = document.forms.login.remember.checked;
    document.forms.login.password.value = "";
    document.forms.loginsub.submit();
}

// }}}
// {{{ send test email

function sendTestEmail(token, hruid)
{
    var url = 'emails/test';
    var msg = "Un email a été envoyé avec succès";
    if (hruid != null) {
        url += '/' + hruid;
        msg += " sur l'adresse de " + hruid + ".";
    } else {
        msg += " sur ton addresse.";
    }
    $('#mail_sent').successMessage($url + '?token=' + token, msg);
    return false;
}

// }}}


/***************************************************************************
 * The real OnLoad
 */

$(function() {
    auto_links();
    getNow();
    setInterval(getNow, 1000);
    $("#quick")
        .focus(function() {
            if ($(this).val() === 'Recherche dans l\'annuaire') {
                $(this).val('');
            }
            $("#quick_button").show();
        })
        .blur(function() {
            $("#quick_button").hide();
        });
    $("#quick_button").click(function() {
        if ($("#quick").val() === 'Recherche dans l\'annuaire'
            || $("#quick").val() === '') {
            return false;
        }
        return true;
    });
});

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
