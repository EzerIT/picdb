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
require_once 'html.inc';
require_once 'util.inc';
require_once 'database.inc';
require_once 'verifier.inc';
require_once 'img.inc';

must_be_user();

$max_per_line = 5;
$max_per_page = $max_per_line * 5;


$verifier = new Verifier(false);
$verifier->doVerify(isset($_POST['delete_bad']));

if (!$verifier->modifications) {
    if (!isset($_GET['cur'])) {
        // Look for photos

        $allcats = find_allcats();

        $res = exec_sql("SELECT id FROM {$db_prefix}photos WHERE NOT published");
        $num_pics = mysqli_num_rows($res);

        $allids = array();
        while ($row = mysqli_fetch_object($res))
            $allids[] = $row->id;

        $cur = 0;

        $_SESSION['allids'] = $allids;
        $_SESSION['num_pics'] = $num_pics;
        $_SESSION['allcats'] = $allcats;
        $_SESSION['get'] = $_GET;
        $_SESSION['cur'] = $cur;
    }
    else {
        // We are at a (possibly new) page for a previous search

        if (is_numeric($_GET['cur']) && $_GET['cur']!=-1) {
            $cur = $_GET['cur'];
            $_SESSION['cur'] = $cur;
        }
        else
            $cur = $_SESSION['cur'];

        $allids = $_SESSION['allids'];
        $num_pics = $_SESSION['num_pics'];
        $allcats = $_SESSION['allcats'];
        $_GET = $_SESSION['get'];
    }

    $allpics = find_pics($allids, $allcats, $num_pics, $cur, $max_per_page);
}

function extrafun($thispic, $dirbig, $dir600, $dir160, $published)
{
    if (!$thispic->published) {
        return shtml_class('div', 'link',
                           shtml_a("editdesc.php?id=$thispic->id", 'Edit')
                           . ' ' 
                           . shtml_a("publish.php?id=$thispic->id",'Publish')
                           . ' '
                           . shtml_attr('a',
                                        "onclick=\"genericConfirm('Delete file',"
                                        . "'Delete the file $thispic->filename?',"
                                        . "'delete.php?id=$thispic->id');return false\" "
                                        . "href=\"#\"",
                                        'Delete'));
    }
    else
        return '';
}    
?>

<?php
/////////////////////////////////////////////////////////////////////////////
// Start help text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

<p>When a picture is uploaded to this site, it is marked as being &ldquo;unpublished.&rdquo; On this
  page you can mange unpublished pictures.</p>

<p>When you open this page, the system will perform a consistency check on the unpublished pictures.
  The system will, for example, generate any missing thumbnails. Once this has been completed, you
  have the following options:</p>

<p>Click on the + in the lower left hand corner of a picture frame. This will give you access to
  three links and show the file name of the picture.</p>

<p>The available links are:</p>
<ul>
<li><b>Edit.</b> Enables you to edit the description of the picture.</li>
<li><b>Publish.</b> Publishes the picture.</li>
<li><b>Delete.</b> Deletes the picture from the database.</li>
</ul>

