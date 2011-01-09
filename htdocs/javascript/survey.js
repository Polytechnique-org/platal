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

(function($) {
    $.fn.extend({
        showQuestions: function(questions) {
            var data = $('#question_base').tmpl(questions);
            this.children().remove();
            data.appendTo(this);
            return this;
        },

        addQuestion: function(id) {
            var question = $("#q_edit_new").tmpl([{ qid: id } ]);
            question
                .children('select')
                .change(function () {
                    var type = $(this).val();
                    var form = question.children('.q_edit_form');
                    form.empty();
                    $("#q_edit_base").tmpl([ { qid: id, type: type } ]).appendTo(form);
                    return true;
                });
            question.appendTo(this);
            return this;
        }
    });
})(jQuery);


$(function() {
    $(".datepicker").datepicker({
        hideIfNoPrevNext: true,
        minDate: new Date()
    });
});


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
