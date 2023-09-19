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
require_once 'util.inc';
require_once 'html.inc';
require_once 'database.inc';
require_once 'dataexception.inc';
require_once 'img.inc';

$max_per_line = 5;
$max_per_page = $max_per_line * 5;

try {
    $bibrefs = array();

    $res = exec_sql("SELECT id,english_name FROM {$db_prefix}biblebooks ORDER BY id");
    while ($row=mysqli_fetch_object($res))
        $bibrefs[$row->id] = $row->english_name;

    if (!isset($_GET['cur']) || !isset($_SESSION['allids'])) { // Assume important $_SESSION information is not set
        // Look for photos

        $allcats = find_allcats(true);

        $table_alias = 'a0';
        foreach ($allcats as $catid => $thiscat) {
            if (isset($_GET[$thiscat->abb]) && $_GET[$thiscat->abb]!=='noval') {
                if ($thiscat->isstring) {
                    $value = my_escape_string($_GET[$thiscat->abb]);
                    $range = explode('-',$value);
                    if (count($range)==1)
                        $single = "(SELECT picid FROM {$db_prefix}piccat WHERE catid=$catid AND stringval='{$range[0]}')";
                    else
                        $single = "(SELECT picid FROM {$db_prefix}piccat WHERE catid=$catid AND stringval BETWEEN '{$range[0]}' AND '{$range[1]}')";
                }
                else {
                    $value = $_GET[$thiscat->abb];
                    $range = explode('-',$value);
                    if (!is_numeric($range[0]) || (count($range)>1 && !is_numeric($range[1])))
                        throw new DataException("Illegal category value: '$value'");
                    if (count($range)==1)
                        $single = "(SELECT picid FROM {$db_prefix}piccat WHERE catid=$catid AND intval={$range[0]})";
                    else
                        $single = "(SELECT picid FROM {$db_prefix}piccat WHERE catid=$catid AND intval BETWEEN {$range[0]} AND {$range[1]})";
                }
                if (!isset($fromstring))
                    $fromstring = "$single AS $table_alias";
                else 
                    $fromstring = "$fromstring INNER JOIN $single AS $table_alias USING (picid)";
                ++$table_alias;
            }
            else
                $_GET[$thiscat->abb] = 'noval';
        }


        if (!isset($fromstring))
            $sql = "SELECT id FROM {$db_prefix}photos WHERE published";
        else
            $sql = "SELECT id FROM {$db_prefix}photos WHERE published AND id IN (SELECT a0.picid FROM $fromstring)";

        if (isset($_GET['book']) && $_GET['book']!=='noval' 
            && isset($_GET['chapter']) && isset($_GET['verse'])) {
            $bookid = $_GET['book'];
            $chap = trim($_GET['chapter']);
            $verse = trim($_GET['verse']);
            if (is_numeric($bookid) &&
                (is_numeric($chap) || (empty($chap) && empty($verse))) &&
                (is_numeric($verse) || empty($verse))) {
                $sql .= " AND id in (SELECT picid FROM {$db_prefix}bibleref WHERE bookid=$bookid";
                if (is_numeric($chap))
                    $sql .= " AND chapter=$chap";
                if (is_numeric($verse))
                    $sql .= " AND $verse>=verse_low AND $verse<=verse_high";
                $sql .= ')';
            }
            else
                throw new DataException("Illegal or missing chapter or verse: $chap:$verse");
        }
        else {
            $bookid = 'noval';
            $chap = '';
            $verse = '';
        }

        if (isset($_GET['fulltext'])) {
            $fulltext = trim($_GET['fulltext']);
            if (!empty($fulltext)) {
                $fulltext_escaped = my_escape_string($fulltext);
                $sql .= " AND MATCH(description) AGAINST('$fulltext_escaped' IN NATURAL LANGUAGE MODE)";
            }
        }
        else {
            $fulltext = '';
        }

        $res = exec_sql($sql);
        $num_pics = mysqli_num_rows($res);

        $allids = array();
        while ($row = mysqli_fetch_object($res))
            $allids[] = $row->id;

	if (isset($_GET['cur']) && is_numeric($_GET['cur']) && $_GET['cur']!=-1)
	    $cur = $_GET['cur'];
	else
	    $cur = 0;

        $_SESSION['allids'] = $allids;
        $_SESSION['num_pics'] = $num_pics;
        $_SESSION['allcats'] = $allcats;
        $_SESSION['get'] = $_GET;
        $_SESSION['cur'] = $cur;
        $_SESSION['bookid'] = $bookid;
        $_SESSION['chap'] = $chap;
        $_SESSION['verse'] = $verse;
        $_SESSION['fulltext'] = $fulltext;
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
        $bookid = $_SESSION['bookid'];
        $chap = $_SESSION['chap'];
        $verse = $_SESSION['verse'];
        $fulltext = $_SESSION['fulltext'];
        $_GET = $_SESSION['get'];
    }

    $allpics = find_pics($allids, $allcats, $num_pics, $cur, $max_per_page);
}
catch (DataException $e) {
    $error = $e->getMessage();
}


