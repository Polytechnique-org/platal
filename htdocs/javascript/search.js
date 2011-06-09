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
    $('.autocompleteTarget').hide();
    $('.autocomplete').show().each(function() {
        targeted = $('../.autocompleteTarget', this)[0];

        if (targeted && targeted.value) {
            me = $(this);

            $.get(baseurl + 'list/' + targeted.name + '/' + targeted.value, {}, function(textValue) {
                me.attr('value', textValue);
                me.addClass('hidden_valid');
            });
        }

        $(this).autocomplete(baseurl + 'autocomplete/' + this.name, {
            selectOnly: 1,
            formatItem: make_format_autocomplete(this),
            field: this.name,
            onItemSelect: select_autocomplete(this.name),
            matchSubset: 0,
            width: $(this).width()}
        );
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

    $(".autocomplete[name='schoolTxt']").change(function() { changeSchool('', ''); });
    changeSchool(request['school'], request['diploma']);

    $(".autocompleteToSelect").each(function() {
        var fieldName = $(this).attr('href');

        $(this).attr('href', baseurl + 'list/' + fieldName).click(function() {
            var oldval = $("input.autocompleteTarget[name='" + fieldName + "']")[0].value;

            $(".autocompleteTarget[name='" + fieldName + "']").parent().load(baseurl + 'list/' + fieldName, {}, function(selectBox) {
                $(".autocompleteTarget[name='" + fieldName + "']").remove();
                $(".autocomplete[name='" + fieldName + "Txt']").remove();
                $("select[name='" + fieldName + "']").attr('value', oldval);
            });

            return false;
        });
    }).parent().find('.autocomplete').change(function() {
        // If we change the value in the type="text" field, then the value in the 'integer id' field must not be used,
        // to ensure that, we unset it
        $(this).parent().find('.autocompleteTarget').val('');
    });

    $('#only_referent').change(function() { changeOnlyReferent(); });
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
        $(".autocompleteTarget[name='" + realfield + "']").val('');
    }
    return;
}

// when choosing autocomplete from list, must validate
function select_autocomplete(name)
{
    nameRealField = name.replace(/Txt$/, '');

    // nothing to do if field is not a text field for a list
    if (nameRealField == name) {
        return null;
    }

    // When changing country or locality, open next address component.
    if (nameRealField == 'country' || nameRealField == 'locality') {
        return function(i) {
            nameRealField = name.replace(/Txt$/, '');
            if (i.extra[0] < 0) {
                cancel_autocomplete(name, nameRealField);
                i.extra[1] = '';
            }
            $("[name='" + nameRealField + "']").parent().load(baseurl + 'list/' + nameRealField, function() {
                $("select[name='" + nameRealField + "']").attr('value', i.extra[1]);
            });
            changeAddressComponents(nameRealField, i.extra[1]);
        }
    }

    if (nameRealField == 'school')
        return function(i) {
            if (i.extra[0] < 0) {
                cancel_autocomplete('schoolTxt', 'school');
                i.extra[1] = '';
            }
            changeSchool(i.extra[1], '');
        }

    // change field in list and display text field as valid
    return function(i) {
        nameRealField = this.field.replace(/Txt$/, '');
        if (i.extra[0] < 0) {
            cancel_autocomplete(this.field, nameRealField);
            return;
        }

        $(".autocompleteTarget[name='"+nameRealField+"']").attr('value',i.extra[1]);
        $(".autocomplete[name='"+this.field+"']").addClass('hidden_valid');
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
            $("select[name='localityTxt']").attr('value', '');
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
        $("select[name='localityTxt']").attr('value', '');
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
        $(".autocomplete[name='schoolTxt']").addClass('hidden_valid');
    } else {
        $(".autocomplete[name='schoolTxt']").removeClass('hidden_valid');
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
    $("input[name='jobtermTxt']").val(full_name).addClass("hidden_valid").show();
    $("input[name='jobterm']").val(jtid);
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
