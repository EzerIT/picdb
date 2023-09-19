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

require_once 'database.inc.php';
require_once 'html.inc.php';
require_once 'util.inc.php';

class verseRange {
    public $low;
    public $high;
    function __construct($l, $h) {
        assert (!is_null($l));
        assert (!is_null($h));

        $this->low = $l;
        $this->high = $h;
    }
}

class refs {
    public $book;
    public $chap;
    public $verses; // array of verseRange

    const MIN_VERSE = 1;
    const MAX_VERSE = 9999; // Indicates end of chapter

    function __construct($b,$c) {
        $this->book = $b;
        $this->chap = $c;
        $this->verses = array();
    }
    function addRange($l, $h) {
        $this->verses[] = new verseRange($l, $h);
    }

    function toStrings() {
        global $book2En;
        $bookEn = $book2En[$this->book];
        $start = ", $bookEn $this->chap";
        $res = '';
        if (!empty($this->verses)) {
            foreach ($this->verses as $ve) {
                if ($ve->low==$ve->high)
                    $res .= "$start:$ve->low";
                elseif ($ve->high==refs::MAX_VERSE)
                    $res .= "$start:$ve->low-end";
                else
                    $res .= "$start:$ve->low-$ve->high";
            }
        }
        else
            $res .= $start;
        return $res;
    }

    function toSql($more) {
        global $book2Id;
        $bookid = $book2Id[$this->book];
        $res = '';
        if (!empty($this->verses)) {
            foreach ($this->verses as $ve)
                $res .= sprintf(",(%d,%d,%d,%d,%s)", $bookid, $this->chap, $ve->low, $ve->high, $more);
        }
        else
            $res .= sprintf(",(%d,%d,%d,%d,%s)", $bookid, $this->chap, refs::MIN_VERSE, refs::MAX_VERSE, $more);
        return $res;
    }
}


$booksFromDa = array(
    "1 Mos",
    "2 Mos",
    "3 Mos",
    "4 Mos",
    "5 Mos",
    "Jos",
    "Dom",
    "Ruth",
    "1 Sam",
    "2 Sam",
    "1 Kong",
    "1 Kong",
    "2 Kong",
    "2 Kong",
    "1 Krøn",
    "1 Kr&oslash;n",
    "2 Krøn",
    "2 Kr&oslash;n",
    "Ezra",
    "Neh",
    "Est",
    "Job",
    "Sl",
    "Ordsp",
//  "Præd",        Must come after Sir
//  "Pr&aelig;d",  Must come after Sir
    "Højs",
    "Es",
    "Jer",
    "Klages",
    "Ez",
    "Dan",
    "Hos",
    "Joel",
    "Am",
    "Obad",
    "Jon",
    "Mika",
    "Nah",
    "Hab",
    "Sef",
    "Hagg",
    "Zak",
    "Mal",
    "Tob",
    "Judit",
    "TilfEst",
    "1 Makk",
    "2 Makk",
    "Visd",
    "Sir",
    "Præd",
    "Pr&aelig;d",
    "ManB",
    "Bar",
    "JerBr",
    "TilfDan",
    "Matt",
    "Mark",
    "Luk",
//  "Joh",  Must come after 3 Joh
    "ApG",
    "Rom",
    "1 Kor",
    "2 Kor",
    "Gal",
    "Ef",
    "Fil",
    "Kol",
    "1 Thess",
    "2 Thess",
    "1 Tim",
    "2 Tim",
    "Tit",
    "Filem",
    "Hebr",
    "Jak",
    "1 Pet",
    "2 Pet",
    "1 Joh",
    "2 Joh",
    "3 Joh",
    "Joh",
    "Jud",
    "Åb",
    "&Aring;b");

