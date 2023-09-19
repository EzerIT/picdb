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


must_be_user();
?>

<?php
/////////////////////////////////////////////////////////////////////////////
// Start body text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

  <h1>Upload Pictures</h1>
  <p>Click the &ldquo;Upload files&rdquo; button below to select files to upload. (If you are using
    Firefox or Chrome, you can also drag and drop pictures into the button.)</p>
  <p>When you have uploaded all your pictures, click &ldquo;Manage unpublished pictures&rdquo; in
    the menu on the left.</p>
  <div id="file-uploader">      
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

    $(function(){
            var uploader = new qq.FileUploader({
                  element: document.getElementById('file-uploader'),
                  action: 'valums_uploader.php',
                  allowedExtensions: ['jpg','png'],        
                  template: '<div class="qq-uploader">' + 
                            '<div class="qq-upload-drop-area ui-corner-all"><span>Drop files here to upload</span></div>' +
                            '<div class="qq-upload-button ui-corner-all">Upload files</div>' +
                            '<ul class="qq-upload-list"></ul>' + 
                            '</div>',

              });
        });

<?php
$headerscript = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End header script
/////////////////////////////////////////////////////////////////////////////
?>


<?php

wrapme('Upload Pictures',
       $headerscript,
       null,
       $bodytext,
       array('valums-file-uploader-b3b20b1/client/fileuploader.js'),
       array('style/fileuploader.css'));

?>