function extrafun($thispic, $dirbig, $dir600, $dir160, $published)
{
    global $credentials;

    if ($thispic->published) {
//        $res = shtml_class('div', 'link', 'Download: '
//                           . shtml_a("dl.php?file=$dirbig/$thispic->filename", 'Large')
//                           . ' '
//                           . shtml_a("dl.php?file=$dir600/$thispic->filename", 'Small'));
        $res = shtml_class('div', 'link', shtml_a("dl.php?file=$dir600/$thispic->filename", 'Download'));
        $res .= shtml_class('div', 'link', shtml_a("link.php?picno={$thispic->pic_no}",'Direct link'));

        if (!is_null($credentials->user)) {
            $res .= shtml_class('div', 'link',
                                shtml_a("editdesc.php?id=$thispic->id", 'Edit')
                                . ' ' 
                                . shtml_a("publish.php?id=$thispic->id", 'Unpublish')
                                . ' '
                                . shtml_attr('a',
                                             "onclick=\"genericConfirm('Delete file',"
                                             . "'Delete the file $thispic->filename?',"
                                             . "'delete.php?id=$thispic->id'); return false\" "
                                             . "href=\"#\"",
                                             'Delete'));
        }
        return $res;
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

<p>This is the EuroPLOT resource database. Here you can find and download pictures from the Biblical lands.</p>

<p>In the left column you can search for pictures in various categories. For example, by selecting
&ldquo;Jericho&rdquo; under &ldquo;Place&rdquo; and pressing the Search button, you will find
pictures from the city of Jericho.</p>

<p>You can also search for pictures that relate to a specific verse in the Bible. Select the book,
chapter, and verse under &ldquo;Bible reference&rdquo; and press the Search button. You can also
search for specific words in the picture descriptions.</p>

<p>On the page you will see a number of pictures with a short description under them. Click on a
picture to view a larger version and see the full description. (At present the descriptions are in
Danish and/or English.)</p>

<p>The system only displays 25 pictures on a page. If more pictures are available, you can move to
other pages by using the page links in the top right corner.</p>

<p>If you click on the + in the lower left hand corner of a picture frame, you will have access to
these links:</p>

<ul>
<li><b>Download.</b> Downloads a smal version of the picture (600
pixels along the longest edge).</li>
<li><b>Direct link.</b> This is a direct link to a small version of this picture and its description.
</ul>

<?php if (!is_null($credentials->user)): ?>
  <h2>Logged-in Users</h2>
   
  <p>You are at present logged in to the system. This gives you some extra functionality on this page.</p>
   
  <p>In the left column you have an administrative menu which gives you access to functions that
  manipulate the resource database. Here you can also change your user profile.</p>
   
  <p>If you click on the + in the lower left hand corner of a picture frame, you will have access to
    three additional links, and you can see the file name of the picture.</p>
   
  <p>The additional links are:</p>
  <ul>
    <li><b>Edit.</b> Enables you to edit the description of the picture.</li>
    <li><b>Unpublish.</b> Moves the picture to the collection of unpublished pictures.</li>
    <li><b>Delete.</b> Deletes the picture from the database.</li>
  </ul>   

  <p>If there is more than one picture in the database, you will see at the top of the page a button
    that allows you to unpublish all pictures in the database simultaneously. Note that this will
    unpublish <i>all</i> pictures, not just the ones displayed on the current page.</p>

 <?php endif; ?>
<?php
$doctext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End help text
/////////////////////////////////////////////////////////////////////////////
?>

<?php
/////////////////////////////////////////////////////////////////////////////
// Start background text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>
<p>Since 1970, Hartvig Wagner has photographed archaeological sites in Israel and its neighbouring
  countries. He has used them in numerous meetings and as illustrations in SBA&rsquo;s newsletter
  &ldquo;Tel&rdquo;; they have also been used in software from the Danish Bible Society.</p>

<p>On this <i>EuroPLOT Resources</i> site they are now made freely available for non-commercial uses
in, for example, teaching.</p>

<p>This project has been a number of years in the making. More than 14 years ago, Hartvig Wagner and
  Nicolai Winther-Nielsen started discussing plans and possibilities for financing digitisation of
  Wagner&rsquo;s slide photos. Later, Jens Bruun Kofoed and Ulrik Sandborg-Petersen became part of
  the database planning and the creation of a project through 3BM. Over a number years, Hartvig
  Wagner himself financed the digitisation of the photos, which was carried out by John Erik
  Hansen, Brønshøj. In the spring of 2012, Claus Tøndering from EuroPLOT programmed the database as
  an employee of Aalborg University.</p>

<p>The database is integrated in the teaching tool PLOTLearner, which is a tutor for the study of
  Hebrew and Greek based on the original Biblical texts. This development work is coordinated by
  Nicolai Winther-Nielsen from EuroPLOT.</p>

<p>In a project on Fjellhaug International University College Denmark, Jens Bruun Kofoed uploaded
  the material with help from three students, Michael Agerbo Mørch and Lisbeth Dam with support from
  SBA and Christian Højgaard with support from LMU.</p>

<p>In the coming years the resource database will be extended with other collections and integrated
  in new learning driven by database techonology and persuasive design.</p>

<?php
$backgroundtext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End background text
/////////////////////////////////////////////////////////////////////////////
?>

<?php
/////////////////////////////////////////////////////////////////////////////
// Start licence text
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

<p>These images are Copyright © 2013 by the photographers/artists and 3BM.</p>

<p style="text-align:center; width: 450px;"><a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/"><img alt="Creative Commons License" style="border-width:0" src="https://licensebuttons.net/l/by-nc-sa/3.0/88x31.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" href="http://purl.org/dc/dcmitype/StillImage" property="dct:title" rel="dct:type">These photos</span> by <span xmlns:cc="http://creativecommons.org/ns#" property="cc:attributionName">Hartvig Wagner and 3BM</span> are licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/" target="_blank">Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License</a>.</p>

<p>This means that you may freely download and use the pictures for non-commercial purposes, provided that you
  attribute the work to Hartvig Wagner and 3BM (but not in any way that suggests that they endorse
  you or your use of the pictures). You are free to alter, transform, or build upon these pictures, but you
  may distribute the resulting pictures only under the same or similar licence to this one.</p>

<p>Commercial licences are available from 3BM at a price of DKK 50 per photo. For more information,
please contact Jens Bruun Kofoed at <span id="jbkmail"></span>.</p>


<?php
$licencetext = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End licence text
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
      <?php else: ?>
        <?php if ($num_pics==0): ?>
          <h1>No pictures found</h1>
        <?php else: ?>
          <?= mk_header($allpics, $num_pics, $max_per_page) ?>
          <?= mk_pageselector($num_pics, $max_per_page, $cur, 'img.php') ?>
   
          <div class="clearfloats"></div>
            <?php if (!is_null($credentials->user)):
              $num_all = num_published_pics();
              if ($num_all>1): ?>
              <p><a class="makebutton"
                    onclick="genericConfirm('Unpublish all','Unpublish all pictures?','publishall.php?pub=0');return false"
                    href="#">Unpublish <?= $num_all>2 ? "all the $num_all" : 'both' ?> pictures in the database</a></p>
           <?php endif;
              endif; ?>

          <?php show_all_pics($allpics, $max_per_line, $dirname_big, $dirname_600, $dirname_160, 1, 'extrafun'); ?>
        <?php endif; // if num_pics==0 ?>
      <?php endif; // if error ?>

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
    $('.img1').lightBox();
});

