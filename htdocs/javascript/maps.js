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

// http://code.google.com/apis/maps/documentation/javascript/
// http://code.google.com/p/google-maps-utility-library-v3/wiki/Libraries

function map_initialize()
{
    var myOptions = {
        zoom: 1,
        center: new google.maps.LatLng(0, 0),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map($('#map_canvas').get(0), myOptions);

    $.xget(window.location.href, {ajax: true}, function(json_data) {
        var data = jQuery.parseJSON(json_data);
        var dots = data.data;
        var count = dots.length;
        var info_window = new google.maps.InfoWindow({});
        var parameters = ", '_blank', 'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=840,height=600'";
        var markers = [];

        for (var i = 0; i < count; ++i) {
            var latLng = new google.maps.LatLng(dots[i].latitude, dots[i].longitude);
            var color = promos_to_color(dots[i].promo);

            if (dots[i].hrpid.search(',') > -1) {
                var hrpids = dots[i].hrpid.split(',');
                var names = dots[i].name.split(',');
                var link_array = new Array();

                for (var j = 0; j < hrpids.length; ++j) {
                    link_array[j] = '<a href="profile/' + hrpids[j] + '" onclick="window.open(this.href' + parameters + '); return false;">' + names[j] + '</a>';
                }
                var link = link_array.join('<br />');
            } else {
                var link = '<a href="profile/' + dots[i].hrpid + '" onclick="window.open(this.href' + parameters + '); return false;">' + dots[i].name + '</a>';
            }

            var marker = new google.maps.Marker({
                'position': latLng,
                'map': map,
                'title': dots[i].name
            });
            marker.bindTo('icon', new ColoredIcon(color));
            marker.set('color', color);
            marker.set('html', link);
            google.maps.event.addListener(marker, 'click', function() {
                info_window.setContent(this.html);
                info_window.open(map, this);
            });
            markers.push(marker);
        }
        var mc = new MarkerClusterer(map, markers, {'averageCenter': true});
    });
}

function ColoredIcon(color)
{
    this.set('starcolor', null);
    this.set('color', color);
    this.set('icon', 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|' + color);
}

ColoredIcon.prototype = new google.maps.MVCObject();

var colors = new Array();
colors['red'] = 'ff0000';
colors['yellow'] = 'ffff00';
colors['blue'] = '0000ff';
colors['green'] = '00ff00';
colors['gray'] = '606060';

function promos_to_color(promos)
{
    var promos_array = promos.split(',');
    var length = promos_array.length;

    if (length == 1) {
        return colors[promo_to_color(promos)];
    }

    var color_array = new Array();
    for (var i = 0; i < length; ++i) {
        color_array[i] = promo_to_color(promos_array[i]);
    }

    return color_average(color_array);
}

function promo_to_color(promo)
{
    var main_education = promo.charAt(0);

    switch (main_education)
    {
      case 'X':
        var year_promo = promo.substr(1);
        if ((year_promo % 2) == 0) {
            return 'red';
        } else {
            return 'yellow';
        }
      case 'M':
        return 'green';
      case 'D':
        return 'blue';
      default:
        return 'gray';
    }
}

function color_average(color_array)
{
    var length = color_array.length;
    var rbg = new Array(0, 0, 0);

    for (var i = 0; i < length; ++i) {
        switch (color_array[i])
        {
          case 'red':
            rbg[0] += 1;
            break;
          case 'yellow':
            rbg[0] += 1;
            rbg[1] += 1;
            break;
          case 'blue':
            rbg[1] += 1;
            break;
          case 'green':
            rbg[2] += 1;
            break;
          case 'gray':
            rbg[0] += 0.5;
            rbg[1] += 0.5;
            rbg[2] += 0.5;
            break;
          default:
            break;
        }
    }

    var color_code = '';
    for (var i = 0; i < 3; ++i) {
        var color = Math.floor(rbg[i] / length * 255);
        var hexa = color.toString(16);
        if (hexa.length == 1) {
            hexa += '0';
        }
        color_code += hexa;
    }

    return color_code;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
