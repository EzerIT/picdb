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

try {
    must_be_admin();

    $allusers = array();
    $res = exec_sql("SELECT * FROM {$db_prefix}users ORDER BY username");
    while ($row = mysqli_fetch_object($res))
        $allusers[] = $row;
}
catch (DataException $e) {
    wrapme('Edit Users',
           null,
           null,
           "<p class=\"error\">Error: {$e->getMessage()}</p>");
    exit;
}

?>


<?php
/////////////////////////////////////////////////////////////////////////////
// Start help text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

<p>On this page you can add new users or edit or remove existing users.</p>

<p>There are three types of people who can access the picture database:</p>

<ul>
  <li><b>Ordinary users</b> cannot log in to the system. They can, however, see all the published
    pictures in the database.</li>
  <li><b>Registered users</b> can log in to the system and edit all the information relating to the
    pictures. They can also edit their own user profile, but they cannot add, edit, or delete other users.
    These users have the &ldquo;Administrator&rdquo; option set to &ldquo;No&rdquo;.</li>
  <li><b>Administrators</b> have all the privileges of registered users plus the ability to add,
    edit, or delete other users. Administrators have the &ldquo;Administrator&rdquo; option set to
    &ldquo;Yes&rdquo;.</li>
</ul>



<?php
$doctext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End help text
/////////////////////////////////////////////////////////////////////////////
?>


<?php
/////////////////////////////////////////////////////////////////////////////
// Start body text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

<table class="type1">
  <tr><th>User name</th><th>First name</th><th>Last name</th><th>E-mail</th><th>Administrator</th><th>Operations</th></tr>
  <?php foreach ($allusers as $user): ?>
    <tr>
      <td class="left"><?= $user->username ?></td>
      <td class="left"><?= $user->first_name ?></td>
      <td class="left"><?= $user->last_name ?></td>
      <td class="left"><?= $user->email ?></td>
      <td><?= $user->isadmin ? 'Yes' : 'No' ?></td>
      <td>
        <a href="edit_one_user.php?id=<?= $user->id ?>&amp;fromeu=1">Edit</a>
        <a onclick="genericConfirm('Delete user','Do you want to delete user \'<?= $user->username ?>\'','delete_user.php?id=<?= $user->id ?>'); return false;" href="#">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<p><a class="makebutton" href="edit_one_user.php?id=-1&amp;fromeu=1">Add new user</a></p>

<?php
$bodytext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End body text
/////////////////////////////////////////////////////////////////////////////
?>

<?php

wrapme('Edit Users',
       null,
       null,
       $bodytext);

?>


