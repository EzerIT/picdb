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

require_once 'database.inc';
require_once 'html.inc';
require_once 'dataexception.inc';
require_once 'img.inc';

try {
    header("Content-Type: application/json");


    if (!isset($_GET['picno']) || !is_numeric($_GET['picno']))
        throw new DataException("Illegal picture number: '$picno'");

    $picno = my_escape_string($_GET['picno']);

    $res = exec_sql("SELECT * FROM {$db_prefix}photos WHERE pic_no=$picno AND published");

    $row = mysqli_fetch_object($res);

    if (!$row)
        throw new DataException("No picture with picture number: '$picno'");

    $q = new stdClass();

    $filename = "$dirname_600/$row->filename";

    $handle = fopen($filename, 'r');

    if (!$handle)
        throw new DataException("Cannot open file '$filename'");

    $buf = fread($handle, filesize($filename));
    fclose($handle);

    $q->img = base64_encode($buf);

    if (substr($row->description,0,2)=='<p')
        $longdesc = $row->description;
    else 
        $longdesc = shtml('p',$row->description); // Make sure $longdesc is embedded in <p>..</p>

    $longdesc = replace_links_json($longdesc);

    $res2 = exec_sql("SELECT cat.name as catname, cv.name as cvname FROM {$db_prefix}piccat as pc,{$db_prefix}categories as cat,{$db_prefix}catval as cv "
                     . "WHERE pc.picid=$row->id AND "
                     . 'cv.category=cat.id AND pc.catid=cat.id AND '
                     . '((cat.isstring AND cv.stringval=pc.stringval) OR (NOT cat.isstring AND cv.intval=pc.intval)) AND '
                     . ' cat.display ORDER BY cat.id');

    while ($row2 = mysqli_fetch_object($res2))
        $longdesc .= '<br/>' . shtml('b',$row2->catname . ': ') . $row2->cvname;

    if (!is_null($row->date))
        $longdesc .= '<br/>' . shtml('b','Date taken: ') . substr($row->date,0,10);

    $q->desc = $longdesc;

    print json_encode($q);

}
catch (DataException $e) {
    $q = new stdClass();
    $q->error = $e->getMessage();

    print json_encode($q);
}

?>
