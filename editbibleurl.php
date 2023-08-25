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
require_once 'database.inc';
require_once 'findref.inc';
require_once 'csvreader.inc';
require_once 'dataexception.inc';

must_be_user();

$icons = array(
    'u' => 'images/u.png',
    'v' => 'images/v.png',
    'd' => 'images/d.png'
    );

function verifyPOST($arg) {
    if (!isset($_POST[$arg]))
        throw new DataException("System error: Missing POST data '$id'");
    return my_escape_string(trim($_POST[$arg]));
}

class DeleteAction {
    private $url;
    private $type;

    function __construct($urlname, $typename) {
        $this->url = verifyPOST($urlname);
        $this->type = verifyPOST($typename);
    }

    function execute() {
        global $db_prefix;
        exec_sql("DELETE FROM {$db_prefix}bibleurl WHERE url='$this->url' AND type='$this->type'");
    }
}


class InsertAction {
    private $url;
    private $type;
    private $refs;
    private $sql;

    function __construct($urlname, $typename, $refsname) {
        $this->url = verifyPOST($urlname);
        $this->type = verifyPOST($typename);
        $this->refs = verifyPOST($refsname);

        $this->refs = str_replace(utf8_encode(chr(0xa0)), ' ', $this->refs); // Replace &nbsp; by regular space

        findref("'$this->url','$this->type'", $this->refs, $this->sql, $brefs); // $brefs is not used

        if (empty($this->sql))
            throw new DataException("No references found in '$this->refs'");
        $this->sql = substr($this->sql,1); // Strip initial comma
    }

    function execute() {
        global $db_prefix;
        exec_sql("INSERT INTO {$db_prefix}bibleurl (bookid,chapter,verse_low,verse_high,url,type) VALUES $this->sql");
    }
}


if (isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
          case 'delete':
                $deleteaction = new DeleteAction('url','type');
                $deleteaction->execute();
                break;
                
          case 'update':
                $deleteaction = new DeleteAction('oldurl','oldtype');
                $insertaction = new InsertAction('url','type','refs');
                $deleteaction->execute();
                $insertaction->execute();
                break;


          case 'add':
                $insertaction = new InsertAction('url','type','refs');
                $insertaction->execute();
                break;
        }
    }
    catch (DataException $e) {
        $error_post = $e->getMessage();
    }
}

function iconstring($type) {
    global $icons;
    if (array_key_exists($type,$icons))
        return sprintf('<img src="%s" alt="%s" />', $icons[$type], $type);
    else
        return $type;
}



$allurls = array();

$res = exec_sql("SELECT DISTINCT url,type FROM {$db_prefix}bibleurl ORDER BY url");
while ($row = mysqli_fetch_object($res)) {
    $allurls[] = $row;
    assert(strlen($row->type)==1);
}


$allrefs = array();
foreach ($allurls as $url) {
    $res = exec_sql("SELECT internal,chapter,verse_low  FROM {$db_prefix}bibleurl, {$db_prefix}biblebooks as bb "
                    . "WHERE url='$url->url' AND type='$url->type' AND bookid=bb.id "
                    . "ORDER BY bookid,chapter,verse_low");
    $values = array();
    while ($row = mysqli_fetch_object($res))
        $values[] = str_replace(' ', '&nbsp;', toEnglish($row->internal, $row->chapter, $row->verse_low)); // Use &nbsp; to prevent line breaks in references

    $allrefs[$url->type . $url->url] = implode('; ', $values);
}        

?>

<?php
/////////////////////////////////////////////////////////////////////////////
// Start help text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

<p>This feature does not relate directly to the picture database. It is used by PLOTLearner to
assign hyperlinks to specific Bible verses.</p>

<p>The system maintains a set of URLs and associated icons and Bible verses. The icons can be used
to identify the URL type. For example, a &ldquo;V&rdquo; icon identifies a video clip, a
&ldquo;D&rdquo; icon identifies a document, and a &ldquo;U&rdquo; icon identifies a generic URL
(that is, anything that does not fit into the other categories).</p>

<p>Use this page to add, edit, or delete the URLs and icons associated with specific Bible
  verses.</p>

<p>The Bible verses should be written in the format &ldquo;Gen.&nbsp;3:8&rdquo; to identify
  Genesis chapter 3 verse 8. The legal Bible book abbreviations are:</p>

<blockquote>
  <p><?= str_replace( array(" ",      ","),
                      array("&nbsp;", ", "),
                      implode(",",$book2EnById) ) ?></p>
</blockquote>

