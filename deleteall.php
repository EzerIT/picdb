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


require_once 'wrapper.inc.php';
require_once 'dataexception.inc.php';
require_once 'delete.inc.php';

must_be_user();

try {
    $res = exec_sql("SELECT id,filename FROM {$db_prefix}photos WHERE NOT published");

    $published = true;

    while ($row = mysqli_fetch_object($res))
        $published = delete_picture($row->id);

    if (!$published)
        header("Location: manpic.php");
    else
        header("Location: img.php");
}
catch (DataException $e) {
    wrapme('Delete All Pictures',
           null,
           null,
           "<p class=\"error\">Error when processing picture {$row->filename} with internal database id={$row->id}:<br/>{$e->getMessage()}</p>");
}
?>
