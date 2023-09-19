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
require_once 'csvreader.inc.php';
require_once 'dataexception.inc.php';
require_once 'findref.inc.php';

must_be_user();

$catid = isset($_GET['category']) ? $_GET['category'] : 0;

$allcats = array();
$res = exec_sql("SELECT * FROM {$db_prefix}categories ORDER BY name");
while ($row = mysqli_fetch_object($res))
    $allcats[$row->id] = $row;

try {
    $uploaded = false;

    if (!is_numeric($catid))
        throw new DataException("Illegal category number: $catid");

    if ($catid>0) {
        if (!isset($allcats[$catid]))
            throw new DataException("Illegal category number: $catid");
        else
            $catobj = $allcats[$catid];

        if (isset($_FILES['file'])) {
            if ($_FILES['file']['error'] > 0)
                throw new DataException('System file error: ' . $_FILES['file']['error']);

            $cs = new CsvReader($_FILES['file']['tmp_name']);

            $deletes = array();
            $inserts = array();

            while ($data = $cs->read_csv(2,2)) {
                if (!is_numeric($data[0]))
                    throw new DataException("Illegal category number: {$data[0]}");

                $res = exec_sql("SELECT id,name FROM {$db_prefix}catval WHERE category=$catobj->id AND "
                                . ($catobj->isstring
                                   ? "stringval='{$data[0]}' AND stringval_high IS NULL"
                                   : "intval={$data[0]} AND intval_high IS NULL"));

                $row = mysqli_fetch_object($res);
                if (!$row)
                    throw new DataException("Illegal category number: {$data[0]}");

                if ($data[1]=="DELETE") {
                    exec_sql("DELETE FROM {$db_prefix}catbibleref WHERE catval_id=$row->id");
                    $deletes[] = $row->name;
                }
                else {
                    findref($row->id, $data[1], $ref_sql, $refs);
                    if (array_key_exists($row->id, $inserts))
                        $inserts[$row->id][1] .= $refs;
                    else
                        $inserts[$row->id] = array($row->name,$refs);

                    exec_sql("INSERT IGNORE INTO {$db_prefix}catbibleref (bookid,chapter,verse_low,verse_high,catval_id) VALUES "
                             . substr($ref_sql,1));
                }
            }
            $cs->close();
            $uploaded = true;
        }

        $allrefs = array();

        $res = exec_sql("SELECT cv.id AS cv_id,cv.name AS cv_name,english_abb,chapter,verse_low,verse_high "
                        . "FROM {$db_prefix}catbibleref AS cbr,{$db_prefix}catval AS cv,{$db_prefix}biblebooks AS bb "
                        . "WHERE cv.category=$catid AND cbr.catval_id=cv.id AND cbr.bookid=bb.id "
                        . "ORDER by bb.id,chapter,verse_low");

        while ($row = mysqli_fetch_object($res)) {
            $ref = ", $row->english_abb $row->chapter";
            if ($row->verse_high==refs::MAX_VERSE) {  // No end verse
                if ($row->verse_low!=refs::MIN_VERSE)
                    $ref .= ":$row->verse_low-end";   // From verse_low to end
                // else: Entire chapter
            }
            else {
                if ($row->verse_low==$row->verse_high)
                    $ref .= ":$row->verse_low";  // Only one verse
                else
                    $ref .= ":$row->verse_low-$row->verse_high"; // From verse_low to verse_high
            }

            if (array_key_exists($row->cv_id, $allrefs))
                $allrefs[$row->cv_id][1] .= $ref;
            else
                $allrefs[$row->cv_id] = array($row->cv_name,$ref);
        }
    }
}
catch (DataException $e) {
    $error = $e->getMessage();
}

?>


<?php
/////////////////////////////////////////////////////////////////////////////
// Start help text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

<p>Once you have defined a set of values within each picture category, you may associate each value
with a set of Bible references. This has no effect within the picture database itself, but it is
used by PLOTLearner programs that access the database.</p>

<p>At the top of the page, there is a drop-down list of available categories. If you select a
category here, the page will display the Bible references that are defined for each value of
that category.</p>

<p>At the bottom of the page you can upload a file containing a collection of Bible references.</p>

