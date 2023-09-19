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
require_once 'publish.inc.php';

must_be_user();

try {
    if ($_GET['pub']=='0') {
        $search = "published";
        $pub = false;
    }
    elseif ($_GET['pub']=='1') {
        $search = "NOT published";
        $pub = true;
    }
    else
        throw new DataException("Illegal parameter for script");

    $res = exec_sql("SELECT id,filename FROM {$db_prefix}photos WHERE $search");

    while ($row = mysqli_fetch_object($res))
        toggle_publish($row->id);

    if ($pub)
        header("Location: manpic.php");
    else
        header("Location: img.php");
}
catch (DataException $e) {
    wrapme('Change Publication Status',
           null,
           null,
           "<p class=\"error\">Error when processing file {$row->filename} with internal database id={$row->id}:<br/>{$e->getMessage()}</p>");
}
?>
