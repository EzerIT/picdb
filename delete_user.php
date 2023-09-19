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

must_be_user();

try {
    must_be_admin();

    if (!isset($_GET['id']) || !is_numeric($_GET['id']))
        throw new DataException("Illegal id value: '$id'");

    $id = my_escape_string($_GET['id']);

    $res = exec_sql("SELECT username FROM {$db_prefix}users WHERE id=$id");
    if (mysqli_num_rows($res)==0)
        throw new DataException("Illegal id value: '$id'.");

    if ($credentials->user->id==$id)
        throw new DataException('You cannot delete yourself');

    exec_sql("DELETE FROM {$db_prefix}users WHERE id=$id");

    header("Location: edit_users.php");
}
catch (DataException $e) {
    wrapme('Delete User',
           null,
           null,
           "<p class=\"error\">Error: {$e->getMessage()}</p>");
}
?>
