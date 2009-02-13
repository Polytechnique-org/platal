/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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
        var i = 0;
        var prefix  = 'edu_';
        while ($('.' + prefix + i).length != 0) {
            i++;
        }
        i--;
        for (var j = 0; j < i; j++) {
            prepareType(j);
        }
        break;
      case 'adresses':
        checkCurrentAddress();
        break;
      case 'poly':
        updateGroupSubLink(document.forms.prof_annu.groupesx_sub);
        break;
      case 'deco':
        for (var i in names) {
            if (typeof names[i] != 'function') {
                if (document.getElementById("medal_" + i) != null) {
                    getMedalName(i);
                    buildGrade(i, document.forms.prof_annu["medal_" + i + "_grade"].value);
                }
            }
        }
        break;
      case 'emploi':
        for (var i = 0 ; document.getElementById('job_' + i) != null ; ++i) {
            updateJobSecteur(i, 'job_' + i, 'jobs[' + i + ']',
                             document.forms.prof_annu["jobs[" + i + "][ss_secteur]"].value);
            updateJobSousSecteur(i, 'job_' + i, 'jobs[' + i + ']',
                             document.forms.prof_annu["jobs[" + i + "][sss_secteur]"].value);
        }
        setTimeout('registerEnterpriseAutocomplete(-1)', 100);
        break;
    }
}

var educationDegree;
var educationDegreeAll;
var educationDegreeName;
var subgrades;
var names;

// Education {{{1

function fillType(selectCtrl, edu, fill)
{
    var i;
    var i0 = 0;

    for (i = selectCtrl.options.length; i >= 0; i--) {
        selectCtrl.options[i] = null;
    }

    if (fill || edu < 0) {
        selectCtrl.options[0] = new Option(' ');
        i0 = 1;
    }
    if (edu >= 0) {
        for (i = 0; i < educationDegree[edu].length; i++) {
            selectCtrl.options[i0 + i] = new Option(educationDegreeName[educationDegree[edu][i] - 1], educationDegree[edu][i]);
        }
    } else if (fill) {
        for (i = 0; i < educationDegreeAll.length; i++) {
            selectCtrl.options[i0 + i] = new Option(educationDegreeName[educationDegreeAll[i] - 1], educationDegreeAll[i]);
        }
    }
}


function selectType(selectCtrl, type)
{
    for (i = 0; i < selectCtrl.options.length; i++) {
        if (selectCtrl.options[i].value == type) {
            selectCtrl.selectedIndex = i;
        }
    }
}