$booksFromEn = array(
    "Gen.",
    "Ex.",
    "Lev.",
    "Num.",
    "Deut.",
    "Josh.",
    "Judg.",
    "Ruth",
    "1 Sam.",
    "2 Sam.",
    "1 Kgs.",
    "1 Kings",
    "2 Kgs.",
    "2 Kings",
    "1 Chron.",
    "1 Chron.",
    "2 Chron.",
    "2 Chron.",
    "Ezra",
    "Neh.",
    "Esther",
    "Job",
    "Ps.",
    "Prov.",
//  "Eccles.",  Must come after Ecclus (in an older version of this file) 
//  "Eccles.",  Must come after Ecclus (in an older version of this file) 
    "Song",
    "Isa.",
    "Jer.",
    "Lam.",
    "Ezek.",
    "Dan.",
    "Hos.",
    "Joel",
    "Amos",
    "Obad.",
    "Jon.",
    "Mic.",
    "Nah.",
    "Hab.",
    "Zeph.",
    "Hag.",
    "Zech.",
    "Mal.",
    "Tb",
    "Jdt",
    "Add Esth",
    "1 Mc",
    "2 Mc",
    "Ws",
    "Ecclus",
    "Eccles.",
    "Eccles.",
    "Pr of Man",
    "Bar",
    "Let Jer",
    "Add Dan",
    "Matt.",
    "Mark",
    "Luke",
//  "John",  Must com after 3 John
    "Acts",
    "Rom.",
    "1 Cor.",
    "2 Cor.",
    "Gal.",
    "Eph.",
    "Phil.",
    "Col.",
    "2 Thess.",
    "2 Tim.",
    "1 Thess.",
    "1 Tim.",
    "Titus",
    "Philem.",
    "Heb.",
    "James",
    "1 Pet.",
    "1 John",
    "2 Pet.",
    "2 John",
    "3 John",
    "John",
    "Jude",
    "Rev.",
    "Rev.");


$books = array(
    "Gn",
    "Ex",
    "Lv",
    "Nm",
    "Dt",
    "Jo",
    "Jgs",
    "Ru",
    "ISm",
    "IISm",
    "IKgs",
    "IKgs",
    "IIKgs",
    "IIKgs",
    "IChr",
    "IChr",
    "IIChr",
    "IIChr",
    "Ezr",
    "Neh",
    "Est",
    "Jb",
    "Ps",
    "Prv",
//  "Eccl",  Must come after Ecclus 
//  "Eccl",  Must come after Ecclus 
    "Sg",
    "Is",
    "Jer",
    "Lam",
    "Ez",
    "Dn",
    "Hos",
    "Jl",
    "Am",
    "Ob",
    "Jon",
    "Mi",
    "Na",
    "Hb",
    "Zep",
    "Hg",
    "Zec",
    "Mal",
    "Tb",
    "Jdt",
    "AddEst",
    "IMc",
    "IIMc",
    "Ws",
    "Ecclus",
    "Eccl",
    "Eccl",
    "PrOfMan",
    "Bar",
    "LetJer",
    "AddDan",
    "Mt",
    "Mk",
    "Lk",
//  "Jn",   Must come after IIIJn
    "Acts",
    "Rom",
    "ICor",
    "IICor",
    "Gal",
    "Eph",
    "Phil",
    "Col",
    "IThes",
    "IIThes",
    "ITm",
    "IITm",
    "Ti",
    "Phlm",
    "Heb",
    "Jas",
    "IPt",
    "IIPt",
    "IJn",
    "IIJn",
    "IIIJn",
    "Jn",
    "Jude",
    "Rv",
    "Rv");

$chverse = '(\d+[abf]?([\-\.,:]\d+[abf]?)*)';



$book2Id = array();
$book2En = array();

$res = exec_sql("SELECT id,internal,english_abb FROM {$db_prefix}biblebooks ORDER BY id");
while ($row=mysqli_fetch_object($res)) {
    $book2Id[$row->internal] = $row->id;
    $book2En[$row->internal] = $row->english_abb;
    $book2EnById[$row->id] = $row->english_abb;
}

assert(count($booksFromDa)==count($books));
assert(count($booksFromEn)==count($books));

function toDanish($book_internal, $chapter, $verse) {
    global $books, $booksFromDa;
    $book_danish = $booksFromDa[array_search($book_internal,$books)];
    return $book_danish . ' ' . $chapter . ',' . $verse;
}

function toEnglish($book_internal, $chapter, $verse) {
    global $book2En;
    return $book2En[$book_internal] . ' ' . $chapter . ':' . $verse;
}


function stripab($v)
{
    if (substr($v,-1)=='a' || substr($v,-1)=='b')
        return substr($v, 0, -1);
    else
        return $v;
}

