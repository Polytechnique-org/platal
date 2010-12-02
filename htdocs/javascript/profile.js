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
        if ($('#job_0').find("[name='jobs[0][name]']").val() == '') {
            registerEnterpriseAutocomplete(0);
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

function addSearchName(isFemale)
{
    var i = 0;
    while ($('#search_name_' + i).length != 0) {
        i++;
    }
    $('#search_name_' + i)
        .updateHtml('profile/ajax/searchname/' + i + '/' + isFemale,
                    function(data) {
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
    $.xget('profile/ajax/buildnames/' + searchnames + '/' + isFemale,
           function(data){
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
    $('#nationality' + i).hide().find('select').val('');
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
    if (educationDegree[edu]) {
        var length = educationDegree[edu].length;
    } else {
        var length = 0;
    }
    for (i = 0; i < length; ++i) {
        html += '<option value="' + educationDegree[edu][i] + '"';
        if (sel == educationDegree[edu][i]) {
            html += ' selected="selected"';
        }
        html += '>' + educationDegreeName[educationDegree[edu][i] - 1] + '</option>';
    }
    // XXX: to be removed once SQL table profile_merge_issues is.
    if (sel != '' && html == '') {
        html += '<option value="' + sel + '" selected="selected">' + educationDegreeName[sel - 1] + '</option>';
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
    $('#addresses_' + i + '_cont').updateHtml('profile/ajax/address/' + i,
                                              checkCurrentAddress());
}

function addressChanged(prefid)
{
    $('#' + prefid + '_cont').find('[name*=changed]').val("1");
}

function validGeoloc(prefid, id, geoloc)
{
    if (geoloc == 1) {
        $('#' + prefid + '_cont').find('[name*=text]').val($('#' + prefid + '_cont').find('[name*=geocodedText]').val());
        $('#' + prefid + '_cont').find('[name*=postalText]').val('');
    }
    if (geoloc > 0) {
        $('#' + prefid + '_cont').find("[name*='[geocodedText]']").remove();
    }
    $('#' + prefid + '_cont').find('[name*=text]').removeClass('error');
    $('#' + prefid + '_cont').find('[name*=geocodeChosen]').val(geoloc);
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
    $('#' + prefix + i).updateHtml('profile/ajax/tel/' + prefid + '/' + prefname + '/' + i);
}

function removeTel(prefname, prefid, id)
{
    var total = 0;
    while ($('#' + prefid + '_' + total).length != 0) {
        ++total;
    }
    $('#' + prefid + '_' + id).remove();
    for (var i = parseInt(id) + 1; i < total; ++i) {
        renumberPhone(prefname, prefid, i);
    }
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

function renumberPhone(prefname, prefid, i)
{
    var telid = i - 1;
    var telprefOld = prefname + '[' + i + ']';
    var telpref = prefname + '[' + telid + ']';
    var idOld = prefid + '_' + i;
    var id = prefid + '_' + telid;

    $('#' + idOld).attr('id', id);
    $('#' + id).find('div.titre').html('N°' + i);
    $('#' + id).find('a.removeTel').attr('href', 'javascript:removeTel(\'' + prefname + '\',\'' + prefid + '\',' + telid + ')');
    $('#' + id).find('select').attr('name', telpref + '[type]');
    $('#' + id).find("[name='" + telprefOld + "[display]']").attr('name', telpref + '[display]');
    $('#' + id).find("[name='" + telprefOld + "[comment]']").attr('name', telpref + '[comment]');
    $('#' + id).find('a.removePhoneComment').attr('href', 'javascript:removePhoneComment(' + id + ',' + telpref + ')');
    $('#' + id).find('#' + idOld + '_addComment').attr('id', id + '_addComment');
    $('#' + id).find('#' + id + '_addComment').attr('href', 'javascript:addPhoneComment(' + id + ')');
    $('#' + id).find('#' + idOld + '_comment').attr('id', id + '_comment');
    $('#' + id).find("[name='" + telprefOld + "[pub]']").attr('name', telpref + '[pub]');
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

/**
 * Adds a job term in job profile page
 * @param jobid id of profile's job among his different jobs
 * @param jtid id of job term to add
 * @param full_name full text of job term
 * @return false if the term already exist for this job, true otherwise
 */
function addJobTerm(jobid, jtid, full_name)
{
    var termid = 0;
    var parentpath;
    var formvarname;
    if (jobid < 0) {
        parentpath = '';
        jobid = '';
        formvarname = 'terms';
    } else {
        parentpath = '#job_'+jobid+' ';
        formvarname = 'jobs['+jobid+'][terms]';
    }
    var lastJobTerm = $(parentpath + '.job_term:last');
    if (lastJobTerm.length != 0) {
        termid = parseInt(lastJobTerm.children('input').attr('name').replace(/^(jobs\[[0-9]+\]\[terms\]|terms)\[([0-9]+)\]\[jtid\]/, '$2')) + 1;
        if ($('#job'+jobid+'_term'+jtid).length > 0) {
            return false;
        }
    }
    var newdiv = '<div class="job_term" id="job'+jobid+'_term'+jtid+'">'+
        '<span>'+full_name+'</span>'+
        '<input type="hidden" name="'+formvarname+'['+termid+'][jtid]" value="'+jtid+'" />'+
        '<img title="Retirer ce mot-clef" alt="retirer" src="images/icons/cross.gif" />'+
        '</div>';
    if (lastJobTerm.length == 0) {
        $(parentpath + '.job_terms').prepend(newdiv);
    } else {
        lastJobTerm.after(newdiv);
    }
    $('#job'+jobid+'_term'+jtid+' img').css('cursor','pointer').click(removeJobTerm);
    return true;
}

/**
 * Remove a job term in job profile page.
 * Must be called from a button in a div containing the term
 */
function removeJobTerm()
{
    $(this).parent().remove();
}

/**
 * Prepare display for autocomplete suggestions in job terms
 * @param row an array of (title of term, id of term)
 * @return text to display
 * If id is negative, it is because there are too much terms to
 * be displayed.
 */
function displayJobTerm(row)
{
    if (row[1] < 0) {
        return '... <em>parcourir les résultats dans un arbre</em> ...';
    }
    return row[0];
}

/**
 * Function called when a job term has been selected from autocompletion
 * in search
 * @param li is the list item (<li>) that has been clicked
 * The context is the jsquery autocomplete object.
 */
function selectJobTerm(li)
{
    var jobid = this.extraParams.jobid;
    if (li.extra[0] >= 0) {
        addJobTerm(jobid,li.extra[0],$(li).text());
    }
    var search_input;
    if (jobid < 0) {
        search_input = $('.term_search')[0];
    } else {
        search_input = $('#job_'+jobid+' .term_search')[0];
    }
    if (li.extra[0] >= 0) {
        search_input.value = '';
        search_input.focus();
    } else {
        search_input.value = li.selectValue.replace(/%$/,'');
        toggleJobTermsTree(jobid, li.selectValue);
    }
}

/**
 * Function to show or hide a terms tree in job edition
 * @param jobid is the id of the job currently edited
 */
function toggleJobTermsTree(jobid, textfilter)
{
    var treepath;
    if (jobid < 0) {
        treepath = '';
    } else {
        treepath = '#job_'+jobid+' ';
    }
    treepath += '.term_tree';
    if ($(treepath + ' ul').length > 0) {
        $(treepath).empty().removeClass().addClass('term_tree');
        if (!textfilter) {
            return;
        }
    }
    createJobTermsTree(treepath, 'profile/ajax/tree/jobterms/all', 'job' + jobid, 'chooseJobTerm', textfilter);
}

/**
 * Function called when a job term is chosen from terms tree
 * @param treeid is the full id of the tree (must look like job3)
 * @param jtid is the id of the job term chosen
 * @param fullname is the complete name (understandable without context) of the term
 */
function chooseJobTerm(treeid, jtid, fullname)
{
    addJobTerm(treeid.replace(/^job(.*)$/, '$1'), jtid, fullname);
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
