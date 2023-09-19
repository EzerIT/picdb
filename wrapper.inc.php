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


// This file contains the basic function for creating output

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once 'adminmenu.inc.php';
require_once 'dataexception.inc.php';


$phpscript = $_SERVER['PHP_SELF'];
$last_comp = strrchr($phpscript, "/");
$last_comp = $last_comp ? substr($last_comp, 1) : $phpscript;

$credentials = new Credentials(); // Calls session_start


function must_be_user() {
    global $credentials;

    if (!$credentials->is_user()) {
        header("Location: login.php");
        exit;
    }
}

function must_be_admin() {
    global $credentials;

    if (!$credentials->is_user()) {
        header("Location: login.php");
        exit;
    }
    if (!$credentials->is_admin())
        throw new DataException('You are do not have administrator rights.');
}

function must_be_admin_or_me($userid) {
    global $credentials;

    if (!$credentials->is_user()) {
        header("Location: login.php");
        exit;
    }
    if (!$credentials->is_admin_or_me($userid))
        throw new DataException('You are do not have administrator rights.');
}



function wrapme($title, $headerscript, $leftbox, $bodytext, $include_js=array(), $include_css=array(), $show_menu=true) {
    // header("Cache-Control: no-cache, must-revalidate"); TODO: Do we need this?

    global $credentials;
    global $last_comp;
    global $doctext;
    global $backgroundtext;
    global $licencetext;

    $picdb_stat = stat('style/picdb.less'); // We need this to modify the href attribute on picbdb.less. Otherwise
                                            // Internet Explorer will not reload the style sheet, even if it has changed.

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en" xml:lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?= $title ?></title>

  <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
  <script type="text/javascript" src="jquery-ui-1.8.21.custom/js/jquery-ui-1.8.21.custom.min.js"></script>
  <?php foreach ($include_js as $ijs): ?>
    <script type="text/javascript" src="<?= $ijs ?>"></script>
  <?php endforeach; ?>

  <link type="text/css" href="jquery-ui-1.8.21.custom/css/sunny/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
  <?php foreach ($include_css as $icss): ?>
    <link type="text/css" href="<?= $icss ?>" rel="stylesheet" />
  <?php endforeach; ?>

  <link rel="stylesheet/less" type="text/css" href="style/picdb.less?stat=<?= $picdb_stat[9] ?>" /><!-- ?stat=... is required to trick MSIE into reloading when picdb.less changes-->
  <script type="text/javascript" src="js/less-1.3.0.min.js"></script> <!-- Must come after the LESS stylesheets -->

  <script type="text/javascript">//<![CDATA[
      $(function() {
          $(".makebutton").button();
          $(".helpbutton").button({ icons: {primary:'ui-icon-help'}});
          $(".backgroundbutton").button(/*{ icons: {primary:'ui-icon-help'}}*/);
          $(".licencebutton").button(/*{ icons: {primary:'ui-icon-help'}}*/);
          $("#generic-dialog-confirm").dialog({
              autoOpen: false,
              resizable: false,
              modal: true,
              buttons: {
                  "Yes": function() {
                      location = $("#generic-destination").text();
                      $( this ).dialog( "close" );
                  },
                  "No": function() {
                      $( this ).dialog( "close" );
                  }
              }
          });


          <?php if (isset($doctext)): ?>
             $( '#helpdiv' ).dialog({
                        autoOpen: false,
                        position: 'center',
                        width: 600,
                        height: 400,
                    });
          <?php endif; ?>

          <?php if (isset($backgroundtext)): ?>
             $( '#backgrounddiv' ).dialog({
                        autoOpen: false,
                        position: 'center',
                        width: 600,
                        height: 400,
                    });
          <?php endif; ?>

          <?php if (isset($licencetext)): ?>
             var addr1 = "jb";
             var addr4 = "du";
             var addr3 = "bi.e";
             var addr2 = "k@d";
             var maila = addr1+addr2+addr3+addr4;
             var string6 = "</a>";
             var string4 = "ilto:";
             var string3 = "<a href=\"ma";
             var string5 = "\">";
              
             $('#jbkmail').html(string3+string4+maila+string5+maila+string6);

             $( '#licencediv' ).dialog({
                        autoOpen: false,
                        position: 'center',
                        width: 600,
                        height: 400,
                    });
          <?php endif; ?>
      });

      function toggleExtra(target,icon) {
          $(target).toggle('blind', null, 250);
          $(icon).toggleClass('ui-icon-plus').toggleClass('ui-icon-minus');
      }

      function genericConfirm(dialogtitle,dialogtext,destination) {
          $('#generic-dialog-confirm').dialog('option', 'title', dialogtitle);
          $('#generic-confirm-text').text(dialogtext);
          $('#generic-destination').text(destination);
          $('#generic-dialog-confirm').dialog('open');
      }

      <?php if (!is_null($headerscript)): ?>
        <?= $headerscript ?>
      <?php endif; ?>
  //]]></script>

</head>

<body>
  <!--Top banner-->
  <div id="top">
    <div id="intopl">
      <a href="https://resources.learner.bible" onclick="window.open(this.href,'_blank');return false;"><img src="images/logo.png" alt="logo"/></a>
    </div>
    <div id="intop" class="ui-corner-all">
      <a href="img.php">EuroPLOT Resources</a>
    </div>
  </div>
     
  <!--Everything below the top banner-->
  <div id="center">
     
    <!--The left column-->
    <div id="left">
      <?php if (!is_null($leftbox)): ?>
        <?= $leftbox ?>
      <?php endif; ?>

      <?php if ($show_menu): ?>
        <?php display_admin_menu($credentials); ?>
      <?php endif; ?>

      <?php if (isset($doctext)): ?>
        <!-- Note: The href="#" will normally cause the browser window to scroll to the top
             when the link is pressed. By making the onclick functions return false, this is
             avoided. -->
        <p><a class="helpbutton" href="#" onclick="$('#helpdiv').dialog('open');return false;">Help</a></p>
      <?php endif; ?>

      <?php if (isset($backgroundtext)): ?>
        <!-- Note: The href="#" will normally cause the browser window to scroll to the top
             when the link is pressed. By making the onclick functions return false, this is
             avoided. -->
        <p><a class="backgroundbutton" href="#" onclick="$('#backgrounddiv').dialog('open');return false;">Background</a></p>
      <?php endif; ?>

      <?php if (isset($licencetext)): ?>
        <!-- Note: The href="#" will normally cause the browser window to scroll to the top
             when the link is pressed. By making the onclick functions return false, this is
             avoided. -->
        <p><a class="licencebutton" href="#" onclick="$('#licencediv').dialog('open');return false;">Copyright and licence</a></p>
      <?php endif; ?>

      <?php if ($last_comp=="img.php" || $last_comp=="manpic.php"): ?>
        <div style="font-size: 8pt; margin-top: 50px;">
          <p>This web page uses the jQuery/lightbox tool &ndash;
            <a class="newwindow" href="http://leandrovieira.com/projects/jquery/lightbox">http://leandrovieira.com/projects/jquery/lightbox</a><br/>
            Copyright © 2008 Leandro Vieira Pinho</br>
          License <a class="newwindow" href="http://creativecommons.org/licenses/by-sa/2.5/br/deed.en_US">CCAttribution-ShareAlike 2.5 Brazil</a>
          </p>
        </div>    
      <?php endif; ?>

    </div>
       
    <div id="right">
      <?= $bodytext ?>

      <!-- Generic confirmation dialog -->
      <div id="generic-dialog-confirm" style="display:none">
        <p>
          <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
          <span id="generic-confirm-text"></span>
          <span id="generic-destination" style="display:none"></span>
        </p>
      </div>

      <?php if (isset($doctext)): ?>
        <div id="helpdiv" style="display:none" title="Help">
          <?= $doctext ?>
        </div>
      <?php endif; ?>

      <?php if (isset($backgroundtext)): ?>
        <div id="backgrounddiv" style="display:none" title="Background">
          <?= $backgroundtext ?>
        </div>
      <?php endif; ?>

      <?php if (isset($licencetext)): ?>
        <div id="licencediv" style="display:none" title="Licence">
          <?= $licencetext ?>
        </div>
      <?php endif; ?>

    </div>

  </div>
</body>
</html>

<?php
}
?>