<p>A Danish Bible reference format (for example, &ldquo;1&nbsp;Mos&nbsp;3,8&rdquo;) is also
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
  <h1>Edit Bible Reference URLs </h1>

  <?php if (isset($error_post)): ?>
    <p class="error">Error when updating data: <?=$error_post?></p>
  <?php endif; ?>

  <?php if (isset($error)): ?>
    <p class="error">Error: <?=$error?></p>
  <?php endif; ?>

  <table class="type1">
    <tr><th>URL</th><th>Icon</th><th>References</th><th></th></tr>
    <?php foreach ($allrefs as $ref => $bib): ?>
      <?php $escapedref = htmlspecialchars(substr($ref,1)); ?>
      <tr>
        <td class="left"><a href="<?= $escapedref ?>" target="_blank"><?= $escapedref ?></a></td>
        <td><?= iconstring($ref[0]) ?></td>
        <td class="left"><?= $bib ?></td>
        <td>
            <!-- Note: The href="#" will normally cause the browser window to scroll to the top
                 when the link is pressed. By making the onclick functions return false, this is
                 avoided. -->
          <a onclick="editVal(<?= sprintf("'%s','%s','%s'",$escapedref,$ref[0],$bib) ?>);return false;" href="#">Edit</a>
          <a onclick="deleteVal(<?= sprintf("'%s','%s'",$escapedref,$ref[0]) ?>);return false;" href="#">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><input class="makebutton" onclick="addVal()" type="button" value="Add an item" /></p>


  <!-- Dialogs for this page follow -->

  <div id="dialog-update-form" style="display:none" title="Edit Bible Reference URL">
    <form id="update-form" action="editbibleurl.php" method="post">
        <table>
          <tr>
            <td>URL</td>
            <td><input type="text" name="url" id="update-url" size="65" class="text ui-widget-content ui-corner-all" /></td>
          </tr>
          <tr>
            <td>Icon</td>
            <td>
              <?php foreach ($icons as $t => $i): ?>
                <input type="radio" name="type" id="update-type-<?= $t ?>" value="<?= $t ?>" /><?= iconstring($t) ?>
              <?php endforeach; ?>
            </td>
          </tr>
          <tr>
            <td>References</td>
            <td><input type="text" name="refs" id="update-refs" size="65" class="text ui-widget-content ui-corner-all" /></td>
          </tr>
        </table>
        <input type="hidden" name="oldurl" id="update-old-url" />
        <input type="hidden" name="oldtype" id="update-old-type" />
        <input type="hidden" name="action" value="update"/>
    </form>
  </div>

  <div id="dialog-add-form" style="display:none" title="Add Bible Reference URL">
    <form id="add-form" action="editbibleurl.php" method="post">
        <table>
          <tr>
            <td>URL</td>
            <td><input type="text" name="url" id="add-url" size="65" class="text ui-widget-content ui-corner-all" /></td>
          </tr>
          <tr>
            <td>Icon</td>
            <td>
              <?php foreach ($icons as $t => $i): ?>
                <input type="radio" name="type" id="add-type-<?= $t ?>" value="<?= $t ?>" /><?= iconstring($t) ?>
              <?php endforeach; ?>
            </td>
          </tr>
          <tr>
            <td>References</td>
            <td><input type="text" name="refs" id="add-refs" size="65" class="text ui-widget-content ui-corner-all" /></td>
          </tr>
        </table>
        <input type="hidden" name="action" value="add"/>
    </form>
  </div>

  <div id="dialog-confirm" style="display:none" title="Delete Bible Reference URL">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
      Do you want to delete these references to<br/><span id="dialog-confirm-value"></span>?
    </p>
    <form id="delete-form" action="editbibleurl.php" method="post">
      <input type="hidden" name="url" id="delete-url" />
      <input type="hidden" name="type" id="delete-type" />
      <input type="hidden" name="action" value="delete"/>
    </form>
  </div>

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


function editVal(url,type,refs) {
    $('#update-url').attr('value',url);
    $('#update-type-' + type).attr('checked','checked');
    $('#update-refs').attr('value',refs);
    $('#update-old-url').attr('value',url);
    $('#update-old-type').attr('value',type);
    $('#dialog-update-form').dialog('open');
}

function addVal() {
    $('#add-type-u').attr('checked','checked');
    $('#dialog-add-form').dialog('open');
}

function deleteVal(url,type) {
    $('#delete-url').attr('value',url);
    $('#delete-type').attr('value',type);
    $('#dialog-confirm-value').text(url);
    $('#dialog-confirm').dialog('open');
}

 
$(function() {
    $( '#dialog-update-form' ).dialog({
        autoOpen: false,
        width: 700,
        modal: true,
        buttons: {
            'Update references': function() {
               $('#update-form').submit();
                $( this ).dialog( 'close' );
            },
            Cancel: function() {
                $( this ).dialog( 'close' );
            }
        },
        close: function() {
            allFields.val( '' ).removeClass( 'ui-state-error' );
        }
    });

    $( '#dialog-add-form' ).dialog({
        autoOpen: false,
        width: 700,
        modal: true,
        buttons: {
            'Add references': function() {
               $('#add-form').submit();
                $( this ).dialog( 'close' );
            },
            Cancel: function() {
                $( this ).dialog( 'close' );
            }
        },
        close: function() {
            allFields.val( '' ).removeClass( 'ui-state-error' );
        }
    });

    $( "#dialog-confirm" ).dialog({
        autoOpen: false,
        resizable: false,
        width: 500,
        modal: true,
        buttons: {
            "Yes": function() {
                $('#delete-form').submit();
                $( this ).dialog( "close" );
            },
            "No": function() {
                $( this ).dialog( "close" );
            }
        }
    });

});

<?php
$headerscript = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End header script
/////////////////////////////////////////////////////////////////////////////
?>


<?php

wrapme('Edit Bible Reference URLs',
       $headerscript,
       null,
       $bodytext);

?>
