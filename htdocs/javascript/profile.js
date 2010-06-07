/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

// Page initialization {{{1

function wizPage_onLoad(id)
{
    switch (id) {
      case 'general':
        var i = 1;
        while ($('.edu_' + i).length != 0) {
            prepareType(i - 1);
            ++i;
        }
        break;
      case 'adresses':
        checkCurrentAddress();
        break;
      case 'poly':
        updateGroupSubLink();
        break;
      case 'deco':
        for (var i in names) {
            if ($('#medal_' + i).length != 0) {
                getMedalName(i);
                buildGrade(i, $('#medal_' + i).find('[name*=medal_' + i + '_grade]').val());
            }
        }
        break;
      case 'emploi':
        for (var i = 0 ; $('#job_' + i).length != 0; ++i) {
            updateJobSector(i, $('#job_' + i).find("[name='jobs[" + i + "][subSector]']").val());
            updateJobSubSector(i, $('#job_' + i).find("[name='jobs[" + i + "][subSubSector]']").val());
            updateJobAlternates(i);
        }
        break;
    }
}

var educationDegree;
var educationDegreeAll;
var educationDegreeName;
var subgrades;
var names;

// Names {{{1

function toggleNamesAdvanced()
{
    $('.names_advanced').toggle();
}

function addSearchName()
{
    var i = 0;
    while ($('#search_name_' + i).length != 0) {
        i++;
    }
    Ajax.update_html('search_name_' + i, 'profile/ajax/searchname/' + i, function(data){
        $('#searchname').before(data);
        changeNameFlag(i);
    });
}

function removeSearchName(i, isFemale)
{
    $('#search_name_' + i).remove();
    updateNameDisplay(isFemale);
}

function changeNameFlag(i)
{
    $('#flag_' + i).remove();
    var typeid = $('#search_name_' + i).find('select').val();
    var type   = $('#search_name_' + i).find('select :selected').text();
    if ($('[name=sn_type_' + typeid + '_' + i + ']').val() > 0) {
        $('#flag_cb_' + i).after('<span id="flag_' + i + '">&nbsp;' +
            '<img src="images/icons/flag_green.gif" alt="site public" title="site public" />' +
            '<input type="hidden" name="search_names[' + i + '][pub]" value="1"/>' +
            '<input type="hidden" name="search_names[' + i + '][typeid]" value="' + typeid + '"/>' +
            '<input type="hidden" name="search_names[' + i + '][type]" value="' + type + '"/></span>');
    } else {
        $('#flag_cb_' + i).after('<span id="flag_' + i + '">&nbsp;' +
            '<img src="images/icons/flag_red.gif" alt="site privé" title="site privé" />' +
            '<input type="hidden" name="search_names[' + i + '][typeid]" value="' + typeid + '"/>' +
            '<input type="hidden" name="search_names[' + i + '][type]" value="' + type + '"/></span>');
    }
}

function updateNameDisplay(isFemale)
{
    var searchnames = '';
    for (var i = 0; i < 10; i++) {
        if ($('#search_name_' + i).find(':text').val()) {
            searchnames += $('#search_name_' + i).find('[name*=typeid]').val() + ';';
            searchnames += $('#search_name_' + i).find(':text').val() + ';;';
        }
    }
    Ajax.update_html(null, 'profile/ajax/buildnames/' + searchnames + '/' + isFemale, function(data){
        var name = data.split(';');
        $('#public_name').html(name[0]);
        $('#private_name').html(name[0] + name[1]);
    });
}

function toggleParticle(id)
{
    if ($('#search_name_' + id).find("[name*='[particle]']").val() == '') {
        $('#search_name_' + id).find("[name*='[particle]']").val(1);
    } else {
        $('#search_name_' + id).find("[name*='[particle]']").val('');
    }
}

// Promotions {{{1

function togglePromotionEdition()
{
    $(".promotion_edition").toggle();
}

// Nationalities {{{1

function delNationality(i)
{
    $('#nationalite' + i).hide().find('select').val('');
}

function addNationality()
{
    var i = 0;
    if ($('#nationality2').find('select').val() == "") {
        i = 2;
    } else if ($('#nationality3').find('select').val() == "") {
        i = 3;
    }
    if ((i == 2) || (i == 3)) {
        $('#nationality' + i).show();
    }
}

