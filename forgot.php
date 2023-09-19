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
require_once 'database.inc.php';
require_once 'dataexception.inc.php';

$sent = false;

try {
    if (isset($_POST['submit'])) {
        $username = my_escape_string(trim($_POST['username']));
        $email = my_escape_string(trim($_POST['email']));
        
        if (!empty($username)) {
            if (preg_match("/\W/", $username))
                throw new DataException('Illegal character in user name');

            $res = exec_sql("SELECT * FROM {$db_prefix}users WHERE username='$username'");
            if (mysqli_num_rows($res)==0)
                throw new DataException('Unknown user name');
        }
        elseif (!empty($email)) {
            if (!preg_match("/@/", $email))
                throw new DataException('Illegal e-mail addresss');

            $res = exec_sql("SELECT * FROM {$db_prefix}users WHERE email='$email'");
            if (mysqli_num_rows($res)==0)
                throw new DataException('Unknown e-mail address');

            if (mysqli_num_rows($res)>1) {
                $unames = "";
                while ($row = mysqli_fetch_object($res))
                    $unames .= $row->username . " ";
                
                throw new DataException('Your e-mail address is associated with several user accounts. '
                                        . "Specify one of these user names: $unames");
            }
        }
        else
            throw new DataException('Specify user name or e-mail address');

        $row = mysqli_fetch_object($res);
            
        // Generate a password, eight random characters from the following set
        // which deliberately excludes l, 1, O and 0
        $pwchar = "abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ23456789";
        $strl = strlen($pwchar)-1;
        $passwd = $pwchar[mt_rand(0,$strl)]
            . $pwchar[mt_rand(0,$strl)]
            . $pwchar[mt_rand(0,$strl)]
            . $pwchar[mt_rand(0,$strl)]
            . $pwchar[mt_rand(0,$strl)]
            . $pwchar[mt_rand(0,$strl)]
            . $pwchar[mt_rand(0,$strl)]
            . $pwchar[mt_rand(0,$strl)];

        $md5pw = md5($pw_salt . $passwd);

        // Store in databse
        exec_sql("UPDATE {$db_prefix}users SET password='$md5pw' WHERE id=$row->id");

        // Inform user
        mail($row->email,"Picture Database: Account information ",
             "Dear $row->first_name $row->last_name\n\n"
             . "Your user name for the picture database is: $row->username\n"
             . "Your password has been set to: $passwd\n\n"
             . "You may change your password by selecting 'Edit my profile' from the administrative menu.\n",
             "From: $mail_sender\nReply-to: $mail_sender\nContent-Type: text/plain; charset=utf-8");

        $sent = true;
    }
}
catch (DataException $ex) {
    $login_msg = $ex->getMessage();
}
?>

<?php
/////////////////////////////////////////////////////////////////////////////
// Start body text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

  <div id="logincenter">
    <?php if (!$sent): ?>
      <h1>Forgot user name or password</h1>
      <div class="ui-corner-all" id="loginbox">
        <?= isset($login_msg) ? "<p class=\"error\">$login_msg</p>" : ''?>
        <p>Type user name or e-mail address</p>
        <form action="forgot.php" method="POST">
           <table>
             <tr><td>User name:</td><td><input type="text" name="username" size="20" /></td></tr>
             <tr><td>E-mail address:</td><td><input type="text" name="email" size="20" /></td></tr>
           </table>
          <input class="makebutton" type="submit" name="submit" value="Submit" />
          <input class="makebutton" type="button" name="cancel" value="Cancel" onClick="location='login.php';" />
        </form>
      </div>
    <?php else: ?>
      <h1>Your user name and a new password has been sent to <?= $row->email ?></h1>
    <?php endif; ?>

    <p class="center"><a href="img.php">Main page</a></p>
  </div>   

<?php
$bodytext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End body text
/////////////////////////////////////////////////////////////////////////////
?>


<?php

wrapme('Forgot User Name or Password',
       null,
       null,
       $bodytext,
       array(),
       array(),
       false);

?>
