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

try {
    must_be_user();

    $allauthors = array();
    $res = exec_sql("SELECT * FROM {$db_prefix}authors ORDER BY range_low");
    while ($row = mysqli_fetch_object($res))
        $allauthors[] = $row;
}
catch (DataException $e) {
    wrapme('Edit Photographers',
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

<p>On this page you can add new photographers or edit or remove existing ones.</p>

<p>Photographers are linked to the images through the image number (that is, the last numerical
  component of the image file name). For each photographer, you must specify the range of numbers
  associated with his or her images.</p>

<p>If an image has a number that is not associated with any photographer, no photographer
information will be displayed with the image.</p>


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
  <tr><th>Low image number</th><th>High image number</th><th>Photographer name</th><th>Operations</th></tr>
  <?php foreach ($allauthors as $author): ?>
    <tr>
      <td class="left"><?= $author->range_low ?></td>
      <td class="left"><?= $author->range_high ?></td>
      <td class="left"><?= $author->name ?></td>
      <td>
        <a href="edit_one_author.php?id=<?= $author->id ?>">Edit</a>
        <a onclick="genericConfirm('Delete photographer','Do you want to delete photographer \'<?= $author->name ?>\' for the image range <?= $author->range_low ?>-<?= $author->range_high ?>?','delete_author.php?id=<?= $author->id ?>'); return false;" href="#">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<p><a class="makebutton" href="edit_one_author.php?id=-1">Add new photographer</a></p>

<?php
$bodytext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End body text
/////////////////////////////////////////////////////////////////////////////
?>

<?php

wrapme('Edit Photographers',
       null,
       null,
       $bodytext);

?>


