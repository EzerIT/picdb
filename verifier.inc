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


require_once 'database.inc';
require_once 'filenamereport.inc';


class Verifier {
    // Arrays for information gathering:

    public  $removed_big = array(); // Deleted files with bad names. Index is irrelevant, value is filename.
    private $in_big = array(); // Files in $unpub_dirname_big. Index is filename (after rename), value is FileNameReport.
    public  $to_remove_big = array(); // Files with bad names. Index is filename (before rename), value is FileNameReport.
    public  $renamed_big = array(); // Renamed files. Index is old filename, value is new filename.

    public  $to_add_160 = array(); // Miniatures to add. Index is irrelevant, value is filename.
    private $in_160 = array(); // Existing miniatures. Index is filename, value is irrelevant.
    public  $removed_160 = array(); // Deleted miniatures. Index is irrelevant, value is filename.

    public  $to_add_600 = array(); // Miniatures to add. Index is irrelevant, value is filename.
    private $in_600 = array(); // Existing miniatures. Index is filename, value is irrelevant.
    public  $removed_600 = array(); // Deleted miniatures. Index is irrelevant, value is filename.

    public  $added_db = array();  // Pictures added to database. Index is irrelevant, value is filename.
    private $in_db = array(); // All photos in database. Index is filename. Value is irrelevant.
    private $in_db_ids = ''; // Comma separated list of all picture ids in the database.
    public  $removed_db = array(); // Deleted database entry. Index is irrelevant, value is filename.
    public  $deleted_rows = 0; // Number of deleted unused database entries.

    public  $dup_picno = array(); // Duplicate picture numbers. Index is picture number, value is count

    public  $allactions = array(); // Ajax actions to create miniatures. Index is irrelevant, value is server request.
    
    private $published; // Does this Verifier work on published or unpublished pictures?

    private $dir_big; // Directory for big pictures
    private $dir_600; // Directory for 600 pixel pictures
    private $dir_160; // Directory for 160 pixel pictures

    public  $modifications; // Were there any modifications to the pictures?

    // Constructor
    // PARAMETER:
    //    $pub (boolean): Does this Verifier work on published or unpublished pictures?
    public function __construct($pub) {
        global $dirname_big;
        global $dirname_600;
        global $dirname_160;
        global $unpub_dirname_big;
        global $unpub_dirname_600;
        global $unpub_dirname_160;

        $this->published = $pub;
        if ($pub) {
            $this->dir_big = $dirname_big;
            $this->dir_600 = $dirname_600;
            $this->dir_160 = $dirname_160;
        }
        else {
            $this->dir_big = $unpub_dirname_big;
            $this->dir_600 = $unpub_dirname_600;
            $this->dir_160 = $unpub_dirname_160;
        }
    }


    // Searches the folder $dir_big for files.
    // ACTIONS:
    //     Delete files with fatal errors (typically, illegal filenames).
    // INFORMATION GATHERING:
    //     Populates $removed_big with names of files that have been deleted.
    private function purge_big() {
        $d = dir($this->dir_big);
        chdir($this->dir_big);
        while (($entry = $d->read()) !== false) {
            if (filetype($entry)=='dir')
                continue;

            $thisfile = new FileNameReport($this->dir_big,$entry);
            if (!is_null($thisfile->fatal_error)) {
                unlink($entry);
                $this->removed_big[] = $entry;
            }
        }
        $d->close();
        chdir(dirname(__FILE__));
    }


    // Searches the folder $dir_big for files.
    // ACTIONS:
    //     Renames files as required.
    // INFORMATION GATHERING:
    //     Populates $to_remove_big with names of files that are illegal.
    //     Populates $in_big with names of files that should remain in the folder.
    //     Populates $renamed_big with names of files that have been renamed.
    private function fill_big() {
        $d = dir($this->dir_big);
        chdir($this->dir_big);
        while (($entry = $d->read()) !== false) {
            if (filetype($entry)=='dir')
                continue;

            $thisfile = new FileNameReport($this->dir_big,$entry);
            if (!is_null($thisfile->fatal_error))
                $this->to_remove_big[$entry] = $thisfile;
            else {
                $this->in_big[$thisfile->new_filename] = $thisfile;

                if ($thisfile->renamed) {
                    $this->renamed_big[$thisfile->old_filename] = $thisfile->new_filename;
                }
            }
        }
        $d->close();
        chdir(dirname(__FILE__));
    }

