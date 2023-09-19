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
require_once 'csvreader.inc.php';
require_once 'dataexception.inc.php';

must_be_user();

$catid = isset($_GET['category']) ? $_GET['category'] : 0;

$allcats = array();
$res = exec_sql("SELECT * FROM {$db_prefix}categories ORDER BY name");
while ($row = mysqli_fetch_object($res))
    $allcats[$row->id] = $row;


if (isset($_POST['action'])) {
    try {
        if (!isset($allcats[$catid]))
            throw new DataException("Illegal category number: $catid");

        $catobj = $allcats[$catid];

        switch ($_POST['action']) {
          case 'delete':
                $id = $_POST['id'];
                if (!is_numeric($id))
                    throw new DataException("Illegal id value: '$id'");

                exec_sql("DELETE FROM {$db_prefix}catval WHERE id=$id");
                break;

          case 'update':
                $id = $_POST['id'];
                if (!is_numeric($id))
                    throw new DataException("Illegal id value: '$id'");

                $name_en = my_escape_string(trim($_POST['name_en']));
                $name_da = my_escape_string(trim($_POST['name_da']));

                exec_sql("UPDATE {$db_prefix}catval SET name='$name_en', name_da='$name_da' WHERE id=$id");
                break;

          case 'add':
                $name_en = my_escape_string(trim($_POST['name_en']));
                $name_da = my_escape_string(trim($_POST['name_da']));
                $range = explode('-',$_POST['value']);

                if (count($range)>2)
                    throw new DataException("Illegal value {$_POST['value']}");

                $value_low = $range[0];

                if (count($range)==2)
                    $value_high = $range[1];
                else
                    $value_high = null;
        
                if ($catobj->isstring) {
                    $select =
                        "SELECT id FROM {$db_prefix}catval WHERE category=$catid AND stringval='$value_low' "
                        . (is_null($value_high) ? 'AND stringval_high IS NULL' : "AND stringval_high='$value_high'");
                    
                    $insert =
                        "INSERT INTO {$db_prefix}catval (category,stringval,stringval_high,name,name_da) "
                        . "VALUES ($catid,'$value_low',"
                        . (is_null($value_high) ? 'NULL,' : "'$value_high',")
                        . "'$name_en','$name_da')";
                }
                else {
                    if (!is_numeric($value_low))
                        throw new DataException("Illegal value $value_low");

                    if (!is_null($value_high) && !is_numeric($value_high))
                        throw new DataException("Illegal value $value_high");

                    $select =
                        "SELECT id FROM {$db_prefix}catval WHERE category=$catid AND intval=$value_low "
                        . (is_null($value_high) ? 'AND intval_high IS NULL' : "AND intval_high=$value_high");

                    $insert =
                        "INSERT INTO {$db_prefix}catval (category,intval,intval_high,name,name_da) "
                        . "VALUES ($catid,$value_low,"
                        . (is_null($value_high) ? 'NULL,' : "$value_high,")
                        . "'$name_en','$name_da')";
                }

                $res = exec_sql($select);

                if (mysqli_num_rows($res)>0)
                    throw new DataException("Value {$_POST['value']} is already in use");

                exec_sql($insert);
                break;
        }

    }
    catch (DataException $e) {
        $error_post = $e->getMessage();
    }
}