// Education {{{1

function prepareType(id)
{
    var edu    = $('.edu_' + id).find("[name='edus[" + id + "][eduid]']").val() - 1;
    var sel    = $('.edu_' + id).find('[name=edu_' + id + '_tmp]').val();
    var html   = '';
    var length = educationDegree[edu].length;
    for (i = 0; i < length; ++i) {
        html += '<option value="' + educationDegree[edu][i] + '"';
        if (sel == educationDegree[edu][i]) {
            html += ' selected="selected"';
        }
        html += '>' + educationDegreeName[educationDegree[edu][i] - 1] + '</option>';
    }
    $('.edu_' + id).find("[name='edus[" + id + "][degreeid]']").html(html);
}

function addEdu()
{
    var i = 0;
    var j = 0;
    var prefix  = 'edu_';
    var class_parity;

    while (!$('#edu_add').hasClass(prefix + i)) {
        if ($('.' + prefix + i).length != 0) {
            j++;
        }
        i++;
    }
    if (j % 2) {
        class_parity = 'pair';
    } else {
        class_parity = 'impair';
    }
    $('#edu_add').removeClass(prefix + i);
    i++;
    $('#edu_add').addClass(prefix + i);
    i--;
    $.get(platal_baseurl + 'profile/ajax/edu/' + i + '/' + class_parity,
          function(data) {
              $('#edu_add').before(data);
              prepareType(i);
          });
}

function removeEdu(i)
{
    var prefix  = 'edu_';
    $('.' + prefix + i).remove();
    while (!$('#edu_add').hasClass(prefix + i)) {
        $('.' + prefix + i).toggleClass('pair');
        $('.' + prefix + i).toggleClass('impair');
        i++;
    }
}

// Networking {{{1

function addNetworking()
{
    var i = 0;
    while ($('#networking_' + i).length != 0) {
        i++;
    }
    var namefirst = '';
    var html = '<tr id="networking_' + i + '">'
        + '  <td colspan="2">'
        + '    <div style="float: left; width: 200px;">'
        + '      <span class="flags">'
        + '        <input type="checkbox" name="networking[' + i + '][pub]"/>'
        + '        <img src="images/icons/flag_green.gif" alt="site public" title="site public">'
        + '      </span>&nbsp;'
        + '      <select name="networking[' + i + '][type]" onchange="javascript:updateNetworking(' + i + ');">';
    for (nw in nw_list) {
        if (namefirst == '') {
            namefirst = nw;
        }
        html += '  <option value="' + nw_list[nw] + '">' + nw + '</option>';
    }
    html += '</select>'
        + '      <input type="hidden" name="networking[' + i + '][name]" value="' + namefirst + '"/>'
        + '    </div>'
        + '    <div style="float: left">'
        + '      <input type="text" name="networking[' + i + '][address]" value="" size="30"/>'
        + '      <a href="javascript:removeNetworking(' + i + ')">'
        + '        <img src="images/icons/cross.gif" alt="cross" title="Supprimer cet élément"/>'
        + '      </a>'
        + '    </div>'
        + '  </td>'
        + '</tr>';

    $('#networking').before(html);
}

function removeNetworking(id)
{
    $('#networking_' + id).remove();
}

function updateNetworking(i)
{
    $('#networking_' + i).find("[name='networking[" + i + "][name]']").val($('#networking_' + i).find('select option:selected').text());
}

// Addresses {{{1

function toggleAddress(id, val)
{
    $('#addresses_' + id + '_grayed').toggle();
    $('#addresses_' + id).toggle();
    $('#addresses_' + id + '_cont').find('[name*=removed]').val(val);
    checkCurrentAddress();
}