function prepareType(i)
{
    fillType(document.forms.prof_annu["edus[" + i + "][degreeid]"], document.forms.prof_annu["edus[" + i + "][eduid]"].selectedIndex - 1);
    selectType(document.forms.prof_annu["edus[" + i + "][degreeid]"], document.forms.prof_annu["edu_" + i + "_tmp"].value);
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

function removeSearchName(i)
{
    $('#search_name_' + i).remove();
    updateNameDisplay();
}

function changeNameFlag(i)
{
    $('#flag_' + i).remove();
    var typeid = $('#search_name_' + i).find('select').val();
    var type   = $('#search_name_' + i).find('select :selected').text();
    if ($('[@name=sn_type_' + typeid + '_' + i + ']').val() > 0) {
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

function updateNameDisplay()
{
    var searchnames = '';
    for (var i = 0; i < 10; i++) {
        if ($('#search_name_' + i).find(':text').val()) {
            searchnames += $('#search_name_' + i).find('[name*=typeid]').val() + ';';
            searchnames += $('#search_name_' + i).find(':text').val() + ';;';
        }
    }
    Ajax.update_html(null, 'profile/ajax/buildnames/' + searchnames, function(data){
        var name = data.split(';');
        $('#public_name').html(name[0]);
        $('#private_name').html(name[0] + name[1]);
    });
}

// Nationalities {{{1

function delNationality(i)
{
    $('#nationalite' + i).hide().find('select').val('');
}

function addNationality()
{
    var i = 0;
    if ($('#nationalite2').find('select').val() == "") {
        i = 2;
    } else if ($('#nationalite3').find('select').val() == "") {
        i = 3;
    }
    if ((i == 2) || (i == 3)) {
        $('#nationalite' + i).show();
    }
}

// Networking {{{1

function addNetworking()
{
    var i = 0;
    var nws = 'networking_';
    while (document.getElementById(nws + i) != null) {
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
        + '      <select id="networking_type_' + i + '" name="networking[' + i + '][type]" onchange="javascript:updateNetworking(' + i + ');">';
    for (nw in nw_list) {
        if (namefirst == '') {
            namefirst = nw;
        }
        html += '  <option value="' + nw_list[nw] + '">' + nw + '</option>';
    }
    html += '</select>'
        + '      <input type="hidden" id="networking_name_' + i + '" name="networking[' + i + '][name]" value="' + namefirst + '"/>'
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
    var name = document.getElementById('networking_name_' + i);
    var type = document.getElementById('networking_type_' + i);
    if (type != null && name != null) {
        name.value = type.options[type.selectedIndex].text;
    }

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

function addressChanged(id)
{
    $('#addresses_' + id + '_cont').find('[name*=changed]').val("1");
}

function validGeoloc(id, geoloc)
{
    if (geoloc == 1) {
        $('#addresses_' + id + '_cont').find('[name*=text]').val($('#addresses_' + id + '_cont').find('[name*=geoloc]').val());
    }
    $('#addresses_' + id + '_cont').find('[name*=text]').removeClass('error');
    $('#addresses_' + id + '_cont').find('[name*=geoloc_choice]').val(geoloc);
    $('.addresses_' + id + '_geoloc').remove();
}

// {{{1 Phones

function addTel(prefid, prefname)
{
    var i = 0;
    var prefix  = prefid + '_';
    while (document.getElementById(prefix + i) != null) {
        i++;
    }
    $('#' + prefix + 'add').before('<div id="' + prefix + i + '" style="clear: both; padding-top: 4px; padding-bottom: 4px"></div>');
    Ajax.update_html(prefix + i, 'profile/ajax/tel/' + prefid + '/' + prefname + '/' + i);
}

function removeTel(id)
{
    $('#' + id).remove();
}

function addPhoneComment(id, pref)
{
    document.getElementById(id+'_comment').style.display = '';
    document.getElementById(id+'_addComment').style.display = 'none';
}

function removePhoneComment(id, pref)
{
    document.getElementById(id+'_comment').style.display = 'none';
    document.forms.prof_annu[pref+ '[comment]'].value = '';
    document.getElementById(id+'_addComment').style.display = '';
}

// Groups {{{1

function updateGroup(type)
{
    var val = document.forms.prof_annu[type + '_sel'].value;
    if (val == '0' || document.getElementById(type + '_' + val) != null) {
        document.getElementById(type + '_add').style.display = 'none';
    } else {
        document.getElementById(type + '_add').style.display = '';
    }
}

function removeGroup(cat, id)
{
    $('#' + cat + '_' + id).remove();
    updateGroup(cat);
}

function addGroup(cat)
{
    var cb   = document.forms.prof_annu[cat + '_sel'];
    var id   = cb.value;
    var text = cb.options[cb.selectedIndex].text;
    var html = '<tr id="' + cat + '_' + id + '">'
        + '  <td>'
        + '    <input type="hidden" name="' + cat + '[' + id + ']" value="' + text + '" />'
        + '  </td>'
        + '  <td>'
        + '    <div style="float: left; width: 70%">'
        +        text
        + '    </div>'
        + '    <a href="javascript:removeGroup(\'' + cat + '\', ' + id + ')">'
        + '      <img src="images/icons/cross.gif" alt="cross" title="Supprimer ce groupe" />'
        + '    </a>'
        + '  </td>'
        + '</tr>';
    $('#' + cat).after(html);
    updateGroup(cat);
}

function updateGroupSubLink(cb)
{
    var href = cb.value ? cb.value : "http://www.polytechnique.net";
    document.getElementById("groupesx_sub").href = href;
}

// Medals {{{1

function updateMedal()
{
    var val = document.forms.prof_annu['medal_sel'].value;
    if (val == '' || document.getElementById('medal_' + val) != null) {
        document.getElementById('medal_add').style.display = 'none';
    } else {
        document.getElementById('medal_add').style.display = '';
    }
}

function getMedalName(id)
{
    document.getElementById('medal_name_' + id).innerHTML = names[id];
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
    var id = document.forms.prof_annu['medal_sel'].value;
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
    document.getElementById(id + '_cont').style.display = 'none';
    if (document.forms.prof_annu[pref + '[new]'].value == '0') {
        document.getElementById(id + '_grayed').style.display = '';
        document.getElementById(id + '_grayed_name').innerHTML =
            document.forms.prof_annu[pref + "[name]"].value.replace('<', '&lt;');
    }
    document.forms.prof_annu[pref + "[removed]"].value = "1";
}

function restoreJob(id, pref)
{
    document.getElementById(id + '_cont').style.display = '';
    document.getElementById(id + '_grayed').style.display = 'none';
    document.forms.prof_annu[pref + "[removed]"].value = "0";
}

function updateJobSecteur(nb, id, pref, sel)
{
    var secteur = document.forms.prof_annu[pref + '[secteur]'].value;
    if (secteur == '') {
        secteur = '-1';
    }
    Ajax.update_html(id + '_ss_secteur', 'profile/ajax/secteur/' + nb + '/' + id + '/' + pref + '/' + secteur + '/' + sel);
}

function updateJobSousSecteur(nb, id, pref, sel)
{
    var ssecteur = document.forms.prof_annu[pref + '[ss_secteur]'].value;
    if (ssecteur == '') {
        ssecteur = '-1';
    }
    Ajax.update_html(id + '_sss_secteur', 'profile/ajax/ssecteur/' + nb + '/' + ssecteur + '/' + sel);
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
        updateSecteur('job_' + id, 'jobs[' + id + ']', '');
    };
}

function addJob()
{
    var i = 0;
    while (document.getElementById('job_' + i) != null) {
        ++i;
    }
    $.get(platal_baseurl + 'profile/ajax/job/' + i, makeAddJob(i));
}

function addEntreprise(id)
{
    $('.entreprise_' + id).toggle();
}

// {{{1 Skills

function updateSkill(cat)
{
    var val  = document.forms.prof_annu[cat + '_sel'].value;
    var show = true;
    if (val == '') {
        show = false;
    }
    if (document.getElementById(cat + '_' + val) != null) {
        show = false;
    }
    document.getElementById(cat + '_add').style.display = show ? '' : 'none';
}

function addSkill(cat)
{
    var sel  = document.forms.prof_annu[cat + '_sel'];
    var val  = sel.value;
    var text = sel.options[sel.selectedIndex].text;
    $.get(platal_baseurl + 'profile/ajax/skill/' + cat + '/' + val,
          function(data) {
          $('#' + cat).append(data);
          document.getElementById(cat + '_' + val + '_title').innerHTML = text;
          updateSkill(cat);
          });
}

function removeSkill(cat, id)
{
    $('#' + cat + '_' + id).remove();
    updateSkill(cat);
}

// Mentor {{{1

function updateCountry()
{
    var val = document.forms.prof_annu.countries_sel.value;
    var show = true;
    if (val == '' || val == '00') {
        show = false;
    }
    if (document.getElementById('countries_' + val) != null) {
        show = false;
    }
    document.getElementById('countries_add').style.display = show ? '' : 'none';
}

function addCountry()
{
    var cb   = document.forms.prof_annu.countries_sel;
    var val  = cb.value;
    var text = cb.options[cb.selectedIndex].text;
    var html = '<div id="countries_' + val + '" style="clear: both; margin-bottom: 0.7em">'
        + '  <a href="javascript:removeCountry(\'' + val + '\')" style="display: block; float:right">'
        + '    <img src="images/icons/cross.gif" alt="" title="Supprimer ce pays" />'
        + '  </a>'
        + '  <div style="float: left; width: 50%">' + text + '</div>'
        + '  <input type="hidden" name="countries[' + val + ']" value="' + text + '" />'
        + '</div>';
    $('#countries').append(html);
    updateCountry();
}

function removeCountry(id)
{
    $('#countries_' + id).remove();
    updateCountry();
}
function updateSSecteur()
{
    var s  = document.forms.prof_annu.secteur_sel.value;
    var ss = document.forms.prof_annu['jobs[-1][ss_secteur]'].value;
    var show = true;
    if (s == '' || ss == '') {
        show = false;
    }
    if (document.getElementById('secteurs_' + s + '_' + ss) != null) {
        show = false;
    }
    document.getElementById('secteurs_add').style.display = show ? 'block' : 'none';
}

function updateSecteur()
{
    var secteur = document.forms.prof_annu.secteur_sel.value;
    if (secteur == '') {
        secteur = '-1';
        document.getElementById('ss_secteur_sel').innerHTML = '';
        return;
    }
    $.get(platal_baseurl + 'profile/ajax/secteur/-1/0/0/' + secteur,
          function(data) {
              data = '<a href="javascript:addSecteur()" style="display: none; float: right" id="secteurs_add">'
                     +  '  <img src="images/icons/add.gif" alt="" title="Ajouter ce secteur" />'
                     +  '</a>' + data;
              document.getElementById('ss_secteur_sel').innerHTML = data;
              $(document.forms.prof_annu['jobs[-1][ss_secteur]']).change(updateSSecteur);
          });
}

function addSecteur()
{
    var scb = document.forms.prof_annu.secteur_sel;
    var s  = scb.value;
    var st = scb.options[scb.selectedIndex].text;

    var sscb = document.forms.prof_annu['jobs[-1][ss_secteur]'];
    var ss = sscb.value;
    var sst = sscb.options[sscb.selectedIndex].text;

    var html = '<div id="secteurs_' + s + '_' + ss + '" style="clear: both; margin-top: 0.5em" class="titre">'
        + '  <a href="javascript:removeSecteur(\'' + s + '\', \'' + ss + '\')" style="display: block; float: right">'
        + '    <img src="images/icons/cross.gif" alt="" title="Supprimer ce secteur" />'
        + '  </a>'
        + '  <input type="hidden" name="secteurs[' + s + '][' + ss + ']" value="' + sst + '" />'
        + '  ' + sst
        + '</div>';
    $('#secteurs').append(html);
    updateSSecteur();
}

function removeSecteur(s, ss)
{
    $('#secteurs_' + s + '_' + ss).remove();
    updateSSecteur();
}

function registerEnterpriseAutocomplete(id)
{
    $(".enterprise_name").each(
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
      }
    );

    $(".sector_name").each(
      function() {
        if (id == -1 || this.name == "jobs[" + id + "][sss_secteur_name]") {
            $(this).autocomplete(platal_baseurl + "search/autocomplete/sss_secteur",
                                 {
                                   selectOnly:1,
                                   field:this.name,
                                   matchSubset:0,
                                   width:$(this).width()
                                 });
        }
      }
    );
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