try {
    $uploaded = false;

    if (!is_numeric($catid) || $catid<0)
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
            $updates = array();
            $inserts = array();

            while ($data = $cs->read_csv(2,3)) {
                $range = explode('-',$data[0]);

                switch (count($range)) {
                  case 1:
                        $res = exec_sql("SELECT * FROM {$db_prefix}catval WHERE category=$catobj->id AND "
                                        . ($catobj->isstring
                                           ? "stringval='{$range[0]}' AND stringval_high IS NULL"
                                           : "intval={$range[0]} AND intval_high IS NULL"));
                        break;
                  case 2:
                        $res = exec_sql("SELECT * FROM {$db_prefix}catval WHERE category=$catobj->id AND "
                                        . ($catobj->isstring
                                           ? "stringval='{$range[0]}' AND stringval_high='{$range[1]}'"
                                           : "intval={$range[0]} AND intval_high={$range[1]}"));
                        break;
                  default:
                        throw new DataException('Error in file near text: "' . $cs->implode($data) . '"');
                }

                $row = mysqli_fetch_object($res);
                if ($row) {
                    if ($data[1]=="DELETE") {
                        $deletes[] = "Deleted item '{$data[0]}'";
                        exec_sql("DELETE FROM {$db_prefix}catval WHERE id=$row->id");
                    }
                    else {
                        switch (count($data)) {
                          case 2:
                                if ($data[1]!=$row->name) {
                                    $updates[] = "Changed item '{$data[0]}' from '{$row->name}' to '{$data[1]}'";
                                    exec_sql("UPDATE {$db_prefix}catval SET name='{$data[1]}' WHERE id=$row->id");
                                }
                                break;
                          case 3:
                                if ($data[1]!=$row->name || $data[2]!=$row->name_da) {
                                    $updates[] = "Changed item '{$data[0]}' from '{$row->name}'/'{$row->name_da}' to '{$data[1]}'/'{$data[2]}'";
                                    exec_sql("UPDATE {$db_prefix}catval SET name='{$data[1]}', name_da='{$data[2]}' WHERE id=$row->id");
                                }
                                break;
                        }
                    }
                }
                elseif ($data[1]!="DELETE") {
                    switch (count($data)) {
                      case 2:
                            $inserts[] = "Inserted item '$data[0]' with value '{$data[1]}'";
                            if ($catobj->isstring)
                                $res = exec_sql("INSERT INTO {$db_prefix}catval (category,stringval,stringval_high,name) "
                                                . "VALUES ($catobj->id,'{$range[0]}',"
                                                . (count($range)==2 ? "'{$range[1]}'" : 'NULL')
                                                . ",'{$data[1]}')");
                            else
                                $res = exec_sql("INSERT INTO {$db_prefix}catval (category,intval,intval_high,name) "
                                                . "VALUES ($catobj->id,{$range[0]},"
                                                . (count($range)==2 ? $range[1] : 'NULL')
                                                . ",'{$data[1]}')");
                            break;
                      case 3:
                            $inserts[] = "Inserted item '$data[0]' with value '{$data[1]}'/'{$data[2]}'";
                            if ($catobj->isstring)
                                $res = exec_sql("INSERT INTO {$db_prefix}catval (category,stringval,stringval_high,name,name_da) "
                                                . "VALUES ($catobj->id,'{$range[0]}',"
                                                . (count($range)==2 ? "'{$range[1]}'" : 'NULL')
                                                . ",'{$data[1]}','{$data[2]}')");
                            else
                                $res = exec_sql("INSERT INTO {$db_prefix}catval (category,intval,intval_high,name,name_da) "
                                                . "VALUES ($catobj->id,{$range[0]},"
                                                . (count($range)==2 ? $range[1] : 'NULL')
                                                . ",'{$data[1]}','{$data[2]}')");
                            break;
                    }
                }
            }
            $cs->close();
            $uploaded = true;
        }

        $allvals = array();
        $useval = $catobj->isstring ? 'stringval' : 'intval';
        $res = exec_sql("SELECT id, $useval as value, {$useval}_high as value_high, name, name_da "
                        . "FROM {$db_prefix}catval WHERE category=$catid "
                        . "ORDER BY value, IF(ISNULL(value_high),1,0), value_high;"); // Sorts null after non-null value_high
        while ($row = mysqli_fetch_object($res)) {
            if (is_null($row->value_high))
                $row->realvalue = $row->value;
            else
                $row->realvalue = $row->value . '-' . $row->value_high;
            if (is_null($row->name))
                $row->name = '';
            else
                $row->name = htmlspecialchars($row->name);
            if (is_null($row->name_da))
                $row->name_da = '';
            else
                $row->name_da = htmlspecialchars($row->name_da);
            $allvals[] = $row;
        }
    }
}
catch (DataException $e) {
    $error = $e->getMessage();
}