function checkCurrentAddress(id)
{
    var hasCurrentAddress = id ? true : false;
    var i = 0;
    while ($('#addresses_' + i + '_cont').length != 0) {
        if ($('#addresses_' + i + '_cont').find('[name*=removed]').val() == 1) {
            $('#addresses_' + i + '_cont').find('[name*=current]').attr('checked', false);
        }
        if (!hasCurrentAddress && $('#addresses_' + i + '_cont').find('[name*=current]:checked').length != 0) {
            hasCurrentAddress = true;
        } else {
            $('#addresses_' + i + '_cont').find('[name*=current]').attr('checked', false);
        }
        i++;
    }
    if (!hasCurrentAddress) {
        i = 0;
        while ($('#addresses_' + i + '_cont').length != 0) {
               if ($('#addresses_' + i + '_cont').find('[name*=removed]').val() == 0) {
                   $('#addresses_' + i + '_cont').find('[name*=current]').attr('checked', 'checked');
                   break;
               }
               i++;
        }
    }
    if (id) {
        $('#addresses_' + id + '_cont').find('[name*=current]').attr('checked', 'checked');
    }
}

function addAddress()
{
    var i = 0;
    while ($('#addresses_' + i + '_cont').length != 0) {
        i++;
    }
    $('#add_address').before('<div id="addresses_' + i + '_cont"></div>');
    Ajax.update_html('addresses_' + i + '_cont', 'profile/ajax/address/' + i, checkCurrentAddress());
}

function addressChanged(prefid)
{
    $('#' + prefid + '_cont').find('[name*=changed]').val("1");
}

function validGeoloc(prefid, id, geoloc)
{
    if (geoloc == 1) {
        $('#' + prefid + '_cont').find('[name*=text]').val($('#' + prefid + '_cont').find('[name*=geoloc]').val());
        $('#' + prefid + '_cont').find('[name*=postalText]').val($('#' + prefid + '_cont').find('[name*=geocodedPostalText]').val());
    }
    if (geoloc > 0) {
        $('#' + prefid + '_cont').find("[name*='[geoloc]']").remove();
    }
    $('#' + prefid + '_cont').find('[name*=text]').removeClass('error');
    $('#' + prefid + '_cont').find('[name*=geoloc_choice]').val(geoloc);
    $('.' + prefid + '_geoloc').remove();
}

// {{{1 Phones

function addTel(prefid, prefname)
{
    var i = 0;
    var prefix  = prefid + '_';
    while ($('#' + prefix + i).length != 0) {
        i++;
    }
    $('#' + prefix + 'add').before('<div id="' + prefix + i + '" style="clear: both; padding-top: 4px; padding-bottom: 4px"></div>');
    Ajax.update_html(prefix + i, 'profile/ajax/tel/' + prefid + '/' + prefname + '/' + i);
}

function removeTel(id)
{
    $('#' + id).remove();
}

function addPhoneComment(id)
{
    $('#' + id + '_comment').show();
    $('#' + id + '_addComment').hide();
}

function removePhoneComment(id, pref)
{
    $('#' + id + '_comment').hide();
    $('#' + id + '_comment').find("[name='" + pref + "[comment]']").val('');
    $('#' + id + '_addComment').show();
}

// {{{1 Groups

function addBinet()
{
    var id   = $('#binets_table').find('[name=binets_sel]').val();
    var text = $('#binets_table').find('select option:selected').text();
    var html = '<tr id="binets_' + id + '">'
             + '  <td>'
             + '    <input type="hidden" name="binets[' + id + ']" value="' + text + '" />'
             + '  </td>'
             + '  <td>'
             + '    <div style="float: left; width: 70%">'
             +        text
             + '    </div>'
             + '    <a href="javascript:removeElement(\'binets\',' + id + ')">'
             + '      <img src="images/icons/cross.gif" alt="cross" title="Supprimer ce groupe" />'
             + '    </a>'
             + '  </td>'
             + '</tr>';
    $('#binets_table').after(html);
    updateElement('binets');
}

function updateGroupSubLink()
{
    var href = $('[name*=groupesx_sub]').val() ? $('[name*=groupesx_sub]').val() : 'http://www.polytechnique.net';
    $('#groupesx_sub').attr('href', href);
}

// {{{1 Medals

function updateMedal()
{
    var val = $('#medals').find('[name*=medal_sel]').val();
    if (val && ($('#medal_' + val).length == 0)) {
        $('#medal_add').show();
    } else {
        $('#medal_add').hide();
    }
}

function getMedalName(id)
{
    $('#medal_name_' + id).html(names[id]);
}

