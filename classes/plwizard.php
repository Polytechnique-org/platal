<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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
 **************************************************************************/

/** A Wizard Page is a page of a wizard. It is a self-contained step which
 * handles both the creation and initialisation of the step (by using the
 * Wizard global state, if needed) and the processing of the action the
 * user made on it.
 */
interface PlWizardPage
{
    /** Build a new instance of the class
     * associated with the given wizard master.
     */
    public function __construct(PlWizard &$wiz);

    /** Return the name of the templace describing the page.
     */
    public function template();

    /** Prepare the page by assigning to it any useful value.
     */
    public function prepare(PlatalPage &$page);

    /** Process information resulting of the application of the page.
     * This function must return a clue indicating the next page to show.
     * This clue can be either a page id, a page number or a navigation
     * id (PlWizard::FIRST_PAGE, PlWizard::NEXT_PAGE, PlWizard::CURRENT_PAGE
     *  PlWizard::PREVIOUS_PAGE, PlWizard::LAST_PAGE).
     */
    public function process();
}

/** A PlWizard is a set of pages through which the user can navigate,
 * his action on a page determining which the next one will be.
 *
 * A Wizard can either a stateless wizard (which is only a set of
 * independent pages through which the user can easily navigate) or
 * stateful (a suite of steps where each step gives clue for the next
 * one).
 */
class PlWizard
{
    const FIRST_PAGE    = 'bt_first';
    const NEXT_PAGE     = 'bt_next';
    const CURRENT_PAGE  = 'bt_current';
    const PREVIOUS_PAGE = 'bt_previous';
    const LAST_PAGE     = 'bt_last';

    protected $name;
    protected $layout;
    protected $stateless;

    protected $pages;
    protected $titles;
    protected $lookup;

    public function __construct($name, $layout, $stateless = false)
    {
        $this->name      = 'wiz_' . $name;
        $this->layout    = $layout;
        $this->stateless = $stateless;
        $this->pages  = array();
        $this->lookup = array();
        $this->titles = array();
        if (!isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = array();
        }
        $_SESSION[$this->name . '_page']  = null;
        $_SESSION[$this->name . '_stack'] = array();
    }

    public function addPage($class, $title,$id = null)
    {
        if ($id != null) {
            $this->lookup[$id] = count($this->pages);
        }
        $this->pages[]  = $class;
        $this->titles[] = $title;
    }

    public function set($varname, $value)
    {
        $_SESSION[$this->name][$varname] = $value;
    }

    public function get($varname, $default = null)
    {
        return isset($_SESSION[$this->name][$varname]) ?
                    $_SESSION[$this->name][$varname] : $default;
    }

    public function v($varname, $default = "")
    {
        return $this->get($varname, $default);
    }

    public function i($varname, $default = 0)
    {
        return (int)$this->get($varname, $default);
    }

    public function clear($varname = null)
    {
        if (is_null($varname)) {
            $_SESSION[$this->name] = array();
        } else {
            unset($_SESSION[$this->name][$varname]);
        }
        $_SESSION[$this->name . '_page'] = null;
    }

    private function getPage($id)
    {
        $page = $this->pages[$id];
        return new $page($this);
    }

    public function apply(PlatalPage &$smarty, $baseurl, $pgid = null)
    {
        $curpage =& $_SESSION[$this->name . '_page'];

        // Process the previous page
        if (!is_null($curpage)) {
            $page = $this->getPage($curpage);
            $next = $page->process();
            $last = $curpage;
            switch ($next) {
              case PlWizard::FIRST_PAGE:
                $curpage = 0;
                break;
              case PlWizard::PREVIOUS_PAGE:
                if (!$this->stateless && count($_SESSION[$this->name . '_stack'])) {
                    $curpage = array_pop($_SESSION[$this->name . '_stack']);
                } elseif ($curpage && $this->stateless) {
                    $curpage--;
                } else {
                    $curpage = 0;
                }
                break;
              case PlWizard::NEXT_PAGE:
                if ($curpage < count($this->pages) - 1) {
                    $curpage++;
                }
                break;
              case PlWizard::LAST_PAGE:
                $curpage = count($this->pages) - 1;
                break;
              case PlWizard::CURRENT_PAGE: break; // don't change the page
              default:
                $curpage = is_numeric($next) ? $next : $this->lookup[$curpage];
                break;
            }
            if (!$this->stateless) {
                array_push($_SESSION[$this->name . '_stack'], $last);
            }
        }
        if ($this->stateless && (in_array($pgid, $this->lookup) || isset($this->pages[$pgid]))) {
            $curpage = $pgid;
        }
        if (is_null($curpage)) {
            $curpage = 0;
        }

        // Prepare the page
        $page = $this->getPage($curpage);
        $smarty->changeTpl($this->layout);
        $smarty->assign('pages', $this->titles);
        $smarty->assign('current', $curpage);
        $smarty->assign('stateless', $this->stateless);
        $smarty->assign('wiz_baseurl', $baseurl);
        $smarty->assign('tab_width', (int)(99 / count($this->pages)));
        $smarty->assign('wiz_page', $page->template());
        $page->prepare($smarty);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
