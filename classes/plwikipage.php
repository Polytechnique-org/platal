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

/** This namespace groups all the methods relative to the integration
 * of PmWiki into plat/al.
 *
 * pagename: A page name is NameSpace.Page and must be acceed trough
 *           NameSpace/Page.
 */
class PlWikiPage
{
    static private $permLevels = array('public' => 'Public',
                                       'logged' => 'Connecté',
                                       'mdp'    => 'Authentifié',
                                       'admin'  => 'Admin');
    static private $defaulPerms = array('logged', 'admin');

    public $name;
    public $path;

    private $perms = null;

    /** Build a new page from a PmWiki page name (ie NameSpace.Page).
     */
    public function __construct($n)
    {
        if (!is_utf8($n)) {
            $n = utf8_encode($n);
        }
        $this->name = $n;
        $this->path = str_replace('.', '/', $n);
    }

    /** Return the filename.
     */
    public function filename()
    {
        return self::workDir() . '/' . $this->name;
    }

    /** Return the filename for the cache file.
     */
    public function cacheFilename()
    {
        return self::workDir() . '/cache_' . $this->name . '.tpl';
    }

    /** Fetch the permissions.
     */
    private function fetchPerms()
    {
        if (!is_null($this->perms)) {
            return;
        }
        $file = $this->filename();
        if (!file_exists($file)) {
            $this->perms = self::$defaulPerms;
            return;
        }
        $lines = explode("\n", file_get_contents($file));
        foreach ($lines as $line) {
            @list($k, $v) = explode('=', $line, 2);
            if ($k == 'platal_perms') {
                $this->perms = explode(':', $v);
                return;
            }
        }
        $this->perms = self::$defaulPerms;
    }

    /** Return read perms.
     */
    public function readPerms()
    {
        if (is_null($this->perms)) {
            $this->fetchPerms();
        }
        return $this->perms[0];
    }

    /** Check if the user can read the page.
     */
    public function canRead()
    {
        return self::havePerms($this->readPerms());
    }

    /** Return write perms.
     */
    public function writePerms()
    {
        if (is_null($this->perms)) {
            $this->fetchPerms();
        }
        return $this->perms[1];
    }

    /** Check if the user can write the page.
     */
    public function canWrite()
    {
        return self::havePerms($this->writePerms());
    }

    /** Set the permission level for the page.
     */
    public function setPerms($read, $write)
    {
        $file = $this->filename();
        if (!file_exists($file)) {
            return false;
        }

        $p = $read . ':' . $write;
        $lines = explode("\n", file_get_contents($file));
        foreach ($lines as $i => $line) {
            list($k, $v) = explode('=', $line, 2);
            if ($k == 'platal_perms') {
                unset($lines[$i]);
                break;
            }
        }
        array_splice($lines, 1, 0, array('platal_perms=' . $p));
        file_put_contents($file, join("\n", $lines));
        $this->perms = array($read, $write);
        return true;
    }


    /** Check permission for RSS feed.
     */
    public function prepareFeed()
    {
        if ($this->canRead()) {
            return;
        }
        $uid = Platal::session()->tokenAuth(Get::v('user'), Get::v('hash'));
        if ($this->canRead()) {
            return;
        }
        exit;
    }

    /** Apply the read permissions for the current page.
     */
    public function applyReadPerms()
    {
        if ($this->canRead()) {
            return;
        }
        $this->applyPerms($this->readPerms());
    }

    /** Apply the write permissions for the current page.
     */
    public function applyWritePerms()
    {
        if ($this->canWrite()) {
            return;
        }
        $this->applyPerms($this->writePerms());
    }

    /** Build the cache for the page.
     */
    public function buildCache()
    {
        global $globals;
        if (is_file($this->cacheFilename())) {
            return;
        }
        system('wget --no-check-certificate ' . escapeshellarg($globals->baseurl . '/' . $this->path) . ' -O /dev/null');
    }

    /** Remove the page.
     */
    public function delete()
    {
        $file  = $this->filename();
        $cache = $this->cacheFilename();
        if (is_file($cache)) {
            unlink($cache);
        }
        if (!is_file($file)) {
            return false;
        }
        unlink($file);
        return true;
    }

    /** Return the wiki storage dir.
     */
    public static function workDir()
    {
        global $globals;
        return $globals->spoolroot . '/spool/wiki.d';
    }

    /** Clear wiki cache.
     */
    public static function clearCache()
    {
        system('rm -f ' . self::workDir() . '/cache_*');
    }


    /** Search links in the a page
     */
    private static function findLinks($line, $groupname)
    {
        $links = array();
        if (preg_match_all('@\[\[([^~][^\]\|\?#]*)((\?|#)[^\]\|]+)?(\\|[^\]]+)?\]\]@', $line, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $j => $link) if (!preg_match('@http://@', $link[0])) {
                $mylink = str_replace('/','.',trim($link[0]));
                $sup = trim(substr($matches[2][$j][0],1));
                $alt = trim(substr($matches[4][$j][0],1));
                $newlink = str_replace(' ','',ucwords($mylink));
                if (strpos($newlink,'.') === false) {
                    $newlink = $groupname.'.'.$newlink;
                }
                if (!$alt && $mylink != $newlink) {
                    $alt = trim($link[0]);
                }
                $links[] = array(
                  'pos' => $matches[0][$j][1],
                  'size' => strlen($matches[0][$j][0]),
                  'href' => $newlink,
                  'sup' => $sup,
                  'alt' => $alt,
                  'group' => substr($mylink, 0, strpos($mylink, '.')));
            }
        }
        return $links;
    }