    // Renames all files in $renamed_big found in folder $dir_big
    private function rename_big() {
        chdir($this->dir_big);
        foreach ($this->renamed_big as $old => $new)
            rename($old, $new);
        chdir(dirname(__FILE__));
    }
    

    // Searches the folder $dirname (which is assumed to hold small pictures) for files.
    // The file names are compared with their big counterparts in $in_big.
    // ACTIONS:
    //     Removes files that have no big counterpart.
    // INFORMATION GATHERING:
    //     Populates $in_mini with names of files that remain in the folder.
    //     Populates $removed_mini with names of files that have been removed.
    private function search_miniatures_for_big($dirname, &$in_mini, &$removed_mini) {
        $d = dir($dirname);
        chdir($dirname);
        while (($entry = $d->read()) !== false) {
            if (filetype($entry)=='dir')
                continue;

            if (array_key_exists($entry, $this->in_big))
                $in_mini[$entry] = true;
            else {
                unlink($entry);
                $removed_mini[] = $entry;
            }
        }
        $d->close();
        chdir(dirname(__FILE__));
    }    


    // Searches the database for published/unpublished files (based on $published)
    // The filenames from the database are compared with filenames in $in_big.
    // ACTIONS:
    //     Removes database entries for files not found in $in_big.
    // INFORMATION GATHERING:
    //     Populates $in_db with names of files that remain in the database.
    //     Populates $removed_db with names of files whose entries have been removed.

    private function search_db_for_big() {
        global $db_prefix;

        $res = exec_sql("SELECT id,filename FROM ${db_prefix}photos WHERE "
                        . ($this->published ? 'published' : 'NOT published'));

        while ($row = mysqli_fetch_object($res)) {
            if (array_key_exists($row->filename, $this->in_big)) {
                $this->in_db[$row->filename] = true;
                $this->in_db_ids .= ",$row->id";
            }
            else {
                exec_sql("DELETE FROM {$db_prefix}photos WHERE id=$row->id"); // ...remove from database
                exec_sql("DELETE FROM {$db_prefix}piccat WHERE picid=$row->id");
                exec_sql("DELETE FROM {$db_prefix}bibleref WHERE picid=$row->id");
                $this->removed_db[] = $row->filename;
            }
        }
        if (strlen($this->in_db_ids)>0)
            $this->in_db_ids = substr($this->in_db_ids,1); // Remove initial comma
    }


    // Searches the list of big pictures (in $in_big) for files with no matching miniature (in $in_mini).
    // The big pictures are found in $srcdirname.
    // The miniatures are found in $dstdirname, and their long edge size is $size pixels.
    // INFORMATION GATHERING:
    //     Populates $actions with Ajax requests that create the miniatures.
    //     Populates $to_add_mini with names of miniatures that will be created.
    private function search_big_for_miniatures($in_mini, $dstdirname, $size, &$to_add_mini) {
        foreach ($this->in_big as $name => $value) {
            if (!array_key_exists($name, $in_mini)) { // Does miniature image exist?
                $httpq = http_build_query(array('file' => $name,
                                                'src' => $this->dir_big,
                                                'dst' => $dstdirname,
                                                'size' => $size,
                                                'count' => count($this->allactions)+1),
                                          '', '&');
                $this->allactions[] = "scalepic.php?$httpq";
                $to_add_mini[] = $name;
            }
        }
    }


    // Searches the list of big files (in $in_big) for files with no matching entry in the database (in $in_db).
    // ACTIONS:
    //     Adds database entries for files not found in $in_db.
    // INFORMATION GATHERING:
    //     Populates $added_db with filenames added to the database.
    private function search_big_for_db() {
        foreach ($this->in_big as $name => $value) {
            if (!array_key_exists($name, $this->in_db)) {
                $this->add_pic_to_db($value);
                $this->added_db[] = $value->new_filename;
            }
        }
    }

