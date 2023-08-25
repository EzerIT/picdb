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

//////////////////////////////////////////////////////////////////////
// Data part
//////////////////////////////////////////////////////////////////////

require_once 'wrapper.inc';
require_once 'util.inc';
require_once 'html.inc';
require_once 'database.inc';
require_once 'dataexception.inc';
require_once 'findref.inc';


must_be_user();


try {
    if (isset($_POST['submit'])) {
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            if (!is_numeric($id))
                throw new DataException("Illegal id value: '$id'");
            $desc = my_escape_string($_POST['content']);

            $res = exec_sql("SELECT published FROM {$db_prefix}photos WHERE id=$id");
            $picture = mysqli_fetch_object($res);

            if (!$picture)
                throw new DataException("Illegal id value: '$id'");

            $dsturl = $picture->published ? "img.php?cur=-1" : "manpic.php?cur=-1";

            findref($id, $desc, $sql, $refs);

            exec_sql("DELETE FROM {$db_prefix}bibleref WHERE picid=$id");
            exec_sql("UPDATE {$db_prefix}photos SET description='$desc' WHERE id=$id");
            if (!empty($sql))
                exec_sql("INSERT INTO {$db_prefix}bibleref (bookid,chapter,verse_low,verse_high,picid) VALUES " . substr($sql,1));
            else
                header("Location: $dsturl");
        }
        else
            throw new DataException("Missing picture id");
    }
    else if (isset($_GET['id'])) {
        $id = $_GET['id'];
        if (!is_numeric($id))
            throw new DataException("Illegal id value: '$id'");

        $res = exec_sql("SELECT * FROM {$db_prefix}photos WHERE id=$id");
        $picture = mysqli_fetch_object($res);

        if (!$picture)
            throw new DataException("Illegal id value: '$id'");

        $imgfile = ($picture->published ? $dirname_600 : $unpub_dirname_600) . '/' . $picture->filename;

        $old_x = $picture->width;
        $old_y = $picture->height;
 
        $scale_factor = 300/max($old_x,$old_y);
 
        $new_x = round($old_x * $scale_factor);
        $new_y = round($old_y * $scale_factor);

        if ($picture->published)
            $srcurl = "img.php?cur=-1";
        else
            $srcurl = "manpic.php?cur=-1";
    }
    else
            throw new DataException("Missing picture id");
}
catch (DataException $e) {
    $error = $e->getMessage();
}
?>


<?php
/////////////////////////////////////////////////////////////////////////////
// Start body text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>
      <?php if (isset($error)): ?>
        <p class="error">Error: <?=$error?></p>
      <?php elseif (!empty($refs)): ?>
        <h1>The following Bible references were found:</h1>

        <p><?= substr($refs,2); ?></p>

        <input onclick="location='<?= $dsturl ?>'" type="button" value="Continue" />

      <?php else: ?>    
        <img alt="" src="<?= $imgfile ?>" height="<?= $new_y ?>" width="<?= $new_x ?>" />
        <p>Filename: <?= $picture->filename ?></p>
   
        <form method="post" action="editdesc.php" accept-charset="UTF-8"> 
          <p>     
            <textarea name="content" cols="50" rows="15"><?= htmlspecialchars($picture->description) ?></textarea>
            <input type="submit" name="submit" value="Save" />
            <input type="hidden" name="id" value="<?= $id ?>" />
            <input onclick="location='<?= $srcurl ?>'" type="button" value="Cancel" />
          </p>
        </form>
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

    $(function(){
        tinyMCE.init({
            theme : "advanced",
            plugins : "autolink,lists,advlink,inlinepopups,preview,paste,directionality,visualchars,nonbreaking,wordcount,advlist,autosave",
            theme_advanced_buttons1 : "bold,italic,underline,|,bullist,numlist,sub,sup,|,charmap,|,undo,redo,|,link,unlink",
            theme_advanced_buttons2 : "pastetext,pasteword,selectall,|,ltr,rtl,|,nonbreaking,|,removeformat,cleanup,code,|,preview",
            theme_advanced_buttons3 : "",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : true,
            mode : "textareas",
            entities : "38,amp"   // Prevents HTML encoding of non-ASCII characters.
                    
            });
    });

<?php
$headerscript = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End header script
/////////////////////////////////////////////////////////////////////////////
?>


<?php

wrapme('Edit Description',
       $headerscript,
       null,
       $bodytext,
       array('tinymce/jscripts/tiny_mce/tiny_mce.js'));

?>
