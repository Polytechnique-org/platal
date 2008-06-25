/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

// Page initialization

function wizPage_onLoad(id)
{
    switch (id) {
      case 'general':
        fillType(document.forms.prof_annu['appli1[type]'], document.forms.prof_annu['appli1[id]'].selectedIndex-1);
        selectType(document.forms.prof_annu['appli1[type]'], document.forms.prof_annu['appli1_tmp'].value);
        fillType(document.forms.prof_annu['appli2[type]'], document.forms.prof_annu['appli2[id]'].selectedIndex-1);
        selectType(document.forms.prof_annu['appli2[type]'], document.forms.prof_annu['appli2_tmp'].value);
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
        }
        registerEnterpriseAutocomplete(-1);
        break;
    }
}

var applisType;
var applisTypeAll;

// General

var subgrades;
var names;
function fillType(selectCtrl, appli, fill)
{
    var i;
    var i0=0;

    for (i = selectCtrl.options.length; i >=0; i--) {
        selectCtrl.options[i] = null;
    }

    if (fill || appli <0) {
        selectCtrl.options[0] = new Option(' ');
        i0=1;
    }
    if (appli>=0)
        for (i=0; i < applisType[appli].length; i++)
            selectCtrl.options[i0+i] = new Option(applisType[appli][i]);
    else if (fill)
        for (i=0; i < applisTypeAll.length; i++)
            selectCtrl.options[i0+i] = new Option(applisTypeAll[i]);
}


function selectType(selectCtrl, type)
{
    for (i = 0; i < selectCtrl.options.length; i++) {
        if (selectCtrl.options[i].text == type)
            selectCtrl.selectedIndex=i;
    }
}

function addSearchName()
{
  var i = 0;
  while (document.getElementById('search_name_' + i) != null) {
      i++;
  }
  $('#add_search_name').before('<div id="search_name_' + i + '" style="padding:2px" class="center"></div>');
  Ajax.update_html('search_name_' + i, 'profile/ajax/searchname/' + i,function(){
    $('#search_name_'+i+' input')[1].focus();
  });
}

function removeSearchName(i)
{
  if (document.getElementById('search_name_'+i+'_new') != null) {
    $('#search_name_'+i).remove();
  } else {
    removeObject('search_name_'+i, 'search_name['+i+']');
  }
}

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

// Addresses

function removeObject(id, pref)
{
    document.getElementById(id).style.display = "none";
    document.forms.prof_annu[pref + "[removed]"].value = "1";
}

function restoreObject(id, pref)
{
    document.getElementById(id).style.display = '';
    document.forms.prof_annu[pref + "[removed]"].value = "0";
}

function getAddressElement(adrid, adelement)
{
    return document.forms.prof_annu["addresses[" + adrid + "][" + adelement + "]"];
}

function checkCurrentAddress(newCurrent)
{
    var hasCurrent = false;
    var i = 0;
    while (getAddressElement(i, 'pub') != null) {
        var radio = getAddressElement(i, 'current');
        var removed = getAddressElement(i, 'removed');
        if (removed.value == "1" && radio.checked) {
            radio.checked = false;
        } else if (radio.checked && radio != newCurrent) {
            radio.checked = false;
        } else if (radio.checked) {
            hasCurrent = true;
        }
        i++;
    }
    if (!hasCurrent) {
        i = 0;
        while (getAddressElement(i, 'pub') != null) {
            var radio = getAddressElement(i, 'current');
            var removed = getAddressElement(i, 'removed');
            if (removed.value != "1") {
                radio.checked= true;
                return;
            }
            i++;
        }
    }
}

function removeAddress(id, pref)
{
    removeObject(id, pref);
    checkCurrentAddress(null);
    if (document.forms.prof_annu[pref + '[datemaj]'].value != '') {
        document.getElementById(id + '_grayed').style.display = '';
    }
}

function restoreAddress(id, pref)
{
    document.getElementById(id +  '_grayed').style.display = 'none';
    checkCurrentAddress(null);
    restoreObject(id, pref);
}

function addAddress()
{
    var i = 0;
    while (getAddressElement(i, 'pub') != null) {
        i++;
    }
    $("#add_adr").before('<div id="addresses_' + i + '_cont"></div>');
    Ajax.update_html('addresses_' + i + '_cont', 'profile/ajax/address/' + i, checkCurrentAddress);
}

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

// Geoloc

function validGeoloc(id, pref)
{
    document.getElementById(id + '_geoloc').style.display = 'none';
    document.getElementById(id + '_geoloc_error').style.display = 'none';
    document.getElementById(id + '_geoloc_valid').style.display = 'none';
    document.forms.prof_annu[pref + "[parsevalid]"].value = "1";
    document.forms.prof_annu[pref + "[text]"].value = document.forms.prof_annu[pref + "[geoloc]"].value;
    document.forms.prof_annu[pref + "[cityid]"].value = document.forms.prof_annu[pref + "[geoloc_cityid]"].value;
    attachEvent(document.forms.prof_annu[pref + "[text]"], "click",
                function() { document.forms.prof_annu[pref + "[text]"].blur(); });
    document.forms.prof_annu[pref + "[text]"].className = '';
}

function validAddress(id, pref)
{
    document.getElementById(id + '_geoloc').style.display = 'none';
    document.getElementById(id + '_geoloc_error').style.display = 'none';
    document.getElementById(id + '_geoloc_valid').style.display = 'none';
    document.forms.prof_annu[pref + "[parsevalid]"].value = "1";
    attachEvent(document.forms.prof_annu[pref + "[text]"], "click",
                function() { document.forms.prof_annu[pref + "[text]"].blur(); });
    document.forms.prof_annu[pref + "[text]"].className = '';
}


// Groups

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


// Medals

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


// Jobs

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
    Ajax.update_html(id + '_ss_secteur', 'profile/ajax/secteur/' +nb + '/' + secteur + '/' + sel);
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


// Skills

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


// Mentor

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
    $.get(platal_baseurl + 'profile/ajax/secteur/-1/' + secteur,
          function(data) {
          data = '<a href="javascript:addSecteur()" style="display: none; float: right" id="secteurs_add">'
          +  '  <img src="images/icons/add.gif" alt="" title="Ajouter ce secteur" />'
          +  '</a>' + data;
          document.getElementById('ss_secteur_sel').innerHTML = data;
          attachEvent(document.forms.prof_annu['jobs[-1][ss_secteur]'], 'change', updateSSecteur);
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
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
