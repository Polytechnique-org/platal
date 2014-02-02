<?php
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
 **************************************************************************/

class PlErrorReport
{
    public $date;
    public $error;
    public $state;

    private function __construct($date, $error, $state = array())
    {
        $this->date  = $date;
        $this->error = "" . $error;
        $this->state = $state;
    }

    private function toCSV()
    {
        return array($this->date, $this->error, serialize($this->state));
    }

    public static function fromCSV(array $entry)
    {
        return new PlErrorReport($entry[0], $entry[1], unserialize($entry[2]));
    }

    public static function report($error)
    {
        $error = new PlErrorReport(date('Y-m-d G:i:s'), $error,
                                   array('Session' => $_SESSION,
                                         'Env' => $_REQUEST,
                                         'Post' => $_POST,
                                         'Get' => $_GET,
                                         'Cookie' => $_COOKIE,
                                         'Server' => $_SERVER));

        $file = fopen(Platal::globals()->spoolroot . '/spool/tmp/site_errors', 'a');
        fputcsv($file, $error->toCSV());
        fclose($file);
    }

    public static function iterate()
    {
        return new PlErrorReportIterator();
    }

    public static function feed(PlPage $page, PlUser $user)
    {
        $feed = new PlErrorReportFeed();
        return $feed->run($page, $user);
    }

    public static function clear()
    {
        @unlink(Platal::globals()->spoolroot . '/spool/tmp/site_errors');
    }
}

class PlErrorReportIterator implements PlIterator
{
    private $file;

    public function __construct()
    {
        $file = Platal::globals()->spoolroot . '/spool/tmp/site_errors';
        if (file_exists($file)) {
            $this->file = fopen($file, 'r');
        } else {
            $this->file = null;
        }
    }

    public function next()
    {
        if (!$this->file) {
            return null;
        }
        $entry = fgetcsv($this->file);
        if ($entry === false) {
            fclose($this->file);
            $this->file = null;
            return null;
        }
        $value = PlErrorReport::fromCSV($entry);
        return $value;
    }

    public function total()
    {
        return 0;
    }

    public function first()
    {
        return false;
    }

    public function last()
    {
        return false;
    }
}

class PlErrorReportFeed extends PlFeed
{
    public function __construct()
    {
        global $globals;
        parent::__construct($globals->core->sitename . ' :: News',
                            $globals->baseurl . '/site_errors',
                            'Erreurs d\'exécution',
                            $globals->baseurl . '/images/logo.png',
                            $globals->coreroot . '/templates/site_errors.feed.tpl');
    }

    protected function fetch(PlUser $user)
    {
        global $globals;
        $it   = PlErrorReport::iterate();
        $data = array();
        while ($row = $it->next()) {
            $title = explode("\n", $row->error);
            $title = $title[0];
            $line = array();
            $line['id'] = $row->date + count($data);
            $line['author'] = 'admin';
            $line['link']   = $globals->baseurl . '/site_errors';
            $line['data']   = $row;
            $line['title']  = $title;
            $line['last_modification'] = $row->date;
            $line['publication'] = $row->date;
            $data[] = $line;
        }
        return PlIteratorUtils::fromArray($data, 1, true);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
