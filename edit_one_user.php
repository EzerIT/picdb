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

$error1 = null;
$error2 = null;
$changed = false;

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id']))
        throw new DataException("Illegal id value: '$id'");

    // $_GET['id'] is -1 if a new user is to be added

    $id = $_GET['id'];

    if ($id==-1)
        must_be_admin();
    else
        must_be_admin_or_me($id);

    // Administrators can change the administrative status of others but not of themselves;
    // also, administrators need to supply their old password when changing passwords
    $is_admin_and_not_me = $credentials->is_admin() && $credentials->user->id!=$id;

    $from_edit_users = isset($_GET['fromeu']);

    if ($id!=-1) {
        $res = exec_sql("SELECT * FROM {$db_prefix}users WHERE id=$id");
        $user = mysqli_fetch_object($res);

        if (!$user)
            throw new DataException("Illegal id value: '$id'");
    }
    else {
        $user = new stdClass();
        $user->username = '';
        $user->first_name = '';
        $user->last_name = '';
        $user->email = '';
        $user->password = '';
        $user->isadmin = 0;
    }

    if (isset($_POST['submit'])) {
        if ($id==-1)
            $username = my_escape_string(trim($_POST['username']));
        $first_name = my_escape_string(strip_tags(trim($_POST['firstname'])));
        $last_name = my_escape_string(strip_tags(trim($_POST['lastname'])));
        $email = my_escape_string(trim($_POST['email']));

        if ($is_admin_and_not_me) 
            $isadmin = $_POST['isadmin']=='yes' ? 1 : 0;
        else {
            $isadmin = null; // This means, don't change state
            $old_password = trim($_POST['oldpassword']);
        }

        $password1 = trim($_POST['password1']);
        $password2 = trim($_POST['password2']);

        if ($id==-1 && empty($username))
            throw new DataException2('Missing user name');
        if ($id==-1 && preg_match('/\W/', $username))
            throw new DataException2('Illegal character in user name (only a-z, 0-9 and _ are allowed)');
        if (empty($first_name))
            throw new DataException2('Missing first name');
        if (empty($last_name))
            throw new DataException2('Missing last name');
        if (empty($email))
            throw new DataException2('Missing e-mail address');
        
        if (!empty($password1)) {
            if ($is_admin_and_not_me || $user->password==md5($pw_salt . $old_password)) {
                if ($password1!=$password2)
                    throw new DataException2('The two new passwords are not identical');
            }
            else
                throw new DataException2('Old password is ' . (empty($old_password) ? 'missing' : 'wrong'));
            
            // New password specified and OK.
            $md5pw = md5($pw_salt . $password1);
        }
        elseif ($id==-1)
            throw new DataException2('Missing password');
        else
            $md5pw = '';

        if ($id==-1) {
            // Check that we have a unique user name
            $res = exec_sql("SELECT id FROM {$db_prefix}users WHERE username='$username'");
            if (mysqli_num_rows($res)>0)
                throw new DataException2('User name is already in taken');

            exec_sql("INSERT INTO {$db_prefix}users (username,first_name,last_name,email,isadmin,password)"
                     . " VALUES ('$username','$first_name','$last_name','$email',$isadmin,'$md5pw')");
        }
        else {
            $changes = array(
                "first_name='$first_name'",
                "last_name='$last_name'",
                "email='$email'");

            if (!is_null($isadmin))
                $changes[] = "isadmin=$isadmin";

            if (!empty($md5pw))
                $changes[] = "password='$md5pw'";

            exec_sql("UPDATE {$db_prefix}users SET " . implode(',',$changes) . " WHERE id=$id");
        }

        $changed = true;
    }
}
catch (DataException $e) {
    // Illegal parameters on initial request
    $error1 = $e->getMessage();
}
catch (DataException2 $e) {
    // Illegal parameters on update request
    $error2 = $e->getMessage();
}

?>



<?php
/////////////////////////////////////////////////////////////////////////////
// Start body text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

<?php if ($changed): ?>
  <?php if ($id==-1): ?>
    <h1>User '<?= $username ?>' added</h1>
  <?php else: ?>
    <h1>User '<?= $user->username ?>' changed</h1>
  <?php endif; ?>

  <?php if ($from_edit_users): ?>
    <p><input class="makebutton" onclick="location='edit_users.php'" type="button" value="Continue" /></p>
  <?php else: ?>
    <p><input class="makebutton" onclick="location='img.php'" type="button" value="Continue" /></p>
  <?php endif; ?>

<?php else: ?>

  <h1><?= $id==-1 ? 'Add new user' : 'Edit user profile' ?></h1>

  <?php if (!empty($error1)): ?>
    <p class="error">Error: <?= $error1 ?></p>
  <?php else: ?>

    <?php if (!empty($error2)): ?>
      <p class="error">Error: <?= $error2 ?></p>
    <?php endif; ?>

    <form action="edit_one_user.php?id=<?= $id ?><?= $from_edit_users ? '&fromeu=1' : '' ?>" method="post">
      <table class="form">
        <tr>
          <td>User name:</td>
          <td>
            <?php if ($id==-1): ?>
              <input type="text" name="username" value="<?= $user->username ?>" />
            <?php else: ?>
              <?= $user->username ?> (Cannot be changed)
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td>First name:</td>
          <td><input type="text" name="firstname" value="<?= $user->first_name ?>" /></td>
        </tr>
        <tr>
          <td>Last name:</td>
          <td><input type="text" name="lastname" value="<?= $user->last_name ?>" /></td>
        </tr>
        <tr>
          <td>E-mail:</td>
          <td><input type="text" name="email" value="<?= $user->email ?>" /></td>
        </tr>
        <?php if ($is_admin_and_not_me): ?>
          <tr>
            <td>Administrator:</td>
            <td>
              <input class="narrow" type="radio" name="isadmin" value="yes" <?= $user->isadmin ? 'checked="checked"' : '' ?> />Yes
              <input class="narrow" type="radio" name="isadmin" value="no" <?= $user->isadmin ? '' : 'checked="checked"' ?> />No
            </td>
          </tr>
        <?php else: ?>
          <tr>
            <td>Old password:</td>
            <td><input type="password" name="oldpassword" /> (Required if changing password)</td>
          </tr>
        <?php endif; ?>
        <tr>
          <td><?= $id==-1 ? 'Password' : 'New password' ?>:</td>
          <td>
            <input type="password" name="password1" />
            <?php if ($id!=-1): ?>
              (Leave blank if not changing password)
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td>Repeat <?= $id==-1 ? '' : 'new' ?> password:</td>
          <td>
            <input type="password" name="password2" />
            <?php if ($id!=-1): ?>
              (Leave blank if not changing password)
            <?php endif; ?>
          </td>
        </tr>
      </table>
      <p><input class="makebutton" type="submit" name="submit" value="Submit" /></p>
    </form>
  <?php endif; // !empty($error1) ?>
<?php endif; ?>

<?php
$bodytext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End body text
/////////////////////////////////////////////////////////////////////////////
?>


<?php

wrapme($id==-1 ? 'Add new user' : 'Edit user profile',
       null,
       null,
       $bodytext);

?>


