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
require_once 'html.inc.php';
require_once 'database.inc.php';
require_once 'dataexception.inc.php';
require_once 'findref.inc.php';
require_once 'csvreader.inc.php';

must_be_user();


try {
    if (isset($_FILES['file'])) {
        // TODO: Handle character sets. Does UTF-8 always work. fgetcvs() uses locale.

        if ($_FILES['file']['error'] > 0)
            throw new DataException('Upload error: ' . $_FILES['file']['error']);
        
        $cr = new CsvReader($_FILES['file']['tmp_name']);

        $inserts = 0;
        $updates = 0;
        $missing = array();
        $allrefs = array();

        while ($data = $cr->read_csv(2,2)) {
            $res = exec_sql("SELECT id,filename,description FROM {$db_prefix}photos WHERE filename='{$data[0]}'");
            $found = false;
            while ($row = mysqli_fetch_object($res)) { // There may be both a published and an unpublish picture
                $found = true;
                if ($row->description!=$data[1]) {
                    $desc = my_escape_string($data[1]);
                    exec_sql("UPDATE {$db_prefix}photos SET description='$desc' WHERE id=$row->id");
                    if (is_null($row->description))
                        ++$inserts;
                    else 
                        ++$updates;

                    findref($row->id, $desc, $ref_sql, $refs);
                    exec_sql("DELETE FROM {$db_prefix}bibleref WHERE picid=$row->id");
                    if (!empty($ref_sql)) {
                        exec_sql("INSERT INTO {$db_prefix}bibleref (bookid,chapter,verse_low,verse_high,picid) VALUES "
                                 . substr($ref_sql,1));
                        $allrefs[] = array($row->filename, $refs);
                    }
                }
            }
            if (!$found)
                $missing[] = $data[0];
        }
        $cr->close();
        // unlink($_FILES['file']['tmp_name']); May not be necessary
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

<p>On this page you can upload a file containing descriptions for several pictures.</p>

<p>The file must be a plain text file. If it contains non-English characters, it must be in UTF-8
encoding. The content of the file must follow these rules:</p>

<ul>
<li>There must be one picture description per line. The format must be similar to this:</li>
<li style="list-style-type:none"><pre>
   183_0_2_M.jpg,&quot;Text for the first picture&quot;
   183_0_3_M.jpg,&quot;Text for the second picture&quot;
   183_0_4_M.jpg,&quot;Text for the third picture&quot;
</pre></li>
<li style="list-style-type:none">The first item on each line is a file name, the second item is the
picture description in quotation marks.</li>
<li>There must be no space between the comma and the first quotation mark.</li>
<li>The description must not contain line breaks.</li>
<li>A few HTML formatting codes can be used: &lt;b&gt;Bold text&lt;b&gt; and &lt;i&gt;Italic text&lt;i&gt;.</li>
<li>If the description is to contain a quotation mark, it must be doubled:</li>
<li style="list-style-type:none"><pre>
   183_0_2_M.jpg,&quot;This is a &quot;&quot;strange&quot;&quot; text.&quot;
</pre></li>
<li>The description may contain Bible references, using a fixed set of abbreviations (for example, &ldquo;Ex. 2:9-12&rdquo; &ndash; see below). In that case, the database will associate the picture with that Bible reference.</li>
</ul>

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

  <?php if (isset($inserts)): ?>
    <p><?=$inserts?> descriptions added, <?=$updates?> descriptions updated.</p>
  <?php endif; ?>

  <?php if (isset($missing) && count($missing)>0): ?>
    <p>Missing files:</p>
    <ul>
      <?php foreach ($missing as $m): ?>
        <li><?=$m?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
             
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

  <div class="coloredbg">
    <h1>Upload new image descriptions (comma separated list)</h1>
    <form action="uploaddesc.php" method="post" enctype="multipart/form-data">
      <p>
        <label for="file">Filename:</label>
        <input type="file" name="file" id="file" />
        <br/>
        <input class="makebutton" type="submit" name="submit" value="Submit" />
      </p>
     </form>
   </div>


<?php
$bodytext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End body text
/////////////////////////////////////////////////////////////////////////////
?>


<?php


wrapme('Upload Descriptions',
       null,
       null,
       $bodytext);

?>
