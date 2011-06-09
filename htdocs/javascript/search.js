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

// {{{ Page initialization

var baseurl = $.plURL('search/');
var address_types = new Array('country', 'administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3', 'locality', 'sublocality');

function load_advanced_search(request)
{
    $('.autocomplete_target').hide();
    $('.autocomplete').show().each(function() {
        $(this).autocomplete(baseurl + 'autocomplete/' + this.name, {
            selectOnly: 1,
            formatItem: make_format_autocomplete(this),
            field: this.name,
            onItemSelect: select_autocomplete(this.name),
            matchSubset: 0,
            width: $(this).width()
        });
    });

    $('.autocomplete').change(function() { $(this).removeClass('hidden_valid'); });

    if (request['country']) {
        $("[name='country']").parent().load(baseurl + 'list/country', function() {
            $("select[name='country']").attr('value', request['country']);
        });
        setAddress(0, 1, new Array(request['country'],
                                   request['administrative_area_level_1'],
                                   request['administrative_area_level_2'],
                                   request['administrative_area_level_3'],
                                   request['locality'],
                                   request['sublocality'])
        );
    } else {
        for (var i = 1; i < 6; ++i) {
            $('tr#' + address_types[i] + '_list').hide();
        }
    }

    $(".autocomplete[name='school_text']").change(function() { changeSchool('', ''); });
    changeSchool(request['school'], request['diploma']);

    $(".autocomplete_to_select").each(function() {
        var field_name = $(this).attr('href');

        if ($(".autocomplete_target[name='" + field_name + "']").val()) {
            display_list(field_name);
        }

        $(this).attr('href', baseurl + 'list/' + field_name).click(function() {
            if ($(this).attr('title') == 'display') {
                display_list(field_name);
            } else {
                var value = $("select[name='" + field_name + "']").val();
                var text_value = $("select[name='" + field_name + "'] option:selected").text();
                $('#' + field_name + '_list').html('');
                $(".autocomplete[name='" + field_name + "_text']").show();
                $('#' + field_name + '_table').attr('title', 'display');
                if (value) {
                    $(".autocomplete_target[name='" + field_name + "']").val(value);
                    $(".autocomplete[name='" + field_name + "_text']").val(text_value).addClass('hidden_valid');
                }
            }

            return false;
        });
    });

    $('#only_referent').change(function() { changeOnlyReferent(); });
}

function display_list(field_name)
{
    var value = $("input.autocomplete_target[name='" + field_name + "']").val();

    $('#' + field_name + '_list').load(baseurl + 'list/' + field_name, {}, function(selectBox) {
        $(".autocomplete_target[name='" + field_name + "']").val('');
        $(".autocomplete[name='" + field_name + "_text']").hide().val('').removeClass('hidden_valid');
        $("select[name='" + field_name + "']").val(value);
        $('#' + field_name + '_table').attr('title', 'hide');
    });
}

// }}}
// {{{ Regexps to wipe out from search queries

var default_form_values = [ /&woman=0(&|$)/, /&subscriber=0(&|$)/, /&alive=0(&|$)/, /&egal[12]=[^&]*&promo[12]=(&|$)/g, /&networking_type=0(&|$)/, /&[^&=]+=(&|$)/g ];

/** Uses javascript to clean form from all empty fields */
function cleanForm(f)
{
    var query = $(f).formSerialize();
    var old_query;
    for (var i in default_form_values) {
        var reg = default_form_values[i];
        if (typeof(reg) != 'undefined') {
            do {
                old_query = query;
                query = query.replace(reg, '$1');
            } while (old_query != query);
        }
    }
    query = query.replace(/^&*(.*)&*$/, '$1');
    if (query == 'rechercher=Chercher') {
        alert("Aucun critère n'a été spécifié.");
        return false;
    }
    document.location = baseurl + 'adv?' + query;
    return false;
}

// }}}
// {{{ Autocomplete related functions.

// display an autocomplete row : blabla (nb of found matches)
function make_format_autocomplete(block)
{
    return function(row) {
        regexp = new RegExp('(' + RegExp.escape(block.value) + ')', 'i');
        name = row[0].htmlEntities().replace(regexp, '<strong>$1<\/strong>');

        if (row[1] === '-1') {
            return '&hellip;';
        }
        if (row[1] === '-2') {
            return '<em>aucun camarade trouvé pour '+row[0].htmlEntities()+'<\/em>';
        }

        mate = (row[1] > 1) ? 'camarades' : 'camarade';
        return name + '<em>&nbsp;&nbsp;-&nbsp;&nbsp;' + row[1].htmlEntities() + '&nbsp;' + mate + '<\/em>';
    };
}