    // Adds a single picture to the database. Categorizes the picture base on information in a FileNameReport.
    // The $filenamereport describes the picture.
    private function add_pic_to_db($filenamereport) {
        global $db_prefix;

        $pic_info = getimagesize("$filenamereport->directory/$filenamereport->new_filename");
        $exif_info = exif_read_data("$filenamereport->directory/$filenamereport->new_filename");

        if (isset($exif_info['DateTimeOriginal'])) {
            // Sanity check
            $date = $exif_info['DateTimeOriginal'];
            if ($date[4]!=':' || $date[7]!=':' || $date[13]!=':' || $date[16]!=':')
                $date = 'NULL';
            else // Convert to SQL format
                $date = "'" . substr($date,0,4) . '-' . substr($date,5,2) . '-' . substr($date,8) . "'";
        }
        else
            $date = 'NULL';

        exec_sql("INSERT INTO {$db_prefix}photos (pic_no, filename, width, height, date, published) VALUES "
                 . "($filenamereport->picno, '$filenamereport->new_filename', {$pic_info[0]}, {$pic_info[1]}, $date, "
                 . ($this->published ? '1' : '0')
                 . ')');

        $id = mysqli_insert_id();
        if ($id > 0) {
            foreach ($filenamereport->categories as $cat => $values) {
                $res = exec_sql("SELECT id,isstring FROM {$db_prefix}categories WHERE name='$cat'");
                assert(mysqli_num_rows($res)==1);
                $row = mysqli_fetch_object($res);

                $cmd = 'INSERT INTO piccat (picid, catid, '
                    . ($row->isstring ? 'stringval' : 'intval')
                    . ') VALUES ';

                if (count($values)>0) {
                    $inserts = array();
                    foreach ($values as $val) {
                        if ($row->isstring)
                            $val = "'$val'";
                        $inserts[] = "($id,$row->id,$val)";
                    }

                    if (count($inserts)>0)
                        exec_sql("INSERT INTO {$db_prefix}piccat (picid, catid, "
                                 . ($row->isstring ? 'stringval' : 'intval')
                                 . ') VALUES ' . implode(',',$inserts));
                }
            }
        }
    }

    // Removes all entries form the bibleref and piccat database table that do not refer to a
    // picture in the photos table (regardless of whether that picture is published or not.
    private function find_unused_db_entries() {
        global $db_prefix;
        global $connected_db;

        $res = exec_sql("DELETE FROM {$db_prefix}bibleref WHERE picid NOT IN "
                        . "(SELECT id FROM {$db_prefix}photos)");
        $this->deleted_rows = mysqli_affected_rows($connected_db);

        $res = exec_sql("DELETE FROM {$db_prefix}piccat WHERE picid NOT IN "
                        . "(SELECT id FROM {$db_prefix}photos)");
        $this->deleted_rows += mysqli_affected_rows($connected_db);
    }

    // Find duplicate picture numbers. Works only on published pictures.
    private function find_dup_picno() {
        global $db_prefix;

        if (!$this->published)
            return;

        $res = exec_sql("SELECT pic_no,COUNT(pic_no) AS count FROM {$db_prefix}photos "
                        . 'WHERE published GROUP BY pic_no HAVING COUNT(pic_no)>1');
        
        while ($row = mysqli_fetch_object($res))
            $this->dup_picno[$row->pic_no] = $row->count;
    }


    // Perform verification and, if $delete_bad is true, delete files with illegal names.
    public function doVerify($delete_bad) {
        if ($delete_bad)
            $this->purge_big();

        $this->fill_big();
        if (count($this->to_remove_big)==0) {
            $this->rename_big();
            $this->search_miniatures_for_big($this->dir_160, $this->in_160, $this->removed_160);
            $this->search_miniatures_for_big($this->dir_600, $this->in_600, $this->removed_600);
            $this->search_db_for_big();

            $this->search_big_for_miniatures($this->in_160, $this->dir_160, 160, $this->to_add_160);
            $this->search_big_for_miniatures($this->in_600, $this->dir_600, 600, $this->to_add_600);

            $this->search_big_for_db();
            
            $this->find_dup_picno();
            $this->find_unused_db_entries();
        }

        $this->modifications = count($this->removed_big) + count($this->renamed_big) + count($this->added_db)
            + count($this->removed_db) + count($this->removed_160) + count($this->removed_600)
            + count($this->to_add_160) + count($this->to_add_600) + $this->deleted_rows + count($this->dup_picno) > 0;
    }
}    
?>
