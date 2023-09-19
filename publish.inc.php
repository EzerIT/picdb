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
require_once 'dataexception.inc.php';

// Toggles the publication statuts of the picture with the specified id.
// Returns the new publication status of the picture.
function toggle_publish($id) 
{
    global $db_prefix;
    global $dirname_big;
    global $dirname_600;
    global $dirname_160;
    global $unpub_dirname_big;
    global $unpub_dirname_600;
    global $unpub_dirname_160;

    if (!isset($id) || !is_numeric($id))
        throw new DataException("Illegal id value: '$id'");
    
    $res = exec_sql("SELECT * FROM {$db_prefix}photos WHERE id=$id");
    $picture = mysqli_fetch_object($res);

    if (!$picture)
        throw new DataException("Illegal id value: '$id'.");

    if ($picture->published) {
        $src_big = $dirname_big;
        $src_600 = $dirname_600;
        $src_160 = $dirname_160;

        $dst_big = $unpub_dirname_big;
        $dst_600 = $unpub_dirname_600;
        $dst_160 = $unpub_dirname_160;
    }
    else {
        $src_big = $unpub_dirname_big;
        $src_600 = $unpub_dirname_600;
        $src_160 = $unpub_dirname_160;

        $dst_big = $dirname_big;
        $dst_600 = $dirname_600;
        $dst_160 = $dirname_160;
    }

    if (!$picture->published) {
        $res2 = exec_sql("SELECT id FROM {$db_prefix}photos WHERE pic_no=$picture->pic_no AND published");
        if ($pic = mysqli_fetch_object($res2))
            throw new DataException("A published picture with picture number $picture->pic_no already exists");
    }

    if (file_exists("$dst_big/$picture->filename"))
        throw new DataException(($picture->published ? 'An unpublished' : 'A published')
                                . " picture with the filename $picture->filename already exists.");

    if (!rename("$src_big/$picture->filename","$dst_big/$picture->filename") ||
        !rename("$src_600/$picture->filename","$dst_600/$picture->filename") ||
        !rename("$src_160/$picture->filename","$dst_160/$picture->filename"))
        throw new DataException("Moving picture $picture->filename failed.");


    exec_sql("UPDATE {$db_prefix}photos SET published=" . (1-$picture->published) . " WHERE id=$id");
    
    return 1-$picture->published;
}
?>
