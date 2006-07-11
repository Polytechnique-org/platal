<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

class EventsModule extends PLModule
{
    function handlers()
    {
        return array(
            'events/submit'  => $this->make_hook('submit', AUTH_MDP),
        );
    }

    function handler_submit(&$page)
    {
        global $globals;
        $page->changeTpl('evenements.tpl');

        $titre      = Post::get('titre');
        $texte      = Post::get('texte');
        $promo_min  = Post::getInt('promo_min');
        $promo_max  = Post::getInt('promo_max');
        $peremption = Post::getInt('peremption');
        $valid_mesg = Post::get('valid_mesg');
        $action     = Post::get('action');

        $page->assign('titre', $titre);
        $page->assign('texte', $texte);
        $page->assign('promo_min', $promo_min);
        $page->assign('promo_max', $promo_max);
        $page->assign('peremption', $peremption);
        $page->assign('valid_mesg', $valid_mesg);
        $page->assign('action', strtolower($action));

        if ($action == 'Confirmer') {
            $texte = preg_replace('/((http|ftp)+(s)?:\/\/[^<>\s]+)/i',
                                  '<a href=\"\\0\">\\0</a>', $texte);
            $texte = preg_replace('/([^,\s]+@[^,\s]+)/i',
                                  '<a href=\"mailto:\\0\">\\0</a>', $texte);
            require_once 'validations.inc.php';
            $evtreq = new EvtReq($titre, $texte, $promo_min, $promo_max,
                                 $peremption, $valid_mesg, Session::getInt('uid'));
            $evtreq->submit();
            $page->assign('ok', true);
        }

        $select = '';
        for ($i = 1 ; $i < 30 ; $i++) {
            $time    = time() + 3600 * 24 * $i;
            $p_stamp = date('Ymd', $time);
            $year    = date('Y',   $time);
            $month   = date('m',   $time);
            $day     = date('d',   $time);

            $select .= "<option value=\"$p_stamp\"";
            if ($p_stamp == strtr($peremption, array("-" => ""))) {
                $select .= " selected='selected'";
            }
            $select .= "> $day / $month / $year</option>\n";
        }
        $page->assign('select',$select);

        return PL_OK;
    }
}

?>
