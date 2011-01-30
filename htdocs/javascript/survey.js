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
    var dispatchType = function(method) {
        return function(type) {
            var name = type + '_' + method;
            var args = Array.prototype.slice.call(arguments, 1);
            if ($.isFunction(this[name])) {
                return this[name].apply(this, args);
            }
            return this;
        };
    };

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
                var fixAttrs;

                if (old_id === new_id) {
                    return;
                }

                fixAttrs = function(attr) {
                    elt.find('[' + attr + '^="' + old_id + '"]').each(function() {
                        var cid = $(this).attr(attr);
                        if (cid.startsWith(old_id)) {
                            $(this).attr(attr, new_id + cid.substring(old_id.length, cid.length));
                        }
                    });
                };
                fixAttrs('id');
                fixAttrs('name');
                elt.children().children('.q_edit_label').text('Question ' + (idx + 1));
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
            var q, child;
            for (q in questions) {
                q = questions[q];
                child = this.addQuestion(q);
                if ($.isArray(q.children)) {
                    child.prepareQuestions(q.children);
                }
            }
            return this;
        },

        isQuestion: function() {
            return this.hasClass('q_edit');
        },

        isRootSection: function() {
            return this.attr('id') === 'questions';
        },

        question: function() {
            return this.isQuestion() ? this : this.parentQuestion();
        },

        questionForm: function() {
            return this.question().children('.q_edit_form');
        },

        qid: function() {
            var question = this.question();
            if (typeof question.get(0) === 'undefined') {
                return;
            }
            var id = question.attr('id');
            if (id.substr(0, 7) !== 'q_edit[') {
                return;
            }
            if (id.charAt(id.length - 1) !== ']') {
                return;
            }
            id = id.substr(7, id.length - 8);
            return parseInt(id, 10);
        },

        parentQuestion: function() {
            return this.parent().closest('.q_edit');
        },

        childrenContainer: function() {
            var question = this.question();
            return question.isRootSection() ? question : question.questionForm().children();
        },

        childrenQuestion: function() {
            return this.childrenContainer().children('.q_edit');
        },

        addQuestion: function(q) {
            var id = $.lastQuestion().qid();
            if (!id) {
                id = 0;
            } else {
                id++;
            }
            if (!q) {
                q = { qid: id };
            }
            console.log(q);
            var question = $("#q_edit_new").tmpl(q);
            var type = question
                .find('select[name$="[type]"]')
                .change(function () {
                    var type = $(this).val();
                    var form = question.children('.q_edit_form');
                    var qid = $(this).qid();
                    form.empty();
                    if (type) {
                        $("#q_edit_base").tmpl({ qid: qid, type: type })
                            .bindQuestion(type, qid)
                            .appendTo(form);
                    }
                    return true;
                });
            if (type.val()) {
                question.children('.q_edit_form')
                        .bindQuestion(type.val(), q.qid, q)
            }
            this.childrenContainer().children('.add_question').before(question);
            $.renumberQuestions();
            return question;
        },

        bindQuestion: dispatchType('bindQuestion'),

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
        },

        /* Multiple choices questions */
        multiple_selectSubtype: function() {
            return this.find('select[name$="[subtype]"]');
        },

        multiple_bindQuestion: function(id, parameters) {
            var $question = this;
            var answer;
            var value;
            this.multiple_selectSubtype()
                .assertLength(1)
                .change(function() {
                    $question.find('.q_edit_answer_box')
                        .empty()
                        .append($('<input>', {
                            type: $(this).val(),
                            disabled: "disabled"
                        }));
                });
            if (parameters) {
                for (answer = 0; answer < parameters.answers.length; answer++) {
                    this.multiple_addAnswer(parameters.answers[answer]);
                }
            }
            return this;
        },

        multiple_addAnswer: function(value) {
            var question = this.question();
            var answer = $("#q_edit_multiple_answer").tmpl({ qid: question.qid(), value: value });
            question.childrenContainer().children('.add_answer').before(answer);
            question.multiple_selectSubtype().change();
            return answer;
        },

        multiple_removeAnswer: function() {
            return this.parent().remove();
        }
    });
}(jQuery));


$(function() {
    $(".datepicker").datepicker({
        hideIfNoPrevNext: true,
        minDate: new Date()
    });
});


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
