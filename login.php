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

require_once 'wrapper.inc';
require_once 'database.inc';
require_once 'dataexception.inc';


// Is this a logout request?
if (isset($_GET['func']) && $_GET['func']=='logout') {
    $_SESSION['picdb_user'] = 0;
    header("Location: img.php");
    exit;
}


// Is this a login request?
if (isset($_POST['submit'])) {
    if (empty($_POST['login_name'])) {
        $login_msg = 'Missing username';
        $_SESSION['picdb_user'] = 0; // Paranoia
    }
    elseif (preg_match('/\W/', $_POST['login_name'])) {
        $login_msg = 'Illegal username';
        $_SESSION['picdb_user'] = 0; // Paranoia
    }
    else {
        $pw = md5($pw_salt . $_POST['password']);
        $name = my_escape_string($_POST['login_name']);
        $res = exec_sql("SELECT * FROM {$db_prefix}users WHERE username='$name' AND password='$pw'");
        $row = mysqli_fetch_object($res);
        if (!$row) {
            $login_msg = 'Uknown username or password';
            $_SESSION['picdb_user'] = 0; // Paranoia
        }
        else {
            $_SESSION['picdb_user'] = $row->id;
            header("Location: img.php");
        }
    }
}
elseif (isset($_SESSION['picdb_user']) && $_SESSION['picdb_user']>0) {
    $login_msg = 'You have been logged out';
    $_SESSION['picdb_user'] = 0;
}
?>


<?php
/////////////////////////////////////////////////////////////////////////////
// Start body text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

  <div id="logincenter">
    <h1>Log in to access the administrative features</h1>
    <div class="ui-corner-all" id="loginbox">
      <?= isset($login_msg) ? "<p class=\"error\">$login_msg</p>" : ''?>
      <form action="login.php" method="POST">
        <table>
          <tr><td>User name</td><td><input type="text" name="login_name" size="20" /></td></tr>
          <tr><td>Password</td><td><input type="password" name="password" size="20" /></td></tr>
        </table>
          <input class="makebutton" class="button" type="submit" name="submit" value="Login" />
      </form>
      <p><a href="forgot.php">Forgotten user name or password?</a></p>
    </div>
    <p class="center"><a href="img.php">Main page</a></p>
  </div>   

<?php
$bodytext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End body text
/////////////////////////////////////////////////////////////////////////////
?>

<?php

wrapme('Administrative Login',
       null,
       null,
       $bodytext,
       array(),
       array(),
       false);

?>
