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

$indent = 0;

function indentit($string=null)
{
    global $indent;
    printf("%{$indent}s%s", null, $string);
    if (!is_null($string))
        print "\n";
}

function addcssfile($filename) {
    indentit("<link type=\"text/css\" href=\"$filename\" rel=\"stylesheet\"/>");
}

function addjsfile($filename) {
    indentit("<script type=\"text/javascript\" src=\"$filename\"></script>");
}

function shtml($tag,$value)
{
    return "<$tag>$value</$tag>";
}

function html($tag,$value)
{
    indentit();
    print shtml($tag,$value) . "\n";
}

// Same as html() but with out line break
function html_nonl($tag,$value)
{
    print shtml($tag,$value);
}

function shtml_a($target,$text)
{
    return "<a href=\"$target\">$text</a>";
}

function html_a($target,$text)
{
    html_attr('a',"href=\"$target\"",$text);
}

function shtml_attr($tag,$attr,$value=null)
{
    return "<$tag $attr>$value</$tag>";
}

function html_attr($tag,$attr,$value=null)
{
    indentit(shtml_attr($tag,$attr,$value));
}

function shtml_class($tag,$class,$value=null)
{
    return shtml_attr($tag, "class=\"$class\"", $value);
}

function html_class($tag,$class,$value=null)
{
    html_attr($tag, "class=\"$class\"", $value);
}

// A standalone tag such as <br/> 
function shtml_1($tag)
{
    return "<$tag />";
}

function shtml_attr_1($tag,$attr)
{
    return "<$tag $attr />";
}


function html_1($tag)
{
    indentit();
    print shtml_1($tag) . "\n";
}

function html_attr_1($tag,$attr)
{
    indentit();
    print shtml_attr_1($tag, $attr) . "\n";
}

function html_b($tag)
{
    indentit();
    printf("<%s>\n", $tag);

    global $indent;
    $indent += 2;
}

function html_attr_b($tag,$attr)
{
    indentit();
    printf("<%s %s>\n", $tag, $attr);

    global $indent;
    $indent += 2;
}

function html_class_b($tag,$class)
{
    html_attr_b($tag, "class=\"$class\"");
}

function html_id_b($tag,$id)
{
    html_attr_b($tag, "id=\"$id\"");
}

function html_style_b($tag,$style)
{
    html_attr_b($tag, "style=\"$style\"");
}


function html_e($tag)
{
    global $indent;
    $indent = max($indent-2, 0);
    indentit();
    printf("</%s>\n", $tag);
}


?>