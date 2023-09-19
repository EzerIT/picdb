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


//////////////////////////////////////////////////////////////////////
// Data part
//////////////////////////////////////////////////////////////////////

require_once 'html.inc.php';
require_once 'database.inc.php';


// $only_used is true if we are only interested in categories that are actually used for an image.
function find_allcats($only_used = false)
{
    global $db_prefix;

    $res = exec_sql("SELECT * from {$db_prefix}categories");

    // $allcats will contain an entry for each category, indexed by the category id
    // $allcats[id]->values will contain an entry for each category value, indexed by the value
    $allcats = array();

    while ($row = mysqli_fetch_object($res)) {
        $row->values = array();

        $useval = $row->isstring ? 'stringval' : 'intval';
    
        $res2 = exec_sql("SELECT $useval as value, {$useval}_high as value_high, name "
                         . "FROM {$db_prefix}catval WHERE category=$row->id "
                         . "ORDER BY value, IF(ISNULL(value_high),1,0), value_high;"); // Sorts null after non-null value_high

        while ($row2 = mysqli_fetch_object($res2))
            if ($only_used && is_null($row2->value_high)) {
                // Only include this category if it is used
                $val = $row->isstring ? "'$row2->value'" : $row2->value;
                $res3 = exec_sql("SELECT * FROM {$db_prefix}piccat WHERE catid=$row->id AND $useval=$val");
                if (mysqli_num_rows($res3)>0)
                    $row->values[] = $row2;
            }
            else
                $row->values[] = $row2;

        $allcats[$row->id] = $row;
    }
    return $allcats;
}


/* Finds value range and name from a list of category values */
function find_cat($allcatvals, $catid)
{
    foreach ($allcatvals as $a)
        if ($a->value==$catid && is_null($a->value_high))
            return $a;

    return null;
}

function num_published_pics()
{
    global $db_prefix;

    $res = exec_sql("SELECT COUNT(*) c FROM {$db_prefix}photos WHERE published");
  
    if ($row = mysqli_fetch_object($res))
        return $row->c;
    else
        return 0;
}
    

function strip_links($orig)
{
    return preg_replace('/{([\\wæøåÆØÅ ]+)\|([\\w_\-\.]+)}/', '$1', $orig);
}

function replace_links_json($orig)
{
    global $db_prefix;
    $num_links = preg_match_all('/{([\\wæøåÆØÅ ]+)\|([\\w_\-\.]+)}/', $orig, $match);

    for ($i=0; $i<$num_links; ++$i) {
        $text = $match[1][$i];
        $picfile = $match[2][$i];
        $orig = preg_replace("/\{$text\|$picfile\}/", "[$picfile]" , $orig);
    }
    return $orig;
}

function replace_links($orig)
{
    global $db_prefix;
    $num_links = preg_match_all('/{([\\wæøåÆØÅ ]+)\|([\\w_\-\.]+)}/', $orig, $match);

    for ($i=0; $i<$num_links; ++$i) {
        $text = $match[1][$i];
        $picfile = $match[2][$i];
        $res = exec_sql("SELECT pic_no FROM {$db_prefix}photos WHERE filename='$picfile'");
        if ($row = mysqli_fetch_object($res))
            $orig = preg_replace("/\{$text\|$picfile\}/", "<a target=\"_blank\" href=\"link.php?picno=$row->pic_no\">$text</a>", $orig);
        else 
            $orig = preg_replace("/\{$text\|$picfile\}/", "$text" , $orig);
    }
    return $orig;
}

function find_pics($allids, $allcats, $num_pics, $cur, $max_per_page)
{
    global $db_prefix;

    $allpics = array();

    if ($num_pics>0) {
        $res = exec_sql("SELECT * FROM {$db_prefix}photos WHERE id IN (" . implode(',',$allids) . ") ORDER BY pic_no LIMIT $cur,$max_per_page");

        while ($row = mysqli_fetch_object($res)) {
            $allpics[] = $row;

	    if (is_null($row->description))
		    $row->description="";
            $row->shortdesc = strip_links(strip_tags($row->description));
            if (utf8_strlen($row->shortdesc)>40)
                $row->shortdesc = substr($row->shortdesc,0,utf8_step($row->shortdesc,37)) . '...';


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
            $row->longdesc = htmlspecialchars($row->longdesc);
        }
    }

    return $allpics;
}

function mk_header($allpics, $num_pics, $max_per_page)
{
    if ($num_pics>1) {
        $view_num_pics = count($allpics);
        return shtml_class('h1','float',"$num_pics pictures found"
                           . ($num_pics>$view_num_pics ? " ($view_num_pics displayed)" : ''));
    }
    else
        return shtml_class('h1','float','1 picture found');
}

function mk_pageselector($num_pics, $max_per_page, $cur, $url)
{
    $res = '<div id="pageselector">';
    if ($num_pics>$max_per_page) {
        $res .= '<p>Page: ';
        if ($cur>0)
            $res .= shtml_a("$url?cur=" . max($cur-$max_per_page,0), 'Prev');
        for ($i=0; $i<$num_pics; $i+=$max_per_page) {
            if ($i == $cur)
                $res .= ' ' . shtml('b',floor($i/$max_per_page)+1);
            else
                $res .= ' ' . shtml_a("$url?cur=$i", floor($i/$max_per_page)+1);
        }
        if ($cur+$max_per_page<$num_pics)
            $res .= ' ' . shtml_a("$url?cur=" . ($cur+$max_per_page), 'Next');
        $res .= '</p>';
    }
    $res .= '</div>';

    return $res;
}

function show_one_pic($thispic, $dirbig, $dir600, $dir160, $published, $extrafun)
{
    html_class_b('td','cell');
    html_class_b('div','shadow1');
    html_class_b('div','thumbnail');

    if ($published==$thispic->published) {
        html_attr('a',"class=\"img1\" href=\"$dir600/$thispic->filename\" title=\"$thispic->longdesc\"",
                  shtml_attr('span','title="Click to view larger picture"',
                             shtml_attr_1('img',"class=\"shadow\" alt=\"Img alt\" src=\"$dir160/$thispic->filename\"")));
    }
    elseif ($thispic->published)
        html_attr_1('img', 'class="shadow" alt="Img alt" src="images/published.png"');
    else
        html_attr_1('img', 'class="shadow" alt="Img alt" src="images/unpublished.png"');

    html_e('div');

    html_class('div','desc',$thispic->shortdesc);

    html_attr('div',"class=\"ui-icon ui-icon-plus\" onclick=\"toggleExtra('#photoreveal-$thispic->id',this);\"");
    html_attr_b('div',"id=\"photoreveal-$thispic->id\" style=\"display:none;\"");

    print $extrafun($thispic, $dirbig, $dir600, $dir160, $published);


    global $credentials;
    if (!is_null($credentials->user))
        html_class('p','center',shtml('i',$thispic->filename));

    html_e('div');
    html_e('div');
    html_e('td');
}

function show_all_pics($allpics, $max_per_line, $dirbig, $dir600, $dir160, $published, $extrafun)
{
    $view_num_pics = count($allpics);

    html_b('table');

    for ($row=0; $row<$view_num_pics; $row += $max_per_line) {
        html_b('tr');
        for ($col=0; $col<$max_per_line && $row+$col<$view_num_pics; ++$col) {
            $thispic = $allpics[$row+$col];
            show_one_pic($thispic, $dirbig, $dir600, $dir160, $published, $extrafun);
        }
        while (++$col<=$max_per_line)
            html('td','');
        html_e('tr');
    }
    html_e('table');
}
