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

function map_initialize(latitude, longitude)
{
    var latlng = new google.maps.LatLng(latitude, longitude);
    var myOptions = {
        zoom: 1,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map($('#map_canvas').get(0), myOptions);

    $.xget('map/ajax', function(json_data) {
        var data = jQuery.parseJSON(json_data);
        var dots = data.data;
        var count = dots.length;
        var markers = [];

        for (var i = 0; i < count; ++i) {
            var latLng = new google.maps.LatLng(dots[i].latitude, dots[i].longitude);

            if (dots[i].hrpid.search(',') > -1) {
                var hrpids = dots[i].hrpid.split(',');
                var names = dots[i].name.split(',');
                var link_array = new Array();

                for (var j = 0; j < hrpids.length; ++j) {
                    link_array[j] = '<a href="profile/' + hrpids[j] + '">' + names[j] + '</a>';
                }
                var link = link_array.join('<br />');
            } else {
                var link = '<a href="profile/' + dots[i].hrpid + '">' + dots[i].name + '</a>';
            }

            var marker = new MarkerWithLabel({
                'position': latLng,
                'labelContent': link,
                'labelClass': 'marker_label'
            });
            markers.push(marker);
        }
        var mc = new MarkerClusterer(map, markers);
    });
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
