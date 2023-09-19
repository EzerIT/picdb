<?php
// Copyright (c) 2012 Ezer IT Consulting.  E-mail: claus@ezer.dk.
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
require_once 'findref.inc.php';


must_be_user();

if (isset($_GET['doit']) && $_GET['doit']=='1') {
    exec_sql("DELETE FROM {$db_prefix}bibleref"); // Empty biblref table

    $res = exec_sql("SELECT id,filename,description FROM {$db_prefix}photos");

    $allrefs = array();

    while ($picture = mysqli_fetch_object($res)) {
        findref($picture->id, $picture->description, $ref_sql, $refs);
        if (!empty($ref_sql)) {
            exec_sql("INSERT INTO {$db_prefix}bibleref (bookid,chapter,verse_low,verse_high,picid) VALUES "
                     . substr($ref_sql,1));
            $allrefs[] = array($picture->filename, $refs);
        }
    }
}
?>

<?php
/////////////////////////////////////////////////////////////////////////////
// Start body text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

<?php if (isset($_GET['doit']) && $_GET['doit']=='1'): ?>

  <?php if (isset($allrefs) && count($allrefs)>0): ?>
    <p>The following Bible references were found:</p>
    <table class="type1">
      <tr><th>Filename</th><th>References</th></tr>
      <?php foreach ($allrefs as $ref): ?>
        <tr>
          <td class="left"><?= $ref[0] ?></td>
          <td class="left"><?= substr($ref[1],2) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

<?php else: ?>

  <h1>Scan Picture Descriptions for Bible References</h1>

  <div class="narrow">
    <p>If you click the button below, the system will scan all picture descriptions for Bible
    references and update the reference information accordingly. This should only be necessary
    if the software has been updated to recognize more Bible references.</p>
  </div>

  <input class="makebutton" onclick="location='rescan.php?doit=1'" type="button" value="Scan for Bible references" />

<?php endif; ?>

<?php
$bodytext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End body text
/////////////////////////////////////////////////////////////////////////////
?>


<?php

wrapme('Scan Again for Bible References',
       null,
       null,
       $bodytext);

?>