<p>If there is more than one unpublished picture, you will see two buttons above the pictures
  allowing you to publish or delete all pictures simultaneously.</p>


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

  <?php if (count($verifier->to_remove_big)>0): ?>
    <h1>The following files have fatal errors and should be deleted:</h1>
    <table class="type1">
      <tr><th>Filename</th><th>Error</th></tr>
      <?php foreach ($verifier->to_remove_big as $name => $fat): ?>
        <tr><td><?=$name?></td><td><?=$fat->fatal_error?></td></tr>
      <?php endforeach; ?>
    </table>

    <form action="manpic.php" method="post">
      <input type="submit" class="makebutton" name="delete_bad" value="Click here to delete these files" />
    </form>

    <?php else: // count($verifier->to_remove_big)>0 ?>

    <?php if ($verifier->modifications): ?>
      <h1>A number of modifications were made to your files:</h1>

      <?php if (count($verifier->removed_big)>0): ?>
        <h2>Deleted files:</h2>
        <table class="type1">
          <tr><th>Filename</th></tr>
          <?php foreach ($verifier->removed_big as $rem): ?>
            <tr><td><?= $rem ?></td></tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>

      <?php if (count($verifier->renamed_big)>0): ?>
        <h2>Renamed files:</h2>
        <table class="type1">
          <tr><th>Old name</th><th>New name</th></tr>
          <?php foreach ($verifier->renamed_big as $old => $new): ?>
            <tr><td><?= $old ?></td><td><?= $new ?></td></tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>

      <?php if (count($verifier->added_db)>0): ?>
         <h2>Added <?= count($verifier->added_db) ?> pictures to database.</h2>
      <?php endif; ?>

      <?php if (count($verifier->removed_db)>0): ?>
         <h2>Removed <?= count($verifier->removed_db) ?> pictures from database.</h2>
      <?php endif; ?>

      <?php if ($verifier->deleted_rows>0): ?>
         <h2>Removed <?= $verifier->deleted_rows ?> unused entries from database.</h2>
      <?php endif; ?>

      <?php if (count($verifier->removed_160)>0): ?>
         <h2>Removed <?= count($verifier->removed_160) ?> thumbnails.</h2>
      <?php endif; ?>

      <?php if (count($verifier->removed_600)>0): ?>
         <h2>Removed <?= count($verifier->removed_600) ?> small pictures.</h2>
      <?php endif; ?>

      <?php if (count($verifier->to_add_160)+count($verifier->to_add_600) > 0): ?>
        <div id="result"></div>
        <?php if (count($verifier->to_add_160)>0): ?>
          <h2 class="addedmini invisible">Added <?= count($verifier->to_add_160) ?> thumbnails.</h2>
        <?php endif; ?>
        <?php if (count($verifier->to_add_600)>0): ?>
          <h2 class="addedmini invisible">Added <?= count($verifier->to_add_600) ?> small pictures.</h2>
        <?php endif; ?>
      <?php endif; ?>
      <input id="proceed" class="invisible makebutton" onclick="location='manpic.php'" type="button" value="Click here to proceed" />
    <?php else: // $modifications ?>

      <?php if ($num_pics==0): ?>
        <h1>No pictures found</h1>
      <?php else: ?>
        <?= mk_header($allpics, $num_pics, $max_per_page) ?>
        <?= mk_pageselector($num_pics, $max_per_page, $cur, 'manpic.php') ?>
 
        <div class="clearfloats"></div>
 
        <?php if ($num_pics>1): ?>
          <p><a class="makebutton" href="publishall.php?pub=1">Publish <?= $num_pics>2 ? "all $num_pics" : 'both' ?> pictures</a></p>

          <p>
            <a class="makebutton" onclick="genericConfirm('Delete all','Delete all unpublished pictures?','deleteall.php');return false" href="#">
              Delete <?= $num_pics>2 ? "all $num_pics" : 'both' ?> pictures
            </a>
          </p>
        <?php endif; ?>

        <?php show_all_pics($allpics, $max_per_line, $unpub_dirname_big, $unpub_dirname_600, $unpub_dirname_160, 0, 'extrafun'); ?>
      <?php endif; // if num_pics==0 ?>
    <?php endif; // $verifier->modifications ?>
<?php endif; // count($verifier->to_remove_big)>0 ?>

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

  action_urls = [
  
  <?php foreach ($verifier->allactions as $action): ?>
    '<?=$action?>',
  <?php endforeach; ?>
  
  undefined]; // End of action list
  
  // Ajax functions
  var errfun = 
      function() {
          updatepage("<p>ERROR - cannot execute request <code>ajax/" + action_urls[auix-1] + "</code></p>");
      };
  
  var reload =
      function(data) {
          if (data.substr(0,2)=='OK') {
              updatepage("<p>Generating miniature " + (parseInt(data.substr(2),10)+1)
                         + " of <?= count($verifier->to_add_160)+count($verifier->to_add_600) ?>. Please wait...</p>");
              if (action_urls[auix]!=undefined) {
                  $.get("ajax/" + action_urls[auix++], reload).error(errfun);
              }
              else {
                  $('#result').addClass('invisible');
                  $('.addedmini').removeClass('invisible');
                  $('#proceed').removeClass('invisible');
              }
          }
          else
              updatepage("<p>" + xmlHttpReq.responseText + "</p>");
      };
  
  function updatepage(str) {
      $('#result').show().html(str);
  }
 
  function generate() {
      auix = 0;
      if (action_urls[auix]!=undefined) {
          updatepage("<p>Generating miniature 1 of <?= count($verifier->to_add_160)+count($verifier->to_add_600) ?>. Please wait...</p>");
          $.get("ajax/" + action_urls[auix++], reload).error(errfun);
      }
      else
          $('#proceed').removeClass('invisible');
  }
 
  $(function(){
          generate();
          $('.img1').lightBox();
      });

<?php
$headerscript = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End header script
/////////////////////////////////////////////////////////////////////////////
?>


<?php

wrapme('Manage Unpublished Pictures',
       $headerscript,
       null,
       $bodytext,
       array('jquery-lightbox-0.5/js/jquery.lightbox-0.5.min.js'),
       array('jquery-lightbox-0.5/css/jquery.lightbox-0.5.css'));

?>
