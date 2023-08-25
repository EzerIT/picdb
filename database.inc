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


// This file contains various database functions.

require_once 'config.inc';

$connected_db = null;


// Prints the error from the last SQL operation.
function print_sql_error($sql,$end = false)
{
    $error = mysqli_error();
    print "<p><b>SQL-error:</b> <i>$error</i></p> <p><b>Request:</b> <i>$sql</i></p>";
    if ($end)
        exit();
}

// Connects to the database
function connect_db()
{
    global $connected_db;

    $connected_db = mysqli_connect(DB_HOST, DB_USER, DB_PASS,DB_NAME);
    if (!$connected_db) {
        print "<p>Cannot connect to database</p>\n";
        print "</body></html>";
        exit();
    }

    exec_sql("SET CHARACTER SET UTF8");
    exec_sql("SET NAMES 'utf8'");

    return $connected_db;
}

// Executes an SQL statement and returns the resulting resource
function exec_sql($sql, $printit=false)
{
    global $connected_db;
    if (!$connected_db)
        connect_db();

    if ($printit)
        print "<pre>$sql</pre>";

    $rs = mysqli_query($connected_db, $sql) or print_sql_error($sql, true);
    return $rs;
}

// Wraps mysqli_real_escape_string 
function my_escape_string($s)
{
    global $connected_db;
    if (!$connected_db)
        connect_db();

    return mysqli_real_escape_string($connected_db,$s);
}

