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

// {{{ class EvtReq

class EvtReq extends Validate
{
    // {{{ properties

    public $evtid;
    public $titre;
    public $texte;
    public $pmin;
    public $pmax;
    public $expiration;
    public $comment;

    public $imgtype;
    public $imgx;
    public $imgy;
    public $img;

    // }}}
    // {{{ constructor

    public function __construct($_titre, $_texte, $_pmin, $_pmax, $_expiration, $_comment, User $_user, PlUpload $upload = null)
    {
        parent::__construct($_user, false, 'evts');
        $this->titre      = $_titre;
        $this->texte      = $_texte;
        $this->pmin       = $_pmin;
        $this->pmax       = $_pmax;
        $this->expiration = $_expiration;
        $this->comment    = $_comment;
        if ($upload) {
            $this->readImage($upload);
        }
    }

    // }}}
    // {{{ function readImage()

    private function readImage(PlUpload $upload)
    {
        if ($upload->exists() && $upload->isType('image')) {
            list($this->imgx, $this->imgy, $this->imgtype) = $upload->imageInfo();
            $this->img = $upload->getContents();
            $upload->rm();
        }
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.evts.tpl';
    }

    // }}}
    // {{{ functon editor()

    public function editor()
    {
        return 'include/form.valid.edit-evts.tpl';
    }

    // }}}
    // {{{ function handle_editor()

    protected function handle_editor()
    {
        $this->titre      = Env::v('titre');
        $this->texte      = Env::v('texte');
        $this->pmin       = Env::i('promo_min');
        $this->pmax       = Env::i('promo_max');
        $this->expiration = Env::v('expiration');
        if (@$_FILES['image']['tmp_name']) {
            $upload = PlUpload::get($_FILES['image'], S::user()->login(), 'event');
            if (!$upload) {
                $this->trigError("Impossible de télécharger le fichier");
            } elseif (!$upload->isType('image')) {
                $page->trigError('Le fichier n\'est pas une image valide au format JPEG, GIF ou PNG');
                $upload->rm();
            } elseif (!$upload->resizeImage(200, 300, 100, 100, 32284)) {
                $page->trigError('Impossible de retraiter l\'image');
            } else {
                $this->readImage($upload);
            }
        }
        return true;
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org/EVENEMENTS] Proposition d'événement";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        $you_have = ($this->formal ? 'vous aviez' : 'tu avais');
        if ($isok) {
            return "  L'annonce que $you_have proposée ({$this->titre}) vient d'être validée.";
        } else {
            return "  L'annonce que $you_have proposée ({$this->titre}) a été refusée.";
        }
    }

    // }}}
    // {{{ function _mail_ps

    protected function _mail_ps($isok)
    {
        $maildate = make_datetime($this->expiration)->format('d / m / Y');
        return "\nPS : pour rappel, en voici le contenu :"
            . "\n--------------------------------------------------------------------------\n"
            . $this->titre
            . "\n--------------------------------------------------------------------------\n"
            . $this->texte . "\n\nPour les promotions de " . $this->pmin . " à " . $this->pmax . ", avec expiration le " . $maildate . ".\n";
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        /* TODO: refines this filter on promotions by using userfilter. */
        if (XDB::execute("INSERT INTO  announces
                         SET  uid = {?}, creation_date=NOW(), titre={?}, texte={?},
                              expiration={?}, promo_min={?}, promo_max={?}, flags=CONCAT(flags,',valide,wiki')",
                $this->user->id(), $this->titre, $this->texte,
                $this->expiration, $this->pmin, $this->pmax)) {
            $eid = XDB::insertId();
            if ($this->img) {
                XDB::execute("INSERT INTO announce_photos
                                      SET eid = {?}, attachmime = {?}, x = {?}, y = {?}, attach = {?}",
                             XDB::insertId(), $this->imgtype, $this->imgx, $this->imgy, $this->img);
            }
            global $globals;
            if ($globals->banana->event_forum) {
                require_once 'banana/forum.inc.php';
                $banana = new ForumsBanana($this->user);
                $post = $banana->post($globals->banana->event_forum,
                                      $globals->banana->event_reply,
                                      $this->titre, MiniWiki::wikiToText($this->texte, false, 0, 80));
                if ($post != -1) {
                    XDB::execute("UPDATE  announces
                                     SET  creation_date = creation_date, post_id = {?}
                                   WHERE  id = {?}", $post, $eid);
                }
            }
            return true;
        }
        return false;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
