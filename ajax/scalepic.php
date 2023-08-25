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

require_once('../config.inc');

ini_set('memory_limit', '128M');


/*
	Function scalepic($name,$dir,$size)
	creates a resized image
	variables:
	$name		Filename
    $src		Directory for original picture
    $dst		Directory for scaled picture
    $size		Size of largest target dimension
*/	
function scalepic($name,$src,$dst,$size)
{
    global $dirname_big;

    if (strcasecmp(substr($name,-4),".jpg")==0)
        $src_img=imagecreatefromjpeg("../$src/$name");
    else if (strcasecmp(substr($name,-4),".png")==0)
        $src_img=imagecreatefrompng("../$src/$name");
    else
        return;

	$old_x=imageSX($src_img);
	$old_y=imageSY($src_img);

    $scale_factor = $size/max($old_x,$old_y);

    $new_x = $old_x * $scale_factor;
	$new_y = $old_y * $scale_factor;

	$dst_img=ImageCreateTrueColor($new_x,$new_y);
	imagecopyresampled($dst_img,$src_img,0,0,0,0,$new_x,$new_y,$old_x,$old_y); 
    if (strcasecmp(substr($name,-4),".png")==0)
		imagepng($dst_img,"../$dst/$name");
	else
		imagejpeg($dst_img,"../$dst/$name"); 
	imagedestroy($dst_img); 
	imagedestroy($src_img); 
}


header("Content-Type: text/plain");
header("Cache-Control: no-cache, must-revalidate");

scalepic($_GET['file'], $_GET['src'], $_GET['dst'], $_GET['size']);

//print "OKScaled photo no. {$_GET['count']}: {$_GET['file']} to size {$_GET['size']}.";
print "OK{$_GET['count']}";
?>