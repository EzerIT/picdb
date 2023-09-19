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

require_once 'database.inc.php';

header("Content-Type: application/json");

$res = exec_sql("SELECT books.wivu_name,ref.chapter,ref.verse_low,photos.pic_no "
                . "FROM {$db_prefix}bibleref AS ref,{$db_prefix}biblebooks AS books,{$db_prefix}photos AS photos "
                . "WHERE ref.bookid=books.id AND ref.picid=photos.id AND photos.published");

$refs = array();

while ($row = mysqli_fetch_object($res)) {
    if (empty($row->wivu_name))
        continue;
 
    $key = "$row->wivu_name:$row->chapter:$row->verse_low";
    if (!array_key_exists($key, $refs))
        $refs[$key] = array();
    $refs[$key][] = (int)$row->pic_no;
}


$res = exec_sql("SELECT books.wivu_name,cbr.chapter,cbr.verse_low,cv.category,cv.intval,cv.stringval,cat.isstring "
                . "FROM {$db_prefix}catbibleref AS cbr,{$db_prefix}catval AS cv,{$db_prefix}categories AS cat,"
                . "{$db_prefix}biblebooks AS books "
                . "WHERE cbr.catval_id=cv.id AND cat.id=cv.category AND cbr.bookid=books.id "
                . "AND books.wivu_name IS NOT NULL");


while ($row = mysqli_fetch_object($res)) {
    if ($row->isstring)
        $cond = "stringval=$row->stringval";
    else
        $cond = "intval=$row->intval";

    $res2 = exec_sql("SELECT photos.pic_no from {$db_prefix}piccat AS piccat,{$db_prefix}photos AS photos "
                     . "WHERE catid=$row->category AND $cond AND piccat.picid=photos.id AND photos.published");

    while ($row2 = mysqli_fetch_object($res2)) {
        $key = "$row->wivu_name:$row->chapter:$row->verse_low";
        if (!array_key_exists($key, $refs))
            $refs[$key] = array();
        $refs[$key][] = (int)$row2->pic_no;
    }
}


print json_encode($refs);


?>