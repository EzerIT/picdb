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

header("Content-Type: application/json");

$res = exec_sql("SELECT books.wivu_name,chapter,verse_low,url,type "
                . "FROM {$db_prefix}bibleurl,{$db_prefix}biblebooks AS books "
                . "WHERE bookid=books.id");

$refs = array();

while ($row = mysqli_fetch_object($res)) {
    if (empty($row->wivu_name))
        continue;

    $key = "$row->wivu_name:$row->chapter:$row->verse_low";
    if (!array_key_exists($key, $refs))
        $refs[$key] = array();
    $refs[$key][] = array($row->url,$row->type);
}

print json_encode($refs);


?>