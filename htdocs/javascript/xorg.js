/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

// {{{ function getNow()
var days   = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
var months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
              'août', 'septembre', 'octobre', 'novembre', 'décembre'];

function getNow() {
    var dt = new Date();
    var dy = dt.getDay();
    var mh = dt.getMonth();
    var wd = dt.getDate();
    var yr = dt.getYear();
    var hr = dt.getHours();
    var mi = dt.getMinutes();
    var se = dt.getSeconds();

    if (yr<1000) {
        yr += 1900;
    }
    if (mi < 10) {
        mi = '0' + mi;
    }
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
    if (((typeof window.sidebar === "object") && $.isFunction(window.sidebar.addSearchEngine))
        || ((typeof window.external === "object") && $.isFunction(window.external.AddSearchProvider))) {
        return true;
    }
    return false;
}

function addSearchEngine()
{
    var searchURI = "http://www.polytechnique.org/xorg.opensearch.xml";
    if ((typeof window.sidebar === "object") && $.isFunction(window.sidebar.addSearchEngine)) {
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

(function($) {
    var goodies = {
        ical: {
            default_title: 'Calendrier iCal',
            sites: [
                {url_prefix: '',
                 img: 'images/icons/calendar_view_day.gif',
                 title: 'Calendrier iCal'},
                {url_prefix: 'http://www.google.com/calendar/render?cid=',
                 img: 'images/goodies/add-google-calendar.gif',
                 title: 'Ajouter à Google Calendar'},
                {url_prefix: 'https://www.google.com/calendar/hosted/polytechnique.org/render?cid=',
                 img: 'images/goodies/add-google-calendar.gif',
                 title: 'Ajouter à Google Apps / Calendar'}
            ]
        },

        rss: {
            default_title: 'Fils RSS',
            sites: [
                {url_prefix: '',
                 img: 'images/icons/feed.gif',
                 title: 'Fil rss'},
                {url_prefix: 'http://fusion.google.com/add?feedurl=',
                 img: 'images/goodies/add-google.gif',
                 alt: 'Add to Google',
                 title: 'Ajouter à iGoogle/Google Reader'},
                {url_prefix: 'http://www.netvibes.com/subscribe.php?url=',
                 img: 'images/goodies/add-netvibes.gif',
                 title: 'Ajouter à Netvibes'},
                {url_prefix: 'http://add.my.yahoo.com/content?.intl=fr&url=',
                 img: 'images/goodies/add-yahoo.gif',
                 alt: 'Add to My Yahoo!',
                 title: 'Ajouter à My Yahoo!'}
            ]
        }
    };

    $.fn.extend({
        goodiesPopup: function goodiesPopup(type) {
            var text = '<div style="text-align: center; line-height: 2.2">';
            var site;
            var entry;
            var s_alt;
            var s_img;
            var s_title;
            var s_url;
            var href = this.attr('href');

            for (site in goodies[type].sites) {
                entry = goodies[type].sites[site];
                s_alt   = entry.alt || "";
                s_img   = entry.img;
                s_title = entry.title || "";
                s_url   = entry.url_prefix.length > 0 ? entry.url_prefix + escape(href) : href;

                text += '<a href="' + s_url + '"><img src="' + s_img + '" title="' + s_title + '" alt="' + s_alt + '"></a><br />';
            }
            text += '<a href="https://www.polytechnique.org/Xorg/Goodies">Plus de bonus</a> ...</div>';

            return this.overlib({
                text: text,
                caption: this.attr('title') || goodies[type].default_title,
                close_text: 'Fermer',
                delay: 800,
                sticky: true,
                width: 150
            });
        }
    });
}(jQuery));

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
                window.open($.plURL(this.href));
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
        if (this.nodeName.toLowerCase() === 'a' && !resource_page) {
            if (rss && !href.contains('prefs/rss') && (href.contains('xml') || href.contains('hash'))) {
                node.goodiesPopup('rss');
            } else if (ical) {
                node.goodiesPopup('ical');
            }
        }
        matches = /^popup_([0-9]*)x([0-9]*)$/.exec(this.className);
        if (matches) {
            node.popWin(matches[1], matches[2]);
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
  return hex_sha1("abc") === "a9993e364706816aba3e25717850c26c9cd0d89d";
}

/*
 * Calculate the SHA-1 of an array of big-endian words, and a bit length
 */
function core_sha1(x, len)
{
  var w, a, b, c, d, e;
  var olda, oldb, oldc, oldd, olde;
  var i, j, t;

  /* append padding */
  x[len >> 5] |= 0x80 << (24 - len % 32);
  x[((len + 64 >> 9) << 4) + 15] = len;

  w = Array(80);
  a =  1732584193;
  b = -271733879;
  c = -1732584194;
  d =  271733878;
  e = -1009589776;

  for(i = 0; i < x.length; i += 16)
  {
    olda = a;
    oldb = b;
    oldc = c;
    oldd = d;
    olde = e;

    for(j = 0; j < 80; j++)
    {
      if(j < 16) w[j] = x[i + j];
      else w[j] = rol(w[j-3] ^ w[j-8] ^ w[j-14] ^ w[j-16], 1);
      t = safe_add(safe_add(rol(a, 5), sha1_ft(j, b, c, d)),
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
  var i, ipad, opad;
  var hash;

  if(bkey.length > 16) bkey = core_sha1(bkey, key.length * chrsz);

  ipad = Array(16);
  opad = Array(16);
  for(i = 0; i < 16; i++)
  {
    ipad[i] = bkey[i] ^ 0x36363636;
    opad[i] = bkey[i] ^ 0x5C5C5C5C;
  }

  hash = core_sha1(ipad.concat(str2binb(data)), 512 + data.length * chrsz);
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
  var i;
  for(i = 0; i < str.length * chrsz; i += chrsz)
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
  var i;
  for(i = 0; i < bin.length * 32; i += chrsz)
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
  var i;
  for(i = 0; i < binarray.length * 4; i++)
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
  var i, j, triplet;
  for(i = 0; i < binarray.length * 4; i += 3)
  {
    triplet = (((binarray[i   >> 2] >> 8 * (3 -  i   %4)) & 0xFF) << 16)
                | (((binarray[i+1 >> 2] >> 8 * (3 - (i+1)%4)) & 0xFF) << 8 )
                |  ((binarray[i+2 >> 2] >> 8 * (3 - (i+2)%4)) & 0xFF);
    for(j = 0; j < 4; j++)
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
    var c,i,j,k,d;
    c = "";
    i = a.length;
    j = b.length;
    if (i < j) {
        d = a; a = b; b = d;
        k = i; i = j; j = k;
    }
    for (k = 0; k < j; k++) {
        c += dechex(hexdec(a.charAt(k)) ^ hexdec(b.charAt(k)));
    }
    for (; k < i; k++) {
        c += a.charAt(k);
    }
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
    var type;

    for (i = 0 ; i < password.length ; ++i) {
        type = getType(password.charAt(i));
        if (prev !== 0 && prev !== type) {
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
    var type;

    for (i = 0 ; i < password.length ; ++i) {
        type = getType(password.charAt(i));
        if (prev !== 0 && prev !== type) {
            prop += 5;
            firstType = false;
        }
        prop += i;
        if (types[type] === 0 && !firstType) {
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
    var submitButton;

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
    submitButton = $(":submit[name='" + passwordprompt_submit + "']");
    if (ok && password.length >= 6 && differentTypes(password)) {
        submitButton.attr("value", okLabel);
        submitButton.removeAttr("disabled");
    } else {
        submitButton.attr("value", "Mot de passe trop faible");
        submitButton.attr("disabled", "disabled");
    }
}

function hashResponse(password1, password2, hasConfirmation, doAuth) {
    var pw1 = $('[name=' + password1 + ']').val();
    var pw2;

    if (hasConfirmation) {
        pw2 = $('[name=' + password2 + ']').val();
        if (pw1 !== pw2) {
            alert("\nErreur : les deux champs ne sont pas identiques !");
            return false;
        }
        $('[name=' + password2 + ']').val('');
    } else if (pw1 === '********') {
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

    alert("Le mot de passe va être chiffré avant de nous parvenir par Internet ! Ainsi il ne circulera pas en clair.");
    $('[name=' + password1 + ']').val('');
    $('[name=pwhash]').val(hash_encrypt(pw1));

    if (doAuth) {
        $('[name=password]').val(pw1);
        doChallengeResponse();
    }

    return true;
}

function correctUserName() {
    var u = document.forms.login.username;
    var mots;

    // login with no space
    if (!u.value.contains(' ')) {
        return true;
    }
    mots = u.value.split(' ');
    // jean paul.du pont -> jean-paul.du-pont
    if (u.value.contains('.')) {
        u.value = mots.join('-');
        return true;
    }
    // jean dupont  -> jean.dupont
    if (mots.length === 2) {
        u.value = mots[0] + "." + mots[1];
        return true;
    }
    // jean dupont 2001 -> jean.dupont.2001
    if (mots.length === 3 && mots[2] > 1920 && mots[2] < 3000) {
        u.value = mots.join('.');
        return true;
    }
    // jean de la vallee -> jean.de-la-vallee
    if (mots[1].toUpperCase() === 'DE') {
        u.value = mots[0] + "." + mots.join('-').substr(mots[0].length+1);
        return true;
    }
    // jean paul dupont -> jean-paul.dupont
    if (mots.length === 3 && mots[0].toUpperCase() === 'JEAN') {
        u.value = mots[0] + "-" + mots[1] + "." + mots[2];
        return true;
    }

    alert('Ton email ne doit pas contenir de blanc.\nLe format standard est\n\nprenom.nom.promotion\n\nSi ton nom ou ton prenom est composé,\nsépare les mots par des -');

    return false;
}

function doChallengeResponse() {
    var new_pass, old_pass, str;

    if (!correctUserName()) {
        return false;
    }

    new_pass = hash_encrypt(document.forms.login.password.value);
    old_pass = hash_encrypt(document.forms.login.password.value.substr(0, 10));

    str = document.forms.login.username.value + ":" +
        new_pass + ":" +
        document.forms.loginsub.challenge.value;

    document.forms.loginsub.response.value = hash_encrypt(str);
    if (new_pass !== old_pass) {
        document.forms.loginsub.xorpass.value = hash_xor(new_pass, old_pass);
    }
    document.forms.loginsub.username.value = document.forms.login.username.value;
    document.forms.loginsub.remember.value = document.forms.login.remember.checked;
    document.forms.login.password.value = "";
    document.forms.loginsub.submit();
}

function doChallengeResponseLogged() {
    var str = document.forms.loginsub.username.value + ":" +
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
    if (hruid) {
        url += '/' + hruid;
        msg += " sur l'adresse de " + hruid + ".";
    } else {
        msg += " sur ton addresse.";
    }
    $('#mail_sent').successMessage(url + '?token=' + token, msg);
    return false;
}

// }}}
// {{{ jQuery object extension

(function($) {
    /* Add new functions to jQuery namesapce */
    $.extend({
        /* The goal of the following functions is to provide an AJAX API that
         * take a different callback in case of HTTP success code (2XX) and in
         * other cases.
         */

        xajax: function(source, method, data, onSuccess, onError, type) {
            /* Shift argument */
            if ($.isFunction(data)) {
                type = type || onError;
                onError = onSuccess;
                onSuccess = data;
                data = null;
            }
            if (onError != null && !$.isFunction(onError)) {
                type = type || onError;
                onError = null;
            }

            function ajaxHandler(data, textStatus, xhr) {
                if (textStatus == 'success') {
                    if (onSuccess) {
                        onSuccess(data, textStatus, xhr);
                    }
                } else if (textStatus == 'error') {
                    if (onError) {
                        onError(data, textStatus, xhr);
                    } else {
                        alert("Une error s'est produite lors du traitement de la requête.\n"
                            + "Ta session a peut-être expiré");
                    }
                }
            }
            return $.ajax({
                url: source,
                type: method,
                success: ajaxHandler,
                data : data,
                dataType: type
            });
        },

        xget: function(source, data, onSuccess, onError, type) {
            return $.xajax(source, 'GET', data, onSuccess, onError, type);
        },

        xgetJSON: function(source, data, onSuccess, onError) {
            return $.xget(source, data, onSuccess, onError, 'json');
        },

        xgetScript: function(source, onSuccess, onError) {
            return $.xget(source, null, onSuccess, onError, 'script');
        },

        xgetText: function(source, data, onSuccess, onError) {
            return $.xget(source, data, onSuccess, onError, 'text');
        },

        xpost: function(source, data, onSuccess, onError, type) {
            return $.xajax(source, 'POST', data, onSuccess, onError, type);
        }
    });

    /* Add new functions to jQuery objects */
    $.fn.extend({
        tmpMessage: function(message, success) {
            if (success) {
                this.html("<img src='images/icons/wand.gif' alt='' /> " + message)
                    .css('color', 'green');
            } else {
                this.html("<img src='images/icons/error.gif' alt='' /> " + message)
                    .css('color', 'red');
            }
            return this.css('fontWeight', 'bold')
                       .show()
                       .delay(1000)
                       .fadeOut(500);
        },

        updateHtml: function(source, callback) {
            var elements = this;
            function handler(data) {
                elements.html(data);
                if (callback) {
                    callback(data);
                }
            }
            $.xget(source, handler, 'text');
            return this;
        },

        successMessage: function(source, message) {
            var elements = this;
            $.xget(source, function() {
                elements.tmpMessage(message, true);
            });
            return this;
        },

        wiki: function(text, withTitle) {
            if (text == '') {
                return this.html('');
            }
            var url = 'wiki_preview';
            if (!withTitle) {
                url += '/notitile';
            }
            var $this = this;
            $.post(url, { text: text },
                   function (data) {
                       $this.html(data);
                   }, 'text');
            return this;
        },

        popWin: function(w, h) {
            return this.click(function() {
                window.open($.plURL(this.href), '_blank',
                            'toolbar=0,location=0,directories=0,status=0,'
                           +'menubar=0,scrollbars=1,resizable=1,'
                           +'width='+w+',height='+h);
                return false;
            });
        }
    });
})(jQuery);

// }}}
// {{{ preview wiki

function previewWiki(idFrom, idTo, withTitle, idShow)
{
    $('#' + idTo).wiki($('#' + idFrom).val(), withTitle);
    if (idShow != null) {
        $('#' + idShow).show();
    }
}

// }}}
// {{{ updatepromofields

function updatepromofields(egal1, egal2, promo2) {
    var comparator = egal1.val();

    if (comparator == '=') {
        egal2.attr('disabled', 'disabled');
        promo2.attr('disabled', 'disabled');
    } else if (comparator == '<=' || comparator == '>=') {
        egal2.removeAttr('disabled');
        promo2.removeAttr('disabled');
        if (comparator == '<=') {
            egal2.val('>=');
        } else {
            egal2.val('<=');
        }
    }
}

// }}}

/***************************************************************************
 * Quick search
 */

/* quick search {{{ */
(function($) {
    function findPos(obj) {
        var curleft = obj.offsetLeft || 0;
        var curtop = obj.offsetTop || 0;
        while (obj = obj.offsetParent) {
            curleft += obj.offsetLeft
            curtop += obj.offsetTop
        }
        return {x:curleft,y:curtop};
    }

    $.template('quickMinifiche',
            '<div class="contact grayed" style="clear: both">' +
                '<div class="identity">' +
                    '<div class="photo"><img src="photo/${hrpid}" alt="${directory_name}" /></div>' +
                    '<div class="nom">' +
                        '{{if is_female}}&bull;{{/if}}<a>${directory_name}</a>' +
                    '</div>' +
                    '<div class="edu">${promo}</div>' +
                '</div>' +
                '<div class="noprint bits"></div>' +
                '<div class="long"></div>' +
            '</div>');


    function buildPopup(input, destination, linkBindFunction)
    {
        var $popup = destination;
        var selected = null;
        var hovered  = 0;

        function updateSelection()
        {
            var sel = $popup.children('.contact').addClass('grayed');
            if (selected !== null) {
                while (selected < 0) {
                    selected += sel.length;
                }
                if (selected >= sel.length) {
                    selected -= sel.length;
                }
                sel.eq(selected).removeClass('grayed');
            }
        }

        function formatProfile(i, profile) {
            var data = $.tmpl('quickMinifiche', profile)
                .css('cursor', 'pointer')
                .hover(function() {
                    selected = i;
                    updateSelection();
                    hovered++;
                }, function() {
                    if (selected === i) {
                        selected = null;
                        updateSelection();
                    }
                    hovered--;
                }).mouseup(function() {
                    var sel = $(this).find('a');
                    if (!sel.attr('hovered')) {
                        sel.click();
                    }
                });
            data.find('a').hover(function() { $(this).attr('hovered', true) },
                                 function() { $(this).attr('hovered', false) });
            return data;
        }

        if (!$popup) {
            $popup = $('<div>').hide()
            .addClass('contact-list')
            .css({
                position: 'absolute',
                width: '300px',
                top: input.css('bottom'),
                clear: 'both',
                'text-align': 'left'
            });
            input.after($popup);
        }

        return {
            hide: function(ignoreIfHover) {
                if (ignoreIfHover && hovered !== 0) {
                    return true;
                }
                selected = null;
                updateSelection();
                $popup.hide();
                return true;
            },

            show: function() {
                var pos = findPos(input.get(0));
                $popup.css('left', pos.x - 300 + input.width()).show();
                return true;
            },

            selected: function() {
                return selected !== null;
            },

            unselect: function() {
                selected = null;
                updateSelection();
            },

            selectNext: function() {
                if (selected === null) {
                    selected = 0;
                } else {
                    selected++;
                }
                updateSelection();
                return true;
            },

            selectPrev: function() {
                if (selected === null) {
                    selected = -1;
                } else {
                    selected--;
                }
                updateSelection();
                return true;
            },

            activeCurrent: function() {
                var sel = $popup.children('.contact');
                if (selected !== null) {
                    sel.eq(selected).find('a').click();
                    return false;
                }
                return true;
            },

            updateContent: function(profiles, extra) {
                var profile;
                var $this;
                $popup.empty();
                for (var i = 0, len = profiles.length; i < len; i++) {
                    (function(elt) {
                        var profile = formatProfile(i, elt);
                        profile.find('a').each(function() {
                            linkBindFunction.call(this, elt, $this, extra);
                        });
                        profile.appendTo($popup);
                    }(profiles[i]));
                }
                if (len === 1) {
                    selected = 0;
                } else {
                    selected = null;
                }
                updateSelection();
                if (len > 0) {
                    this.show();
                } else {
                    this.hide();
                }
                return true;
            }
        };
    }

    $.fn.extend({
        quickSearch: function(options) {
            return this.each(function() {
            var $this  = $(this);
            var $input = this;
            var $popup;
            var previous = null;
            var pending  = false;
            var disabled = false;
            var updatePopup;

            options = options || { };
            options = $.extend({
                destination:       null,
                minChars:          3,
                shortChars:        5,
                shortTimeout:      300,
                longTimeout:       100,
                queryParams:       {
                    offset: 0,
                    count:  10,
                    allow_special: true
                },
                loadingClassLeft:  'ac_loading',
                loadingClassRight: 'ac_loading_left',
                selectAction: function(profile, popup, extra) {
                    var type = extra.link_type || 'profile';
                    switch (type) {
                      case 'profile':
                        $(this).attr('href', 'profile/' + profile.hrpid)
                        .popWin(840, 600)
                        .click(function() { $popup.hide(); return false; });
                        break;
                      case 'admin':
                        $(this).attr('href', 'admin/user/' + profile.hrpid)
                        .click(function() { window.open($.plURL(this.href)); return false });
                        break;
                    }
                }
            }, options);
            options.loadingClass = $this.css('text-align') === 'right' ? options.loadingClassRight
                                                                       : options.loadingClassLeft;
            $this.attr('autocomplete', 'off');
            $popup = buildPopup($this, options.destination, options.selectAction);

            function markPending() {
                pending = true;
            }

            function performUpdate(quick)
            {
                if (updatePopup === markPending) {
                    return true;
                }
                updatePopup = markPending;
                $this.addClass(options.loadingClass);
                $.xapi('search', $.extend({ 'quick': quick }, options.queryParams), function(data) {
                    if (data.profile_count > options.queryParams.count || data.profile_count < 0) {
                        return $popup.hide();
                    }
                    $popup.updateContent(data.profiles, data);
                    previous = quick;
                }, function(data, text) {
                    if (text !== 'abort') {
                        disabled = true;
                    }
                }).complete(function() {
                    $this.removeClass(options.loadingClass);
                    updatePopup = doUpdatePopup;
                    if (pending) {
                        updatePopup();
                    }
                });
                return true;
            }

            function doUpdatePopup(dontDelay)
            {
                var quick = $this.val();
                if ($.isFunction(quick.trim)) {
                    quick = quick.trim();
                }
                pending = false;
                if (disabled || quick.length < options.minChars) {
                    previous = quick;
                    return $popup.hide();
                } else if (!dontDelay) {
                    var timeout = quick.length < options.shortChars ? options.shortTimeout : options.longTimeout;
                    setTimeout(function() {
                        updatePopup(true);
                    }, timeout);
                    return true;
                } else if (previous === quick) {
                    return $popup.show();
                }
                return performUpdate(quick);
            }

            updatePopup = doUpdatePopup;

            return $this.keyup(function(e) {
                if (e.keyCode !== 27 /* escape */ && e.keyCode !== 13 /* enter */
                    && e.keyCode !== 9 /* tab */ && e.keyCode !== 38 /* up */
                    && e.keyCode !== 40 /* down */) {
                    return updatePopup();
                }
                return true;
            })
            .keydown(function(e) {
                switch (e.keyCode) {
                  case 9: /* Tab */
                  case 40: /* Down */
                    $popup.selectNext();
                    return false;

                  case 38:
                    $popup.selectPrev();
                    return false;

                  case 13: /* Return */
                    return $popup.activeCurrent();

                  case 27: /* Escape */
                    if ($popup.selected()) {
                        $popup.unselect();
                    } else {
                        $popup.hide();
                    }
                    return true;
                }
                return true;
            })
            .blur(function() {
                return $popup.hide(true);
            })
            .focus(updatePopup);});
        }
    });
}(jQuery));

/***************************************************************************
 * Overlib made simple
 */

(function($) {
    $.fn.extend({
        overlib: function(text, width, height) {
            var args = [ ];
            var key;

            if (typeof text === 'string') {
                args.push(text);
                if (width) {
                    args.push(WIDTH, width);
                }
                if (height) {
                    args.push(HEIGHT, height);
                }
            } else {
                for (key in text) {
                    switch (key) {
                      case 'text':
                        args.unshift(text[key]);
                        break;
                      case 'caption':
                        args.push(CAPTION, text[key]);
                        break;
                      case 'close_text':
                        args.push(CLOSETEXT, text[key]);
                        break;
                      case 'delay':
                        args.push(DELAY, text[key]);
                        break;
                      case 'sticky':
                        if (text[key]) {
                            args.push(STICKY);
                        }
                        break;
                      case 'width':
                        args.push(WIDTH, text[key]);
                        break;
                      case 'height':
                        args.push(HEIGHT, text[key]);
                        break;
                    }
                }
            }
            return this
                .mouseover(function () {
                    return overlib.apply(null, args);
                })
                .mouseout(nd);
        }
    });
}(jQuery));
/* }}} */

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
        })
        .quickSearch();
    $("#quick_button").click(function() {
        if ($("#quick").val() === 'Recherche dans l\'annuaire'
            || $("#quick").val() === '') {
            return false;
        }
        return true;
    });
});

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
