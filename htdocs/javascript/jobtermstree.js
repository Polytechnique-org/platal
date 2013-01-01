/***************************************************************************
 *  Copyright (C) 2003-2013 Polytechnique.org                              *
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

/**
 * Creates a job terms tree.
 * @param domElement the jQuery selector string that defines the DOM element
 * which should contain the tree.
 * @param platalpage the base page to query for branches
 * @param treeid an id unique for the tree in this page that will be used in
 * clickFunc
 * @param clickFunc name of a javascript function that will be called when a
 * term is clicked. The three params of this function will be : treeid, the
 * id of the job term clicked, and the full name of the job term clicked.
 * @param text_filter a string that is tokenized to filter jobtermss shown
 * in the tree: only terms that contain all the tokens are shown with their
 * parents.
 */
function createJobTermsTree(domElement, platalpage, treeid, clickFunc, text_filter)
{
    $(domElement).jstree({
        "core" : {"strings":{"loading":"Chargement ..."}},
        "plugins" : ["themes","json_data"],
        "themes" : { "url" : $.plURL("css/jstree.css") },
        "json_data" : { "ajax" : {
            "url" : $.plURL(platalpage),
            "data" : function(nod) {
                var jtid = 0;
                if (nod != -1) {
                    jtid = nod.attr("id").replace(/^.*_([0-9]+)$/, "$1");
                }
                return { "jtid" : jtid, "treeid" : treeid, "attrfunc" : clickFunc, "text_filter": text_filter }
            }
        }} });
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:

