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
    must_be_user();

    if (!isset($_GET['id']) || !is_numeric($_GET['id']))
        throw new DataException("Illegal id value: '$id'");

    // $_GET['id'] is -1 if a new author is to be added

    $id = $_GET['id'];

    if ($id!=-1) {
        $res = exec_sql("SELECT * FROM {$db_prefix}authors WHERE id=$id");
        $author = mysqli_fetch_object($res);

        if (!$author)
            throw new DataException("Illegal id value: '$id'");
    }
    else {
        $author = new stdClass();
        $author->range_low = 0;
        $author->range_high = 0;
        $author->name = '';
    }

    if (isset($_POST['submit'])) {
        $range_low = intval($_POST['range_low']);
        $range_high = intval($_POST['range_high']);
        $authorname = my_escape_string(strip_tags(trim($_POST['authorname'])));

        if (empty($authorname))
            throw new DataException2('Missing photographer name');
        if ($range_low<=0)
            throw new DataException2('Illegal low image number');
        if ($range_high<=0)
            throw new DataException2('Illegal high image number');
        if ($range_high<$range_low)
            throw new DataException2('High image number is less than low image number');

        if ($id==-1) {
            exec_sql("INSERT INTO {$db_prefix}authors (range_low,range_high,name)"
                     . " VALUES ($range_low,$range_high,'$authorname')");
        }
        else {
            exec_sql("UPDATE {$db_prefix}authors SET range_low=$range_low, range_high=$range_high, name='$authorname' WHERE id=$id");
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
    <h1>Photographer '<?= $authorname ?>' with range <?= $range_low ?>-<?= $range_high ?> added</h1>
  <?php else: ?>
    <h1>Photographer changed to '<?= $authorname ?>' with range <?= $range_low ?>-<?= $range_high ?></h1>
  <?php endif; ?>

  <p><input class="makebutton" onclick="location='edit_authors.php'" type="button" value="Continue" /></p>

<?php else: ?>

  <h1><?= $id==-1 ? 'Add new photographer' : 'Edit photographer' ?></h1>

  <?php if (!empty($error1)): ?>
    <p class="error">Error: <?= $error1 ?></p>
  <?php else: ?>

    <?php if (!empty($error2)): ?>
      <p class="error">Error: <?= $error2 ?></p>
    <?php endif; ?>

    <form action="edit_one_author.php?id=<?= $id ?>" method="post">
      <table class="form">
        <tr>
          <td>Low image number:</td>
          <td><input type="text" name="range_low" value="<?= $author->range_low ?>" /></td>
        </tr>
        <tr>
          <td>High image number:</td>
          <td><input type="text" name="range_high" value="<?= $author->range_high ?>" /></td>
        </tr>
        <tr>
          <td>Photographer name:</td>
          <td><input type="text" name="authorname" value="<?= $author->name ?>" /></td>
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

wrapme($id==-1 ? 'Add New Photographer' : 'Edit Photographer',
       null,
       null,
       $bodytext);

?>