function replace_for_javascript($s) {
    return str_replace(array('\\',   '\''  ),   // Replace \ with \\
                       array('\\\\', '\\\''),   // Replace ' with \'
                       $s);
}

?>


<?php
/////////////////////////////////////////////////////////////////////////////
// Start help text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

<p>On this page you can modify the descriptions associated with each category value.</p>

<p>The picture database classifies pictures as belonging to certain categories. Within each category
a set of values exist, each with a description of the meaning of the category. For example, within
the category &ldquo;Place&rdquo; the value <i>183</i> refers to the city of Jericho. A value range
can also be defined. For example, with the category &ldquo;Place&rdquo; the values <i>1-599</i> all
refer to locations in Israel.</p>

<p>At the top of the page, there is a drop-down list of available categories. If you select a
category here, the page will display the values that are defined within that category.</p>

<p>Once you have selected a category, you can edit the values in two ways: Edit each item
individually, or upload a file containing category values. This is described below.</p>

<h2>Edit a Single Value</h2>

<p>To edit an existing value, click on the &ldquo;Edit&rdquo; link to the right of the value and
enter the new text.</p>

<p>To delete an existing value, click on the &ldquo;Delete&rdquo; link to the right of the
value.</p>

<p>To add a new value, click on the &ldquo;Add an item&rdquo; button and enter the value (or value
range) and the text.</p>

<h2>Upload a File with Values</h2>

<p>At the bottom of the page you can upload a file containing a set of values.</p>

<p>The file must be a plain text file. If it contains non-English characters, it must be in UTF-8
encoding. The content of the file must follow these rules:</p>

<ul>
<li>There must be one value (or value range) per line. The format must be similar to this:</li>
<li style="list-style-type:none"><pre>
   2002,&quot;Landscapes&quot;,&quot;Landskaber&quot;
   2003,&quot;DELETE&quot;
</pre></li>
<li style="list-style-type:none">The first item on each line is a value (or value range), the second
item is the English name for the value, the third item (if present) is the Danish name for the
value. See below for a description of &quot;DELETE&quot;.</li>
<li>There must be no space between the comma and the first quotation mark.</li>
<li>If the text is to contain a quotation mark, it must be doubled:</li>
<li style="list-style-type:none"><pre>
   2004,&quot;This is a &quot;&quot;strange&quot;&quot; value&quot;
</pre></li>
</ul>

<p>If a value listed in the file refers to a value that already exists in the database, the database
will be updated with the new text.</p>

<p>If a value listed in the file refers to a value that does not exists in the database, the value
will be added to the database.</p>

