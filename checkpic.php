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
require_once 'util.inc.php';
require_once 'database.inc.php';
require_once 'verifier.inc.php';

must_be_user();


$verifier = new Verifier(true);
$verifier->doVerify(isset($_POST['delete_bad']));

?>


<?php
/////////////////////////////////////////////////////////////////////////////
// Start help text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

<p>When you open this page, the system will check that there are no inconsistencies in the picture
database. If everything is fine, the text &ldquo;No problems found&rdquo; will be displayed.
Ideally, this should always happen.</p>

<p>If the system detects inconsistencies, it will try to correct the problems. The inconsistencies
may, for example, be missing or extraneous thumbnails, or picture files that are not in the
database.</p>

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

    <form action="checkpic.php" method="post">
      <input class="makebutton" type="submit" name="delete_bad" value="Click here to delete these files" />
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

      <?php if (count($verifier->dup_picno)>0): ?>
         <h2>
           Warning: Duplicate picture numbers:
           <?php foreach ($verifier->dup_picno as $no => $count): ?>
             <?= $no ?>
           <?php endforeach; ?>
         </h2>
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
      <input id="proceed" class="invisible makebutton" onclick="location='checkpic.php'" type="button" value="Click here to check again" />
    <?php else: // $modifications ?>
      <h1>No problems found</h1>
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
      });

<?php
$headerscript = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End header script
/////////////////////////////////////////////////////////////////////////////
?>


<?php

wrapme('Check Published Pictures',
       $headerscript,
       null,
       $bodytext);

?>
