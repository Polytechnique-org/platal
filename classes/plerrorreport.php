<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

    public function fromCSV(array $entry)
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
        $this->file = fopen(Platal::globals()->spoolroot . '/spool/tmp/site_errors', 'r');
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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
