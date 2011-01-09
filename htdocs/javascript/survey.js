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
    $.extend({
        questions: function() {
            return $('.q_edit:not(#questions)');
        },

        lastQuestion: function() {
            return $.questions().last();
        },

        renumberQuestions: function() {
            var q = $.questions();
            q.each(function(idx) {
                var elt = $(this);
                var old_id = elt.attr('id');
                var new_id = 'q_edit[' + idx + ']';
                if (old_id == new_id) {
                    return;
                }

                var children = elt.children(':not(.q_edit)');
                while (children.length > 0) {
                    children.filter('.q_edit_label').text('Question ' + (idx + 1));
                    children.children('[name*="' + old_id + '"]').each(function() {
                        function replace(attr) {
                            var cid = $(this).attr(attr);
                            if (cid.substr(0, id.length) == old_id) {
                               $(this).attr(attr, new_id + cid.substring(old_id.length, cid.length));
                            }
                        }
                        replace('id');
                        replace('name');
                    });
                    children = children.children(':not(.q_edit)');
                }
                elt.attr('id', new_id);
            });
        },

        debugPrintQuestions: function() {
            var q = $.questions();
            var str = '';
            q.each(function() {
                str += $(this).attr('id') + '\n';
            });
            alert(str);
        }
    });

    $.fn.extend({
        showQuestions: function(questions) {
            var data = $('#question_base').tmpl(questions);
            this.empty();
            data.appendTo(this);
            return this;
        },

        /* Edition form */
        prepareQuestions: function(questions) {
            var data = $('#q_edit_new').tmpl(questions);
            data.prependTo(this);
            return this;
        },

        isQuestion: function() {
            return this.hasClass('q_edit');
        },

        isRootSection: function() {
            return this.attr('id') == 'questions';
        },

        question: function() {
            return this.isQuestion() ? this : this.parentQuestion();
        },

        qid: function() {
            var question = this.question();
            if (question.get(0) == undefined) {
                return undefined;
            }
            var id = question.attr('id');
            if (id.substr(0, 7) != 'q_edit[') {
                return undefined;
            }
            if (id.charAt(id.length - 1) != ']') {
                return undefined;
            }
            id = id.substr(7, id.length - 8);
            return parseInt(id);
        },

        parentQuestion: function() {
            return this.parent().closest('.q_edit');
        },

        childrenContainer: function() {
            var question = this.question();
            return question.isRootSection() ? question : question.children('.q_edit_form').children();
        },

        childrenQuestion: function() {
            return this.childrenContainer().children('.q_edit');
        },

        addQuestion: function() {
            var id = $.lastQuestion().qid();
            if (id == undefined) {
                id = 0;
            } else {
                id++;
            }
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
            var dest = this.question();
            var res = this.childrenContainer().children('.add_question').before(question);
            $.renumberQuestions();
            return res;
        },

        removeQuestion: function(force) {
            var question = this.parentQuestion();
            if (!force && question.children('.q_edit_form').children().children('.q_edit').length > 0) {
                if (!alert('Vous avez demander la suppression d\'une section contenant des questions. '
                         + 'Ces questions seront supprimées. Etes-vous sur de vouloir continuer ?')) {
                    return this;
                }
            }
            var res;
            if (question.isRootSection()) {
                res = question.empty();
            } else {
                res = question.remove();
            }
            $.renumberQuestions();
            return res;
        },

        buildParentsQuestions: function() {
            var $this = $(this);
            $.questions().each(function() {
                var parent = $(this).parentQuestion();
                if (!parent.isRootSection()) {
                    $('<input>', {
                        type: 'hidden',
                        name: 'q_edit[' + $(this).qid() + '][parent]',
                        value: parent.qid()
                    }).appendTo($this);
                }
            });
            return $this;
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
