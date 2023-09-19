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


// This file contains various utility functions.


// Returns the number of Unicode characters in a UTF-8 string
function utf8_strlen($str) {
    $len = strlen($str);
    $bytecount = 0;
    $ucount = 0;

    while ($bytecount < $len) {
        $val = ord($str[$bytecount]);
        if (($val&0xe0) == 0xc0)
            $bytecount += 2;
        elseif (($val&0xf0) == 0xe0)
            $bytecount += 3;
        elseif (($val&0xf8) == 0xf0)
            $bytecount += 4;
        else
            $bytecount += 1;
        ++$ucount;
    }
    return $ucount;
}

// Step forward through a UTF8 string a fixed number of Unicode characters.
// Returns the number of bytes processed.
function utf8_step($str, $ucount) {
    $len = strlen($str);
    $bytecount = 0;

    while ($bytecount < $len && $ucount > 0) {
        $val = ord($str[$bytecount]);
        if (($val&0xe0) == 0xc0)
            $bytecount += 2;
        elseif (($val&0xf0) == 0xe0)
            $bytecount += 3;
        elseif (($val&0xf8) == 0xf0)
            $bytecount += 4;
        else
            $bytecount += 1;
        --$ucount;
    }
    return $bytecount;
}


// Debug print
function preprint($var,$name=null) {
    if ($name)
        print "<pre>$name: ";
    else
        print '<pre>';

//    var_dump($var);
    print_r($var);
    print '</pre>';
}

?>