function decodeverse($ref,$res)
{
    $vers1 = '(\d+[ab]?)';
    $vers2 = "({$vers1}-{$vers1})";
    $vers3 = "({$vers1}f)";
    $vers4 = "(({$vers1}|{$vers2}|{$vers3})((\\.({$vers1}|{$vers2}|{$vers3}))*))";
    //       "123        456      78       9X"

    if (preg_match("/^{$vers4}$/",$ref, $rgs)) {
        if (!empty($rgs[3])) {
            //print "Single verse: {$rgs[3]}\n";
            $res->addRange(stripab($rgs[3]), stripab($rgs[3]));
        }
        elseif (!empty($rgs[4])) {
            // print "Verse range: {$rgs[5]} to {$rgs[6]}\n";
            $res->addRange(stripab($rgs[5]), stripab($rgs[6]));
        }
        elseif (!empty($rgs[7])) {
            // print "Verse with f: {$rgs[8]}\n";
            $res->addRange($rgs[8], $rgs[8]+1);
        }
        else
            print "ERROR " . __LINE__ . " ref: $ref\n";

        if (!empty($rgs[9])) {
            // print "More: ";
            decodeverse(substr($rgs[9],1),$res);
        }
    }
    else
        print "ERROR " . __LINE__ . " ref: $ref\n";
}

function checkref2($book, $ref)
{
    $result = array();

    $chap1 = '(\d+)';
    $chap2 = "({$chap1}-{$chap1})";

    $vers1 = '(\d+[ab]?)';
    $chapverse = "({$chap1}[,:]{$vers1}-{$chap1}[,:]{$vers1})";


    $verses = '(\d+[abf]?([\-\.]\d+[abf]?)*)';

    if (preg_match("/^{$chap1}$/",$ref, $rgs)) {
        //print "Single chapter: {$rgs[1]}\n";
        $result[] = new refs($book, $rgs[1]);
    }
    elseif (preg_match("/^{$chap2}$/",$ref, $rgs)) {
        //print "Chapter range: {$rgs[2]} to {$rgs[3]}\n";
        for ($ch=$rgs[2]; $ch<=$rgs[3]; ++$ch)
            $result[] = new refs($book, $ch);
    }
    elseif (preg_match("/^{$chap1}[,:]{$verses}$/",$ref, $rgs)) {
        // print "Chapter is {$rgs[1]}. ";
        $rr = new refs($book, $rgs[1]);
        decodeverse($rgs[2],$rr);
        $result[] = $rr;
    }
    elseif (preg_match("/^{$chapverse}$/",$ref, $rgs)) {
        //print "Chap/verse range: $rgs[2]/$rgs[3] to $rgs[4]/$rgs[5]\n";
        $versestart = stripab($rgs[3]);
        $verseend = stripab($rgs[5]);
        for ($ch=$rgs[2]; $ch<=$rgs[4]; ++$ch) {
            $rr = new refs($book, $ch);
            if ($ch==$rgs[2])
                $rr->addRange(stripab($rgs[3]), refs::MAX_VERSE);
            elseif ($ch==$rgs[4])
                $rr->addRange(1,stripab($rgs[5]));
            $result[] = $rr;
        }
    }
    return $result;
}
    


    
function checkref($book,$ref)
{
    global $chverse;
    $regex = "/({$chverse});?/";

    $result = array();
    if (preg_match_all($regex,$ref,$rgs)>0) {
        foreach ($rgs[1] as $rr)
            $result[] = checkref2($book, $rr);
    }
    return $result;
}


function findref($more, $description, &$sql, &$refs)
{
    global $db_prefix;
    global $books;
    global $booksFromDa;
    global $booksFromEn;
    global $chverse;

    $allbooks = '(' . implode('|',$books) . ')';
    $chverses = "(\s*{$chverse};?)";

    $regex = "/$allbooks({$chverses}+)/";

    $sql = '';
    $refs = '';
    $desc = str_replace($booksFromEn, $books, $description);
    $desc = str_replace($booksFromDa, $books, $desc);

    if (preg_match_all($regex, $desc, $rgs)>0) {
        assert (count($rgs[1])==count($rgs[2]));

        for ($i=0; $i<count($rgs[1]); ++$i) {
            $a = checkref($rgs[1][$i],$rgs[2][$i]);

            foreach ($a as $a1) {
                foreach ($a1 as $a2) {
                    assert(is_object($a2));

                    $sql .= $a2->toSql($more);

                    $refs .= $a2->toStrings();
                }
            }
        }
    }
}


?>