<p>If the text is simply the word DELETE, the associated value will be removed from the database.</p>


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
  <h1>Edit Categories</h1>

  <?php if (isset($error_post)): ?>
    <p class="error">Error when updating data: <?=$error_post?></p>
  <?php endif; ?>

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
    <?php if (count($deletes) + count($updates) + count($inserts) == 0): ?>
      <h2>No changes were made</h2>
    <?php else: ?>
      <h2>The following changes were made:</h2>
      <?php foreach ($deletes as $d): ?>
        <p class="narrow"><?= $d ?></p>
      <?php endforeach; ?>
      <?php foreach ($updates as $d): ?>
        <p class="narrow"><?= $d ?></p>
      <?php endforeach; ?>
      <?php foreach ($inserts as $d): ?>
        <p class="narrow"><?= $d ?></p>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (isset($allvals)): ?>
    <h2>Values for category '<?= $catobj->name ?>'</h2>

    <table class="type1">
      <tr><th>Value</th><th>Name</th><th>Danish name</th><th></th></tr>
      <?php foreach ($allvals as $val): ?>
        <tr>
          <td><?= $val->realvalue ?></td>
          <td class="left"><?= $val->name ?></td>
          <td class="left"><?= $val->name_da ?></td>
          <td>
            <!-- Note: The href="#" will normally cause the browser window to scroll to the top
                 when the link is pressed. By making the onclick functions return false, this is
                 avoided. -->
            <a onclick="editVal(<?= sprintf("%d,'%s','%s','%s'",
                                            $val->id,
                                            $val->realvalue,
                                            replace_for_javascript($val->name),
                                            replace_for_javascript($val->name_da)
                                            ) ?>); return false;" href="#">Edit</a>
            <a onclick="deleteVal(<?= $val->id ?>,'<?= $val->realvalue ?>'); return false;" href="#">Delete</a>
          </td>

        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <?php if ($catid>0): ?>
    <p><input class="makebutton" onclick="addVal()" type="button" value="Add an item" /></p>

    <div class="coloredbg ui-corner-all">
      <h2>Upload new values for category '<?= $catobj->name ?>'</h2>

      <form action="editcat.php?category=<?= $catid ?>" method="post" enctype="multipart/form-data">
        <p>
          <label for="file">Filename:</label>
          <input type="file" name="file" id="file" />
          <br/>
          <input class="makebutton" type="submit" name="submit" value="Submit" />
        </p>
      </form>
    </div>

  <?php endif; ?>

  <!-- Dialogs for this page follow -->

  <div id="dialog-update-form" style="display:none" title="Edit category">
    <form id="update-form" action="editcat.php?category=<?= $catid ?>" method="post">
        <table>
          <tr>
            <td>Value</td>
            <td id="update-value"></td>
          </tr>
          <tr>
            <td><label for="name_en">English name</label></td>
            <td><input type="text" name="name_en" id="update-name_en" size="50" class="text ui-widget-content ui-corner-all" /></td>
          </tr>
          <tr>
            <td><label for="name_da">Danish name</label></td>
            <td><input type="text" name="name_da" id="update-name_da" size="50" class="text ui-widget-content ui-corner-all" /></td>
          </tr>
        </table>
        <input type="hidden" name="id" id="update-id" />
        <input type="hidden" name="action" value="update"/>
    </form>
  </div>

  <div id="dialog-add-form" style="display:none" title="Edit category">
    <form id="add-form" action="editcat.php?category=<?= $catid ?>" method="post">
        <table>
          <tr>
            <td><label for="value">Value</label></td>
            <td><input type="text" name="value" id="value" class="text ui-widget-content ui-corner-all" /></td>
          </tr>
          <tr>
            <td><label for="name_en">English name</label></td>
            <td><input type="text" name="name_en" id="name_en" size="50" value="" class="text ui-widget-content ui-corner-all" /></td>
          </tr>
          <tr>
            <td><label for="name_da">Danish name</label></td>
            <td><input type="text" name="name_da" id="name_da" size="50" value="" class="text ui-widget-content ui-corner-all" /></td>
          </tr>
        </table>
        <input type="hidden" name="action" id="action" value="add"/>
    </form>
  </div>

  <div id="dialog-confirm" style="display:none" title="Delete category item">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
      Do you want to delete the category item for the value <span id="dialog-confirm-value"></span>?
    </p>
    <form id="delete-form" action="editcat.php?category=<?= $catid ?>" method="post">
      <input type="hidden" name="id" id="delete-id" />
      <input type="hidden" name="action" id="action" value="delete"/>
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


function editVal(id,realvalue,name_en,name_da) {
    $('#update-value').text(realvalue);
    $('#update-name_en').attr('value',name_en);
    $('#update-name_da').attr('value',name_da);
    $('#update-id').attr('value',id);
    $('#dialog-update-form').dialog('open');
}

function addVal() {
    $('#dialog-add-form').dialog('open');
}


function deleteVal(id,realvalue) {
    $('#delete-id').attr('value',id);
    $('#dialog-confirm-value').text(realvalue);
    $('#dialog-confirm').dialog('open');
}


$(function() {
    $('#category').change(function() {
        window.location = 'editcat.php?category=' + $(this).val();
    });

    $( '#dialog-update-form' ).dialog({
        autoOpen: false,
        width: 600,
        modal: true,
        buttons: {
            'Update category item': function() {
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
        width: 600,
        modal: true,
        buttons: {
            'Add category item': function() {
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

wrapme('Edit Categories',
       $headerscript,
       null,
       $bodytext);

?>
