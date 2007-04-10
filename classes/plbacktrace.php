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
 ***************************************************************************/

/** Ligth debugging tool to generate execution backtrace
 */
class PlBacktrace
{
    static public $bt = array();

    public $traces    = array();
    public $totaltime = 0.0;
    public $error     = false;

    function __construct($name, array $init = array(), $sizef = 'rows', $timef = 'exectime', $errorf = 'error')
    {
        PlBacktrace::$bt[$name] = $this;
        foreach ($init as &$entry) {
            $this->add($entry, $sizef, $timef, $errorf);
        }
    }

    private function fixCharset($action)
    {
        return is_utf8($action) ? $action : utf8_encode($action);
    }

    private function add(array &$entry, $sizef = 'rows', $timef = 'exectime', $errorf = 'error')
    {
        $trace = array();
        $trace['action'] = $this->fixCharset($entry['action']);
        unset($entry['action']);
        $trace['exectime'] = @$entry[$timef];
        $this->totaltime += $trace['exectime'];
        unset($entry[$timef]);
        $trace['rows'] = @$entry[$sizef];
        unset($entry[$sizef]);
        $trace['error'] = @$entry[$errorf];
        unset($entry[$errorf]);
        if ($trace['error']) {
            $this->error = true;
        }
        $trace['data'] = array($entry);
        $this->traces[] =& $trace;
    }

    public function newEvent($action, $rows = 0, $error = null, array $userdata = array())
    {
        $trace = array('action' => $this->fixCharset($action), 'time' => 0);
        $this->traces[] =& $trace;
        $this->update($rows, $error, $userdata);
    }

    public function start($action)
    {
        $this->traces[] =  array('action' => $this->fixCharset($action), 'starttime' => microtime(true));;
    }

    public function stop($rows = 0, $error = null, array $userdata = array())
    {
        $time = microtime(true);
        if (!$this->traces) {
            return;
        }
        $trace =& $this->traces[count($this->traces) - 1];
        $trace['exectime'] = $time - $trace['starttime'];
        unset($trace['starttime']);
        $this->totaltime += $trace['exectime'];
        $this->update($rows, $error, $userdata);
    }

    public function update($rows = 0, $error = null, array $userdata = array())
    {
        $trace =& $this->traces[count($this->traces) - 1];
        $trace['rows']  = $rows;
        $trace['error'] = $error;
        $trace['data']  = $userdata;
        if ($trace['error']) {
            $this->error = true;
        }
    }

    public static function clean()
    {
        foreach (PlBacktrace::$bt as $name=>&$entry) {
            if (!$entry->traces) {
                unset(PlBacktrace::$bt[$name]);
            }
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
