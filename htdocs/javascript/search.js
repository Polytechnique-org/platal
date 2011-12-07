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
var address_types = new Array('country', 'administrative_area_level_1', 'administrative_area_level_2', 'locality', 'postal_code');
var address_types_count = address_types.length;
var autocomplete_sub = {'country': 'locality_text'};

function load_advanced_search(request)
{
    $('.autocomplete_target').hide();
    $('.autocomplete').show().each(function() {
        $(this).autocomplete({
            source: baseurl + 'autocomplete/' + this.name,
            select: function(event, ui) {
                select_autocomplete(this.name, ui.item.id);
            },
            change: function(event, ui) {
                if (ui.item != null && ui.item.field != null) {
                    $(this).val(ui.item.field);
                }
            }
        });
    });

    $('.autocomplete').change(function() { $(this).removeClass('hidden_valid'); });

    if (request['country']) {
        setAddress(0, 1, new Array(request['country'],
                                   request['administrative_area_level_1'],
                                   request['administrative_area_level_2'],
                                   request['locality'],
                                   request['postal_code'])
        );
    } else {
        for (var i = 1; i < address_types_count; ++i) {
            $('tr#' + address_types[i]).hide();
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

    $('.delete_address_component').click(function() {
        var field_name = $(this).attr('href');
        var hide = false;
        var remove = false;

        for (var i = 1; i < address_types_count; ++i) {
            if (field_name == address_types[i]) {
                hide = true;
            }
            if (hide) {
                if (field_name != address_types[i]) {
                    remove = true;
                }
                delete_address_component(address_types[i], remove);
            }
        }

        return false;
    });
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

var default_form_values = [ /&woman=0(&|$)/, /&subscriber=0(&|$)/, /&alive=0(&|$)/, /&egal2=[^&]*&promo2=(&|$)/,
                            /&egal1=[^&]*&promo1=&edu_type=(?:Ing[^n]+nieur|Master|Doctorat)(&|$)/, /&networking_type=0(&|$)/,
                            /&origin_corps=0(&|$)/, /&current_corps=0(&|$)/,
                            /corps_rank=0(&|$)/, /&has_email_redirect=0(&|$)/, /&[^&=]+=(&|$)/g ];

/** Uses javascript to clean form from all empty fields */
function cleanForm(f, targeturl)
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
    query = query.replace(/^(.*)&+$/, '$1');
    query = query.replace(/^&+(.*)$/, '$1');
    if (query == 'rechercher=Chercher') {
        alert("Aucun critère n'a été spécifié.");
        return false;
    }
    document.location = $.plURL(targeturl + '?' + query);
    return false;
}

// }}}
// {{{ Autocomplete related functions.

function cancel_autocomplete(field, realfield)
{
    $(".autocomplete[name='" + field + "']").removeClass('hidden_valid').val('').focus();
    if (typeof(realfield) != 'undefined') {
        $(".autocomplete_target[name='" + realfield + "']").val('');
    }
    return;
}

// when choosing autocomplete from list, must validate
function select_autocomplete(name, id)
{
    var field_name = name.replace(/_text$/, '');
    if (autocomplete_sub[field_name] != null) {
        $(".autocomplete[name='" + autocomplete_sub[field_name] + "']").autocomplete('option', 'source', baseurl + 'autocomplete/' + autocomplete_sub[field_name] + '/' + id);
    }

    // just display field as valid if field is not a text field for a list
    if (field_name == name) {
        $(".autocomplete[name='" + name + "']").addClass('hidden_valid');
        return;
    }

    // When changing country, locality or school, open next address component.
    if (field_name == 'country' || field_name == 'locality' || field_name == 'school') {
        if (id < 0) {
            cancel_autocomplete(name, field_name);
            id = '';
        }

        if (field_name == 'school') {
            changeSchool(id, '');
        } else {
            changeAddressComponents(field_name, id);
        }

        $(".autocomplete_target[name='" + field_name + "']").attr('value', id);
        $(".autocomplete[name='" + name + "']").addClass('hidden_valid');
        return;
    }

    // change field in list and display text field as valid
    if (id < 0) {
        cancel_autocomplete(this.field, field_name);
        return;
    }

    $(".autocomplete_target[name='" + field_name + "']").attr('value', id);
    $(".autocomplete[name='" + name + "']").addClass('hidden_valid');
}

// }}}
// {{{ Various search functions.

function setAddress(i, j, values)
{
    var prev_type = address_types[i];
    var next_type = address_types[j];
    var next_list = next_type + '_list';

    $('#' + next_list).load(baseurl + 'list/' + next_type, { previous:prev_type, value:values[i] }, function() {
        if ($("select[name='" + next_type + "']").children("option").size() > 1) {
            $("tr#" + next_type).show();
            $("select[name='" + next_type + "']").attr('value', values[j]);
            if (next_type == 'locality') {
                $('tr#locality_text').hide();
                $("select[name='locality_text']").attr('value', '');
            }
            if (j < address_types_count) {
                setAddress(j, j + 1, values);
            }
        } else {
            $("tr#" + next_type).hide();
            $("select[name='" + next_type + "']").attr('value', '');
            if (j < address_types_count) {
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

    if (next_type == 'locality') {
        $('tr#locality_text').hide();
        $("select[name='locality_text']").attr('value', '');
    }

    if (autocomplete_sub[prev_type] != null) {
        $(".autocomplete[name='" + autocomplete_sub[prev_type] + "']").autocomplete('option', 'source', baseurl + 'autocomplete/' + autocomplete_sub[prev_type] + '/' + value);
    }

    $('#' + next_list).load(baseurl + 'list/' + next_type, { previous:prev_type, value:value }, function() {
        $("select[name='" + next_type + "']").attr('value', '');
        if ($("select[name='" + next_type + "']").children('option').size() > 1) {
            $('tr#' + next_type).show();
        } else {
            $('tr#' + next_type).hide();
            if (j < address_types_count) {
                displayNextAddressComponent(i, j + 1, value);
            }
        }
    });
}

function changeAddressComponents(type, value)
{
    var i = 0;

    while (address_types[i] != type && i < address_types_count) {
        ++i;
    }

    for (var j = i + 1; j < address_types_count; ++j) {
        delete_address_component(address_types[j], true);
    }

    if (value != '' && i < address_types_count) {
        $("select[name='" + type + "']").attr('value', value);
        displayNextAddressComponent(i, i + 1, value);
    }
}

function delete_address_component(field_name, remove)
{
    if (remove || field_name == 'locality') {
        $('tr#' + field_name).hide();
        $('#' + field_name + '_list').html('');

        if (field_name == 'locality') {
            $("input[name='locality_text']").val('');
            $('tr#locality_text').show();
        }
    } else {
        $("select[name='" + field_name + "']").val('');
        $("input[name='" + field_name + "']").val('');
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

// when choosing a job term in tree, hide tree and set job term field
function searchForJobTerm(treeid, jtid, full_name)
{
    $(".term_tree").remove();
    $("input[name='jobterm_text']").val(full_name).addClass("hidden_valid").show();
    $("input[name='jobterm']").val(jtid);
}

function addressesDump()
{
    if ($('#addresses_dump:checked').length > 0) {
        $('#recherche').attr('action', 'search/adv/addresses').attr('method', 'post').removeAttr('onsubmit');
    } else {
        $('#recherche').attr('action', 'search/adv').attr('method', 'get');
    }
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
