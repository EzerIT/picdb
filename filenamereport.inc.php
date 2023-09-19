<?php
// Copyright (c) Claus Tondering.  E-mail: claus@ezer.dk.
//
// This code is distributed under an MIT License:
//
// Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
// associated documentation files (the "Software"), to deal in the Software without restriction,
// including without limitation the rights to use, copy, modify, merge, publish, distribute,
// sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all copies or
// substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING
// BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
// DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.


// This file contains files to handle the photo naming scheme:
// [PLACE#]_[PERIOD#]_[PICTURE#]_[RESOLUTION].[FILETYPE]

require_once 'database.inc.php';

// Find legal place numbers
$places = array();
$res = exec_sql("SELECT intval FROM {$db_prefix}categories,{$db_prefix}catval "
                . "WHERE {$db_prefix}categories.name='Place' "
                . "AND {$db_prefix}categories.id={$db_prefix}catval.category");
while ($row = mysqli_fetch_object($res))
    $places[] = $row->intval;

// Find legal culture numbers
$cultures = array();
$res = exec_sql("SELECT intval FROM {$db_prefix}categories,{$db_prefix}catval "
                . "WHERE {$db_prefix}categories.name='Culture' "
                . "AND {$db_prefix}categories.id={$db_prefix}catval.category");
while ($row = mysqli_fetch_object($res))
    $cultures[] = $row->intval;


// Find legal period numbers
$periods = array();
$res = exec_sql("SELECT intval FROM {$db_prefix}categories,{$db_prefix}catval "
                . "WHERE {$db_prefix}categories.name='Period' "
                . "AND {$db_prefix}categories.id={$db_prefix}catval.category");
while ($row = mysqli_fetch_object($res))
    $periods[] = $row->intval;

// Find legal resolution strings
$resolutions = array();
$res = exec_sql("SELECT stringval FROM {$db_prefix}categories,{$db_prefix}catval "
                . "WHERE {$db_prefix}categories.name='Resolution type' "
                . "AND {$db_prefix}categories.id={$db_prefix}catval.category");
while ($row = mysqli_fetch_object($res))
    $resolutions[] = $row->stringval;


class FileNameReport {
    public $directory;
    public $old_filename;
    public $new_filename;
    public $renamed;
    public $errors;
    public $fatal_error;

    public $categories;

    public $picno;
    private $place;
    private $culture;
    private $period;
    private $resolution;
    function __construct($dir, $f) {
        global $db_prefix;

        $this->directory = $dir;
        $this->old_filename = $f;
        $this->errors = array();
        $this->fatal_error = null;

        $ext = strrchr($f,'.');
        if ($ext!='.jpg' && $ext!='.JPG' && $ext!='.png' && $ext!='.PNG') {
            $this->fatal_error = 'Illegal file extension';
            return;
        }

        // Rename?
        if ($ext==".JPG") {
            $this->new_filename = substr_replace($f,'jpg',-3);
            $this->renamed = true;
        }
        elseif ($ext==".PNG") {
            $this->new_filename = substr_replace($f,'png',-3);
            $this->renamed = true;
        }
        else {
            $this->new_filename = $f;
            $this->renamed = false;
        }

        $head = substr($f, 0, -4);
        $parts = explode('_', $head);

        if (count($parts)!=4) {
            $this->fatal_error = 'File name does not contain 3 underscores';
            return;
        }

        $cat = $this->fillcategories();

        if (!is_numeric($this->picno)) {
            $this->fatal_error = "Picture number ($this->picno) is not numeric";
            return;
        }

        foreach ($this->place as $c) {
            if (!is_numeric($c)) {
                $this->fatal_error = "Place/culture number is not numeric";
                return;
            }
            if (!isset($places[$c]))
                $this->errors[] = "Illegal place number";
        }
        
        foreach ($this->culture as $c) {
            if (!isset($cultures[$c]))
                $this->errors[] = "Illegal culture number";
        }

        foreach ($this->period as $c) {
            if (!is_numeric($c)) {
                $this->fatal_error = "Period number is not numeric";
                return;
            }
            if (!isset($periods[$c]))
                $this->errors[] = "Illegal period number";
        }

        if (!isset($this->resolution) ||
            !isset($resolutions[$this->resolution]))
            $this->errors[] = "Illegal resolution type";
    }

    private function fillcategories() {
        // This code assumes that the file extension is always 4 characters long

        $this->place = array();
        $this->culture = array();
        $this->period = array();

        $parts = explode('_', substr($this->new_filename,0,-4));

        // $parts[0] contains the place if the value is <2200 or in the range 5000..5499, otherwise
        // the value specifies culture
        $subparts = explode('.', $parts[0]);
        foreach ($subparts as $sp) {
            if (is_numeric($sp)) {
                if ($sp<2200 || ($sp>=5000 && $sp<5500))
                    $this->place[] = $sp;
                else
                    $this->culture[] = $sp;
            }
        }
    
        // $parts[1] is the period
        $subparts = explode('.', $parts[1]);
        foreach ($subparts as $sp)
            $this->period[] = $sp;

        // $parts[2] is the picture number
        $this->picno = $parts[2];

        // $parts[3] is the resolution
        $this->resolution = $parts[3];


        $this->categories = array( 'Place' => $this->place,
                                   'Culture' => $this->culture,
                                   'Period' => $this->period,
                                   'Resolution type' => array($this->resolution) );
    }
}