    public function rename($newname, $changeLinks = true)
    {
        $newpage = new PlWikiPage(str_replace('/', '.', $newname));

        list($groupname, ) = explode('.', $this->name);
        list($newgroupname, ) = explode('.', $newpage->name);

        $file    = $this->filename();
        $newfile = $newpage->filename();
        if (!is_file($file)) {
            // old page doesn't exist
            return false;
        }
        if (!rename($file, $newfile)) {
            // impossible to renama page
            return false;
        }

        if (!$changeLinks) {
            $this->name = $newpage->name;
            $this->path = $newpage->path;
            return true;
        }

        $changedLinks = 0;
        // change name inside this folder and ingroup links if changing group
        $lines = explode("\n", file_get_contents($newfile));
        $changed = false;
        foreach ($lines as $i => $line) {
            list($k, $v) = explode('=', $line, 2);
            if ($k == 'name' && $v == $pagename_dots) {
                $lines[$i] = 'name=' . $newpage->name;
                $changed = true;
            } else if ($groupname != $newgroupname) {
                $links =  self::findLinks($line, $groupname);
                $newline = '';
                $last = 0;
                foreach ($links as $link) {
                    if ($link['group'] == $groupname) {
                        $newline .= substr($line, $last, $link['pos']);
                        $newline .= '[['.$link['href'].$link['sup'].($link['alt']?(' |'.$link['alt']):'').']]';
                        $last = $link['pos']+$link['size'];
                        $changedLinks++;
                    }
                }
                if ($last != 0) {
                    $newline .= substr($line, $last);
                    $lines[$i] = $newline;
                    $changed = true;
                }
            }
        }
        file_put_contents($newfile, join("\n", $lines));

        // change wiki links in all wiki pages
        $endname = substr($pagename_dots, strpos($this->name, '.') + 1);
        $pages = array();
        exec('grep ' . $endname . '  ' . self::workDir() . '/* -sc', $pages);
        foreach($pages as $line) {
            if (preg_match('%/([^/:]+):([0-9]+)$%', $line, $vals) && $vals[2] > 0) {
                $inpage = $vals[1];
                $lines = explode("\n", file_get_contents(self::workDir().'/'.$inpage));
                $changed = false;
                // find all wiki links in page and change if linked to this page
                foreach ($lines as $i => $line) {
                    $links = self::findLinks($line, substr($inpage, 0, strpos($inpage, '.')));
                    $newline = '';
                    $last = 0;
                    foreach ($links as $link) {
                        if ($link['href'] == $pagename_dots) {
                            $newline .= substr($line, $last, $link['pos']);
                            $newline .= '[[' . $newname_dots . $link['sup'] . ($link['alt'] ? (' |' . $link['alt']) : '') . ']]';
                            $last = $link['pos'] + $link['size'];
                            $changedLinks++;
                        }
                    }
                    if ($last != 0) {
                        $newline .= substr($line, $last);
                        $lines[$i] = $newline;
                        $changed = true;
                    }
                }
                if ($changed) {
                    file_put_contents(self::workDir() . '/' . $inpage, join("\n", $lines));
                }
            }
        }
        if ($changedLinks > 0) {
            return $changedLinks;
        }
        $this->name = $newpage->name;
        $this->path = $newpage->path;
        return true;
    }

    /** Return the authentication level translation table.
     */
    public static function permOptions()
    {
        return array('public' => 'Public',
                     'logged' => 'Connecté',
                     'mdp'    => 'Authentifié',
                     'admin'  => 'Admin');
    }

    /** Check permissions.
     */
    public static function havePerms($perm)
    {
        switch ($perm) {
          case 'public':
            return true;
          case 'logged':
          case 'mdp':
            return S::logged();
          case 'admin':
            return S::has_perms();
          default:
            return false;
        }
    }

    /** Apply permissions.
     */
    public static function applyPerms($perm)
    {
        switch ($perm) {
          case 'public':
            return;
          case 'logged':
            Platal::session()->start(AUTH_PUBLIC + 1);
            return;
          default:
            Platal::session()->start(Platal::session()->sureLevel());
            return;
        }
    }

    /** Return the current page.
     */
    public static function currentPage()
    {
        if (!Get::v('n')) {
            return null;
        }

        $words = explode('/', trim(Get::v('n'), '/'));
        if (count($words) == 2) {
            return new PlWikiPage(join('.', $words));
        }

        // We are on NameSpace.Page, redirect to NameSpace/Page
        array_unshift($words, $words[0]);
        $b = array_pop($words);
        $a = array_pop($words);

        pl_redirect($a . '/' . $b);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
