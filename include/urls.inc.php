<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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


/****************************************************************************
 * URL-related functions
 ***************************************************************************/


/** Helper function: replace the query of a provided URL.
 *
 * Also strips authentication-related components, as well as the URL fragment (#foo)
 */
function rebuild_url($base_url, $query)
{
    $url_parts = parse_url($base_url);

    // See https://tools.ietf.org/html/rfc3986#section-3 for a list of
    // available URL components

    $full_url = (
        $url_parts['scheme']
        . '://'
        // We ignore 'user' and 'pass'
        . $url_parts['host']
        . (isset($url_parts['port']) ? ':' . $url_parts['port'] : '')
        . $url_parts['path']
        . '?' . $query  // Replace the old query
        // Ignore the fragment
    );
    return $full_url;
}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
