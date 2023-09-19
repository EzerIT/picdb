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
require_once 'img.inc.php';

try {
    if (!isset($_GET['picno']) || !is_numeric($_GET['picno']))
        throw new DataException('Illegal picture number');

    $picno = $_GET['picno'];

    $res = exec_sql("SELECT * FROM {$db_prefix}photos WHERE pic_no=$picno AND published");
    $row = mysqli_fetch_object($res);

    if (!$row)
        throw new DataException('Illegal picture number');

    $allcats = find_allcats();

    if (substr($row->description,0,2)=='<p')
        $row->longdesc = $row->description;
    else 
        $row->longdesc = shtml('p',$row->description); // Make sure $row->longdesc is embedded in <p>..</p>

    $row->longdesc = replace_links($row->longdesc);

    $longdescs = array();

    $res2 = exec_sql("SELECT * FROM {$db_prefix}piccat WHERE picid=$row->id");
    while ($row2 = mysqli_fetch_object($res2)) {
        $thiscat = $allcats[$row2->catid];
        if ($thiscat->display) {
            $catval = find_cat($thiscat->values, is_null($row2->intval) ? $row2->stringval : $row2->intval);
            if ($catval) {
                if (isset($longdescs[$row2->catid]))
                    $longdescs[$row2->catid] .= '<br/>' . shtml('b',$thiscat->name . ': ') . $catval->name;
                else
                    $longdescs[$row2->catid] = '<br/>' . shtml('b',$thiscat->name . ': ') . $catval->name;
            }
        }
    }

    ksort($longdescs);
    foreach ($longdescs as $ld)
        $row->longdesc .= $ld;

    if (!is_null($row->date))
        $row->longdesc .= '<br/>' . shtml('b','Date taken: ') . substr($row->date,0,10);

    $res2 = exec_sql("SELECT name from {$db_prefix}authors WHERE $row->pic_no>=range_low AND $row->pic_no<=range_high");
    if ($row2 = mysqli_fetch_object($res2))
        $row->longdesc .= '<br/>'. shtml('b','Photographer: ') . $row2->name;
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
    <p class="error"><?= $error ?></p>
  <?php else: ?>
    <img alt="<?= $row->filename ?>" src="<?= "$dirname_600/$row->filename" ?>" />
    <div class="picdesc"><?= $row->longdesc ?></div>
  <?php endif; ?>

<?php
$bodytext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End body text
/////////////////////////////////////////////////////////////////////////////
?>

<?php

wrapme('View Picture',
       null,
       null,
       $bodytext,
       array(),
       array(),
       false);

?>
