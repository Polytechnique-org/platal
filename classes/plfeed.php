<?php
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

/** A feed article.
 */
class PlFeedArticle
{
    public $id;

    public $author;
    public $author_email;

    public $publication;
    public $last_modification;

    public $title;
    public $link;

    public $template;

    public function __construct($tpl, array& $data)
    {
        $this->template = $tpl;
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}

/** An abstract feed. A feed is nothing more than a set of article.
 */
abstract class PlFeed implements PlIterator
{
    public $title;
    public $link;
    public $description;

    public $img_link;

    private $article_tpl;
    private $iterator;

    public function __construct($title, $link, $desc, $img_link,
                                $article_tpl) {
        $this->title        = $title;
        $this->link         = $link;
        $this->description  = $desc;
        $this->img_link     = $img_link;
        $this->article_tpl  = $article_tpl;
    }

    /** Fetch the feed for the given user.
     */
    abstract protected function fetch($user);

    public function next()
    {
        $data = $this->iterator->next();
        if (!empty($data)) {
            return new PlFeedArticle($this->article_tpl, $data);
        }
        return null;
    }

    public function total()
    {
        return $this->iterator->total();
    }

    public function first()
    {
        return $this->iterator->first();
    }

    public function last()
    {
        return $this->iterator->last();
    }

    public function run(PlPage& $page, $login, $token, $require_auth = true, $type = 'rss2')
    {
        $user = Platal::session()->tokenAuth($login, $token);
        if (empty($user)) {
            if ($require_auth) {
                return PL_FORBIDDEN;
            } else {
                $user = null;
            }
        }

        $page->assign('rss_hash', $token);
        header('Content-Type: application/rss+xml; charset=utf8');
        $this->iterator = $this->fetch($user);
        $page->coreTpl('feed.' . $type . '.tpl', NO_SKIN);
        $page->assign_by_ref('feed', $this);
        $page->run();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