function cancel_autocomplete(field, realfield)
{
    $(".autocomplete[name='" + field + "']").removeClass('hidden_valid').val('').focus();
    if (typeof(realfield) != 'undefined') {
        $(".autocomplete_target[name='" + realfield + "']").val('');
    }
    return;
}

// when choosing autocomplete from list, must validate
function select_autocomplete(name)
{
    var field_name = name.replace(/_text$/, '');

    // nothing to do if field is not a text field for a list
    if (field_name == name) {
        return null;
    }

    // When changing country or locality, open next address component.
    if (field_name == 'country' || field_name == 'locality') {
        return function(i) {
            if (i.extra[0] < 0) {
                cancel_autocomplete(name, field_name);
                i.extra[1] = '';
            }
            $("[name='" + field_name + "']").parent().load(baseurl + 'list/' + field_name, function() {
                $("select[name='" + field_name + "']").attr('value', i.extra[1]);
            });
            changeAddressComponents(field_name, i.extra[1]);
        }
    }

    if (field_name == 'school')
        return function(i) {
            if (i.extra[0] < 0) {
                cancel_autocomplete('school_text', 'school');
                i.extra[1] = '';
            }
            changeSchool(i.extra[1], '');
        }

    // change field in list and display text field as valid
    return function(i) {
        if (i.extra[0] < 0) {
            cancel_autocomplete(this.field, field_name);
            return;
        }

        $(".autocomplete_target[name='" + field_name + "']").attr('value', i.extra[1]);
        $(".autocomplete[name='" + this.field + "']").addClass('hidden_valid');
    }
}

// }}}
// {{{ Various search functions.

function setAddress(i, j, values)
{
    var prev_type = address_types[i];
    var next_type = address_types[j];
    var next_list = next_type + '_list';

    if (j == 3) {
        $('tr#locality_text').hide()
            $("select[name='locality_text']").attr('value', '');
    }

    $("[name='" + next_type + "']").parent().load(baseurl + 'list/' + next_type, { previous:prev_type, value:values[i] }, function() {
        if ($("select[name='" + next_type + "']").children("option").size() > 1) {
            $("tr#" + next_list).show();
            $("select[name='" + next_type + "']").attr('value', values[j]);
            if (j < 6) {
                setAddress(j, j + 1, values);
            }
        } else {
            $("tr#" + next_list).hide();
            $("select[name='" + next_type + "']").attr('value', '');
            if (j < 6) {
                setAddress(i, j + 1, values);
            }
        }
    });
}

function displayNextAddressComponent(i, j, value)
{
    var prev_type = address_types[i];
    var next_type = address_types[j];
    var next_list = next_type + '_list';

    if (j == 3) {
        $('tr#locality_text').hide();
        $("select[name='locality_text']").attr('value', '');
    }

    $("[name='" + next_type + "']").parent().load(baseurl + 'list/' + next_type, { previous:prev_type, value:value }, function() {
        $("select[name='" + next_type + "']").attr('value', '');
        if ($("select[name='" + next_type + "']").children('option').size() > 1) {
            $('tr#' + next_list).show();
        } else {
            $('tr#' + next_list).hide();
            if (j < 6) {
                displayNextAddressComponent(i, j + 1, value);
            }
        }
    });
}

function changeAddressComponents(type, value)
{
    var i = 0, j = 0;

    while (address_types[i] != type && i < 6) {
        ++i;
    }

    j = i + 1;
    while (j < 6) {
        $("select[name='" + address_types[j] + "']").attr('value', '');
        $('tr#' + address_types[j] + '_list').hide();
        ++j;
    }

    if (value != '' && i < 5) {
        $("select[name='" + type + "']").attr('value', value);
        displayNextAddressComponent(i, i + 1, value);
    }
}

// when changing school, open diploma choice
function changeSchool(schoolId, diploma)
{
    $(".autocompleteTarget[name='school']").attr('value', schoolId);

    if (schoolId) {
        $(".autocomplete[name='school_text']").addClass('hidden_valid');
    } else {
        $(".autocomplete[name='school_text']").removeClass('hidden_valid');
    }

    $("[name='diploma']").parent().load(baseurl + 'list/diploma/', { school:schoolId }, function() {
        $("select[name='diploma']").attr('value', diploma);
    });
}

// when checking/unchecking "only_referent", disable/enable some fields
function changeOnlyReferent()
{
    if ($("#only_referent").is(':checked')) {
        $("input[name='entreprise']").attr('disabled', true);
    } else {
        $("input[name='entreprise']").removeAttr('disabled');
    }
}

// when choosing a job term in tree, hide tree and set job term field
function searchForJobTerm(treeid, jtid, full_name)
{
    $(".term_tree").remove();
    $("input[name='jobterm_text']").val(full_name).addClass("hidden_valid").show();
    $("input[name='jobterm']").val(jtid);
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