<p>The file must be a plain text file. If it contains non-English characters, it must be in UTF-8
encoding. The content of the file must follow these rules:</p>

<ul>
<li>There must be one value per line. (Value ranges are not allowed.) The format must be similar to this:</li>
<li style="list-style-type:none"><pre>
   183,&quot;Gen. 3:8-10; Ex. 8:5&quot;
   140,&quot;DELETE&quot;
</pre></li>
<li style="list-style-type:none">The first item on each line is a value that has
previously been defined within this category. (For example, the value 183 may refer to "Jericho"
within the "Place" category.) The second item is a list of Bible references enclosed in quotation
marks. See below for a description of &quot;DELETE&quot;.</li>
<li>There must be no space between the comma and the first quotation mark.</li>
</ul>

<p>The listed Bible references will be added to the specified category value.</p>

<p>If the text is simply the word DELETE, all Bible references associated with the value will be
removed from the database.</p>

<p>The Bible references should be written in the format &ldquo;Gen.&nbsp;3:8-10&rdquo; to identify
  Genesis chapter 3 verses 8 through 10. The legal Bible book abbreviations are:</p>

<blockquote>
  <p><?= str_replace( array(" ",      ","),
                      array("&nbsp;", ", "),
                      implode(",",$book2EnById) ) ?></p>
</blockquote>

<p>A Danish Bible reference format (for example, &ldquo;1&nbsp;Mos&nbsp;3,8-10&rdquo;) is also
accepted.</p>


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

  <?php if (isset($error)): ?>
    <p class="error">Error: <?=$error?></p>
  <?php endif; ?>

  <p>
    <select id="category">
      <option value="0" <?= $catid==0 ? 'selected="selected"' : '' ?> >Select a category</option>
      <?php foreach ($allcats as $cat): ?>
        <option value="<?= $cat->id ?>" <?= $catid==$cat->id ? 'selected="selected"' : '' ?> ><?= $cat->name ?></option>
      <?php endforeach; ?>
    </select>
  </p>

  <?php if ($uploaded): ?>
    <?php if (count($deletes) + count($inserts) == 0): ?>
      <h2>No changes were made</h2>
    <?php else: ?>
      <?php if (count($deletes)>0): ?>
        <h2>Bible references for the following category values were deleted:</h2>
        
        <?php foreach ($deletes as $d): ?>
          <p class="narrow"><?= $d ?></p>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (count($inserts)>0): ?>
        <h2>Bible references for the following category values were added:</h2>

        <table class="type2">
          <tr><th>Category value</th><th>References</th></tr>

          <?php foreach ($inserts as $d): ?>
            <tr><td><?= $d[0] ?></td><td><?= substr($d[1],1) ?></td>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (isset($allrefs)): ?>
    <?php if (count($allrefs)==0): ?>
      <h2>Category '<?= $catobj->name ?>' has no Bible references</h2>
    <?php else: ?>
      <h2>Bible references for category '<?= $catobj->name ?>'</h2>

      <table class="type1">
        <tr><th>Category value</th><th>References</th></tr>
        <?php foreach ($allrefs as $d): ?>
          <tr><td><?= $d[0] ?></td><td><?= substr($d[1],1) ?></td>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  <?php endif; ?>

  <?php if ($catid>0): ?>
    <div class="coloredbg">
      <h2>Upload new Bible references for category '<?= $catobj->name ?>'</h2>
   
      <form action="editcatref.php?category=<?= $catid ?>" method="post" enctype="multipart/form-data">
        <p>
          <label for="file">Filename:</label>
          <input type="file" name="file" id="file" />
          <br/>
          <input class="makebutton" type="submit" name="submitr" value="Submit"/>
        </p>
      </form>
    </div>
  <?php endif; ?>

<?php
$bodytext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End body text
/////////////////////////////////////////////////////////////////////////////
?>




<?php
/////////////////////////////////////////////////////////////////////////////
// Start header script
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

    $(function() {
            $('#category').change(function() {
                    window.location = 'editcatref.php?category=' + $(this).val();
                });
        });

<?php
$headerscript = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End header script
/////////////////////////////////////////////////////////////////////////////
?>


<?php

wrapme('Upload Bible References for Category',
       $headerscript,
       null,
       $bodytext);

?>