<?php
$headerscript = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End header script
/////////////////////////////////////////////////////////////////////////////
?>

<?php
/////////////////////////////////////////////////////////////////////////////
// Start search box
/////////////////////////////////////////////////////////////////////////////
ob_start();
?>

  <div id="search" class="ui-corner-all">
    <form action="img.php">
      <?php foreach ($allcats as $thiscat): ?>
        <p class="forlabel">
          <label for="<?=$thiscat->abb?>"><?=$thiscat->name?></label>
          <select name="<?=$thiscat->abb?>" id="<?=$thiscat->abb?>">
            <option value="noval" <?=$_GET[$thiscat->abb]==='noval' ? 'selected="selected"' : ''?> >Select one</option>
            <?php foreach ($thiscat->values as $row): ?>
              <?php if (is_null($row->value_high)): ?>
                <option value="<?=$row->value?>" <?=$_GET[$thiscat->abb]===(string)$row->value ? 'selected="selected"' : ''?> >&nbsp;&nbsp;&nbsp;&nbsp;<?=htmlspecialchars($row->name)?></option>
              <?php else: ?>
                <option value="<?=$row->value?>-<?=$row->value_high?>" <?=$_GET[$thiscat->abb]==="$row->value-$row->value_high" ? 'selected="selected"' : ''?> ><?=htmlspecialchars($row->name)?>:</option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </p>
      <?php endforeach; ?>
 
      <p class="forlabel">Bible reference:</p>
      <div id="bibsearch">
        <p class="forlabel">
          <label for="book">Book</label>
          <select name="book" id="book">
            <option value="noval" <?=$bookid==='noval' ? 'selected="selected"' : ''?> >Select one</option>
            <?php foreach ($bibrefs as $value => $name): ?>
              <option value="<?=$value?>" <?=$bookid===(string)$value ? 'selected="selected"' : ''?> ><?=htmlspecialchars($name)?></option>
            <?php endforeach; ?>
          </select>
          <label for="chapter">Chapter</label>
          <input type="text" class="chapter" name="chapter" id="chapter" value="<?=htmlspecialchars($chap)?>"/>
          <label for="verse">Verse</label>
          <input type="text" class="verse" name="verse" id="verse" value="<?=htmlspecialchars($verse)?>"/>
        </p>
      </div>

      <div id="fulltextsearch">
        <p>
          <label for="fulltext">Search for any of these words:</label>
          <input type="text" name="fulltext" id="fulltext" value="<?=htmlspecialchars($fulltext)?>"/>
        </p>
      </div>

      <div id="buttons">
        <input class="makebutton" type="submit" value="Search" />
        <input class="makebutton" onclick="location='img.php'" type="button" value="Clear" />
      </div>
    </form>
  </div>

<?php
$leftbox = ob_get_clean();
/////////////////////////////////////////////////////////////////////////////
// End search box
/////////////////////////////////////////////////////////////////////////////
?>


<?php

wrapme('EuroPLOT Resources',
       $headerscript,
       $leftbox,
       $bodytext,
       array('jquery-lightbox-0.5/js/jquery.lightbox-0.5.min.js'),
       array('jquery-lightbox-0.5/css/jquery.lightbox-0.5.css'));

?>
