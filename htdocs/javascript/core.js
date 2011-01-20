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

// {{{ Assertion

// }}}
// {{{ jQuery object extension

(function($) {
    function assert(condition, text) {
        if ($.isFunction(condition)) {
            condition = condition();
        }
        if (condition) {
            return this;
        }
        if (!text) {
            throw "Assertion failed";
        } else {
            throw "Assertion failed: " + text;
        }
    }


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
        },

        dynPost: function(action, key, value) {
            var values;
            if (!$.isArray(key)) {
                values = { };
                values[key] = value;
            } else {
                values = key;
            }
            var form = $('<form>', {
                action: action,
                method: 'post'
            });
            for (var k in values) {
                $('<input>', {
                    type: 'hidden',
                    name: k,
                    value: values[k]
                }).appendTo(form);
            }
            $('body').appendTo(form);
            form.submit();
        },

        assert: assert
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
        },

        assert: assert,

        assertLength: function(len) {
            return this.assert(function() {
                return $(this).length == len;
            });
        },

        assertId: function(id) {
            return this.assert(function() {
                return $(this).attr('id') == id;
            });
        },

        assertClass: function(clazz) {
            return this.assert(function() {
                return $(this).hasClass(clazz);
            });
        }
    });
})(jQuery);

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
// {{{ PmWiki decoding

Nix = {
    map: null,
    convert: function(a) {
        Nix.init();
        var s = '';
        for (i = 0; i < a.length ; i++) {
            var b = a.charAt(i);
            s += ((b >= 'A' && b <= 'Z') || (b >= 'a' && b <= 'z') ? Nix.map[b] : b);
        }
        return s;
    },
    init: function() {
        if (Nix.map != null)
            return;
        var map = new Array();
        var s='abcdefghijklmnopqrstuvwxyz';
        for (i = 0; i < s.length; i++)
            map[s.charAt(i)] = s.charAt((i+13)%26);
        for (i=0; i<s.length; i++)map[s.charAt(i).toUpperCase()] = s.charAt((i+13)%26).toUpperCase();
            Nix.map = map;
    },
    decode: function(a) {
        document.write(Nix.convert(a));
    }
}

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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
