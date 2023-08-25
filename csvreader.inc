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

class CsvReader {
    private $utf8_handle;
    private $sep;

    const UTF8_BOM = "\xef\xbb\xbf";
    const UTF16BE_BOM = "\xfe\xff";
    const UTF16LE_BOM = "\xff\xfe";

    // Sets $utf8_handle to point to a UTF-8 encoded file with $sep as field separator
    function __construct($filename) {
        $input_handle = fopen($filename, 'r');
        if ($input_handle===false)
            throw new DataException('System error: Cannot open file');

        $bom = fread($input_handle,3);

        if ($bom == CsvReader::UTF8_BOM) // This is a UTF-8 file
            $this->utf8_handle = $input_handle;
        else {
            fseek($input_handle,0);
            $bom = fread($input_handle,2);
            if ($bom == CsvReader::UTF16BE_BOM) 
                $from = "UTF-16BE";
            elseif  ($bom == CsvReader::UTF16LE_BOM)
                $from = "UTF-16LE";
            else {
                // Assume Windows-1252
                fseek($input_handle,0);
                $from = "cp1252";
            }

            $this->utf8_handle = tmpfile();
            fwrite($this->utf8_handle, CsvReader::UTF8_BOM);
            while ($buf = fread($input_handle,10000))
                fwrite($this->utf8_handle, iconv($from,'UTF-8',$buf));
            fclose($input_handle);
        }

        fseek($this->utf8_handle, 3); // First position after BOM

        $findsep = fread($this->utf8_handle,50); // This is where we look for a separator

        $find_tab = strstr($findsep,"\x09");
        $find_comma = strstr($findsep,',');

        if (!$find_tab)
            $this->sep = ',';
        elseif (!$find_comma)
            $this->sep = "\x09";
        elseif (strlen($find_tab) > strlen($find_comma)) // Found tab before comma
            $this->sep = "\x09";
        else
            $this->sep = ',';

        fseek($this->utf8_handle, 3); // First position after BOM
    }

    public function read_csv($min_fields=null, $max_fields=null) {
        while (true) {
            $data = fgetcsv($this->utf8_handle,0,$this->sep);
            if (!$data)
                return false;
            if (count($data)==1 && is_null($data[0])) // Blank line, ignore
                continue;

            // Does line contain anything bu separators?
            $found_data = false;
            foreach ($data as $d) {
                if (!empty($d)) {
                    $found_data = true;
                    break;
                }
            }
            if (!$found_data)
                continue;

            if ((!is_null($min_fields) && count($data)<$min_fields) ||
                (!is_null($max_fields) && count($data)>$max_fields))
                throw new DataException('Error in file near text: "' . $this->implode($data) . '"');
            return $data;
        }
    }

    public function implode($data) {
        return implode($data,$this->sep);
    }

    public function close() {
        fclose($this->utf8_handle);
        $this->utf8_handle = null;
    }
}
?>
