<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

/** This module handles the "N N-10" operation, locally named "Delta10".
 */
class DeltaTenModule extends PLModule
{
    function handlers()
    {
        return array(
            'deltaten/search'   => $this->make_hook('index', AUTH_COOKIE, 'user'),
            'deltaten'          => $this->make_hook('index', AUTH_COOKIE, 'user'),
        );
    }

    /** Check whether a given user is in a "DeltaTen" promo.
     * This is based on "has a profile, is an X, and is in a young enough promo".
     */
    protected function isDeltaTenEnabled(User $user, $role)
    {
        if (!$user->hasProfile()) {
            return false;
        }
        return $user->profile()->isDeltaTenEnabled($role);
    }

    function handler_index($page, $action='', $subaction='')
    {
        global $globals;
        if (!$this->isDeltaTenEnabled(S::user(), Profile::DELTATEN_YOUNG)) {
            $page->killError("Ta promotion ne participe pas à l'opération N N-10.");
        }

        if ($this->isDeltaTenEnabled(S::user(), Profile::DELTATEN_OLD)) {
            $profile = S::user()->profile();
            if ($profile->getDeltatenMessage()) {
                $page->trigSuccess("Tu participes bien à l'opération N N-10 en tant qu'ancien.");
            } else {
                $page->trigWarning("Tu ne participes pas encore à l'opération N N-10 en tant qu'ancien.");
            }
        }
        $page->setTitle("Opération N N-10");
        $page->assign('deltaten_promo_old', S::user()->profile()->yearpromo() - 10);
        $wp = new PlWikiPage('Docs.Deltaten');
        $wp->buildCache();

        require_once 'ufbuilder.inc.php';
        $ufb = new UFB_DeltaTenSearch();
        $page->addJsLink('search.js');
        if (!$ufb->isEmpty()) {
            require_once 'userset.inc.php';
            $ufc = $ufb->getUFC();
            if (!$ufc instanceof PFC_And) {
                $ufc = new PFC_And($ufc);
            }
            $ufc->addChild(new UFC_DeltaTen());
            $ufc->addChild(new UFC_Promo('=', UserFilter::GRADE_ING, S::user()->profile()->yearpromo() - 10));

            $set = new ProfileSet($ufc);
            $set->addMod('minifiche', 'Opération N N-10');
            $set->apply('deltaten/search', $page, $action, $subaction);
            $nb_tot = $set->count();
            if ($nb_tot > $globals->search->private_max) {
                $page->assign('formulaire', 1);
                $page->trigError('Recherche trop générale.');
                $page->assign('plset_count', 0);
            } else if ($nb_tot == 0) {
                $page->assign('formulaire', 1);
                $page->trigError("Il n'existe personne correspondant à ces critères dans la base.");
            }
        }
        $page->changeTpl('deltaten/index.tpl');
    }
}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