function buildGrade(id, current)
{
    var grade;
    var subg = subgrades[id];
    var obj  = $('#medal_grade_' + id);
    if (!subg) {
        obj.prepend('<input type="hidden" name="medals[' + id + '][grade]" value="0" />');
    } else {
        var html = 'Agrafe : <select name="medals[' + id + '][grade]">';
        html += '<option value="0">Non précisée</option>';
        for (grade = 0 ; grade < subg.length ; grade++) {
            html += '<option value="' + subg[grade][0] + '"';
            if (subg[grade][0] == current) {
                html += ' selected="selected"';
            }
            html += '>' + subg[grade][1] + '</option>';
        }

        html += '</select>';
        obj.prepend(html);
    }
}

function makeAddProcess(id)
{
    return function(data)
    {
        $('#medals').after(data);
        updateMedal();
        getMedalName(id);
        buildGrade(id, 0);
    };
}

function addMedal()
{
    var id = $('#medals').find('[name=medal_sel]').val();
    $.get(platal_baseurl + 'profile/ajax/medal/' + id, makeAddProcess(id));
}

function removeMedal(id)
{
    $("#medal_" + id).remove();
    updateMedal();
}

// Jobs {{{1

function removeJob(id, pref)
{
    $('#' + id + '_cont').hide();
    if ($('#' + id).find("[name='" + pref + "[new]']").val() == '0') {
        $('#' + id + '_grayed').show();
        $('#' + id + '_grayed_name').html($('#' + id).find("[name='" + pref + "[name]']").val());
    }
    $('#' + id).find("[name='" + pref + "[removed]']").val('1');
}

function restoreJob(id, pref)
{
    $('#' + id + '_cont').show();
    $('#' + id + '_grayed').hide();
    $('#' + id).find("[name='" + pref + "[removed]']").val('0');
}

function updateJobSector(id, sel)
{
    var sector = $('#job_' + id).find("[name='jobs[" + id + "][sector]']").val();
    if (sector == '') {
        sector = '-1';
    }
    Ajax.update_html('job_' + id + '_subSector', 'profile/ajax/sector/' + id + '/job_' + id + '/jobs[' + id + ']/' + sector + '/' + sel);
}

function updateJobSubSector(id, sel)
{
    var subSector = $('#job_' + id).find("[name='jobs[" + id + "][subSector]']").val();
    if (subSector == '') {
        subSector = '-1';
    }
    Ajax.update_html('job_' + id + '_subSubSector', 'profile/ajax/sub_sector/' + id + '/' + subSector + '/' + sel);
}

function updateJobAlternates(id)
{
    var subSubSector = $('#job_' + id).find("[name='jobs[" + id + "][subSubSector]']").val();
    if (subSubSector != '') {
        Ajax.update_html('job_' + id + '_alternates', 'profile/ajax/alternates/' + id + '/' + subSubSector);
    }
}

function emptyJobSubSector(id)
{
    Ajax.update_html('job_' + id + '_subSubSector', 'profile/ajax/sub_sector/' + id + '/-1/-1');
}

function emptyJobAlternates(id)
{
    Ajax.update_html('job_' + id + '_alternates', 'profile/ajax/alternates/' + id + '/-1');
}

function displayAllSector(id)
{
    $('.sector_text_' + id).remove();
    $('.sector_' + id).show();
}

function makeAddJob(id)
{
    return function(data)
    {
        $('#add_job').before(data);
        registerEnterpriseAutocomplete(id);
    };
}

function addJob()
{
    var i = 0;
    while ($('#job_' + i).length != 0) {
        ++i;
    }
    $.get(platal_baseurl + 'profile/ajax/job/' + i, makeAddJob(i));
}

function addEntreprise(id)
{
    $('.entreprise_' + id).toggle();
}

// {{{1 Skills

function addSkill(cat)
{
    var val  = $('#' + cat + '_table').find('[name=' + cat + '_sel]').val();
    var text = $('#' + cat + '_table').find('[name=' + cat + '_sel] :selected').text();
    $.get(platal_baseurl + 'profile/ajax/skill/' + cat + '/' + val,
          function(data) {
              $('#' + cat).append(data);
              $('#' + cat + '_' + val + '_title').text(text);
              updateElement(cat);
          });
}

// {{{1 Mentor

function addCountry()
{
    var val  = $('#countries_table').find('[name=countries_sel] :selected').val();
    var text = $('#countries_table').find('[name=countries_sel] :selected').text();
    var html = '<div id="countries_' + val + '" style="clear: both; margin-bottom: 0.7em">'
        + '  <a href="javascript:removeElement(\'countries\',\'' + val + '\')" style="display: block; float:right">'
        + '    <img src="images/icons/cross.gif" alt="" title="Supprimer ce pays" />'
        + '  </a>'
        + '  <div style="float: left; width: 50%">' + text + '</div>'
        + '  <input type="hidden" name="countries[' + val + ']" value="' + text + '" />'
        + '</div>';
    $('#countries').append(html);
    updateElement('countries');
}

function updateSubSector()
{
    var s  = $('#sectorSelection').find('[name=sectorSelection]').val();
    var ss = $('#subSectorSelection').find("[name='jobs[-1][subSector]']").val();
    if ((s == '' || ss == '') || $('#sectors_' + s + '_' + ss).length != 0) {
        $('#addSector').hide();
    } else {
        $('#addSector').show();
    }
}

function removeSector(s, ss)
{
    $('#sectors_' + s + '_' + ss).remove();
    updateSubSector();
}

function updateSector()
{
    var sector = $('#sectorSelection').find('[name=sectorSelection]').val();
    if (sector == '') {
        sector = '-1';
        $('#subSectorSelection').html('');
        return;
    }
    $.get(platal_baseurl + 'profile/ajax/sector/-1/0/0/' + sector,
          function(data) {
              data = '<a href="javascript:addSector()" style="display: none; float: right" id="addSector">'
                   + '  <img src="images/icons/add.gif" alt="Ajouter ce secteur" title="Ajouter ce secteur" />'
                   + '</a>' + data;
              $('#subSectorSelection').html(data);
              $('#subSectorSelection').find("[name='jobs[-1][subSector]']").change(updateSubSector);
          });
}

function addSector()
{
    var s   = $('#sectorSelection').find('[name=sectorSelection]').val();
    var ss  = $('#subSectorSelection').find("[name='jobs[-1][subSector]']").val();
    var sst = $('#subSectorSelection').find("[name='jobs[-1][subSector]'] :selected").text();

    var html = '<div id="sectors_' + s + '_' + ss + '" style="clear: both; margin-top: 0.5em" class="titre">'
             + '  <a href="javascript:removeSector(\'' + s + '\',\'' + ss + '\')" style="display: block; float: right">'
             + '    <img src="images/icons/cross.gif" alt="" title="Supprimer ce secteur" />'
             + '  </a>'
             + '  <input type="hidden" name="sectors[' + s + '][' + ss + ']" value="' + sst + '" />'
             + '  ' + sst
             + '</div>';
    $('#sectors').append(html);
    updateSubSector();
}

function registerEnterpriseAutocomplete(id)
{
    $(".enterpriseName").each(
      function() {
        if (id == -1 || this.name == "jobs[" + id + "][name]") {
            $(this).autocomplete(platal_baseurl + "search/autocomplete/entreprise",
                                 {
                                     selectOnly:1,
                                     field:this.name,
                                     matchSubset:0,
                                     width:$(this).width()
                                 });
        }
      });

    $(".sectorName").each(
      function() {
        if (id == -1 || this.name == "jobs[" + id + "][subSubSectorName]") {
            $(this).autocomplete(platal_baseurl + "search/autocomplete/subSubSector",
                                 {
                                     selectOnly:1,
                                     field:this.name,
                                     matchSubset:0,
                                     width:$(this).width()
                                 });
        }
      });
}

// {{{1 Multiusage functions

function updateElement(cat)
{
    var val = $('#' + cat + '_table').find('[name=' + cat + '_sel]').val();
    if (val == '' || $('#' + cat + '_' + val).length != 0) {
        $('#' + cat + '_add').hide();
    } else {
        $('#' + cat + '_add').show();
    }
}

function removeElement(cat, id)
{
    $('#' + cat + '_' + id).remove();
    updateElement(cat);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
