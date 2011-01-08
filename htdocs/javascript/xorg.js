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
// {{{ dynpost()

function dynpost(action, values)
{
    var form = document.createElement('form');
    form.action = action;
    form.method = 'post';

    $('body').get(0).appendChild(form);

    for (var k in values) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = k;
        input.value = values[k];
        form.appendChild(input);
    }

    form.submit();
}


function dynpostkv(action, k, v)
{
    var dict = {};
    dict[k] = v;
    dynpost(action, dict);
}

// }}}
// {{{ function RegExp.escape()

RegExp.escape = function(text) {
  if (!arguments.callee.sRE) {
    var specials = [
      '/', '.', '*', '+', '?', '|',
      '(', ')', '[', ']', '{', '}',
      '\\', '^' , '$'
    ];
    arguments.callee.sRE = new RegExp(
      '(\\' + specials.join('|\\') + ')', 'g'
    );
  }
  return text.replace(arguments.callee.sRE, '\\$1');
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
    var light = (url.indexOf('display=light') > url.indexOf('?'));
    var resource_page = (url.indexOf('rss') > -1 || url.indexOf('ical') > -1);

    $("a,link").each(function(i) {
        var node = $(this);
        var href = this.href;
        if(!href || node.hasClass('xdx')
           || href.indexOf('mailto:') > -1 || href.indexOf('javascript:') > -1) {
            return;
        }
        if ((href.indexOf(fqdn) < 0 && this.className.indexOf('popup') < 0) || node.hasClass('popup')) {
            node.click(function () {
                window.open(href);
                return false;
            });
        }
        if (href.indexOf(fqdn) > -1 && light) {
            href = href.replace(/([^\#\?]*)\??([^\#]*)(\#.*)?/, "$1?display=light&$2$3");
            this.href = href;
        }
        var rss  = href.indexOf('rss') > -1;
        var ical = href.indexOf('ical') > -1;
        if (rss || ical) {
            if (href.indexOf('http') < 0) {
                href = 'http://' + fqdn + '/' + href;
            }
        }
        if (this.nodeName.toLowerCase() == 'a' && !resource_page) {
            if (rss && href.indexOf('prefs/rss') < 0 &&  (href.indexOf('xml') > -1 || href.indexOf('hash'))) {
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
        },

        closeOnEsc: function() {
            return $(window).keydown(function (e) {
                if (e.keyCode == 27) {
                    window.close();
                }
            });
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
                window.open(this.href, '_blank',
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
