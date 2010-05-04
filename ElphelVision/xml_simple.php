<?
/* xml-simple.php

Simple XML Parser for PHP by Rogers Cadenhead, derived from
original code by Jim Winstead Jr.

Version 1.01
Web: http://www.cadenhead.org/workbench/xml-simple
   
Copyright (C) 2005 Rogers Cadenhead

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. */

if (__FILE__ == $PATH_TRANSLATED) {
    header("Status: 403 Forbidden");
    exit();
}

// a PHP class library that parses XML data
class xml_simple {
    /* an array that holds parsed XML data as either name-value pairs (for character data) or           arrays (for subelements) */
    var $tree = array();
    var $force_to_array = array();
    // a descriptive error message, if the class fails to execute successfully
    var $error = null;

    // Create the XML parser that will read XML data formatted with the specified encoding
    function xml_simple($encoding = 'UTF-8') {
        $this->parser = xml_parser_create($encoding);
        xml_set_object($this->parser, &$this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
        xml_set_element_handler($this->parser, "start_element", "stop_element");
        xml_set_character_data_handler($this->parser, "char_data");
    }

    function force_to_array() {
        for ($i = 0; $i < func_num_args(); $i++) {
            $this->force_to_array[] = func_get_arg($i);
        }
    }

    /* Parse XML data, storing it in the instance variable; returns false if the data cannot be parsed. */
    function parse($data) {
        $this->tree = array();

        if (!xml_parse($this->parser, $data, 1)) {
            $this->error = "xml parse error: " .
                xml_error_string(xml_get_error_code($this->parser)) .
	            " on line ".xml_get_current_line_number($this->parser);
            return false;
        }
        return $this->tree[0]["content"];
    }

    function parse_file($file) {
        $fp = @fopen($file, "r");
        if (!$fp) {
            user_error("unable to open file: '$file'");
            return false;
        }   
        while ($data = fread($fp, 4096)) {
            if (!xml_parse($this->parser, $data, feof($fp))) {
	            user_error("xml parse error: " .
	                xml_error_string(xml_get_error_code($this->parser)) .
	                " on line " . xml_get_current_line_number($this->parser));
                }
            }
            fclose($fp);
            return $this->tree[0]["content"];
        }

    function encode_as_xml($value) {
        if (is_array($value)) {
            reset($value); $out = '';
            while (list($key,$val) = each($value)) {
	            if (is_array($val) && isset($val[0])) {
	                reset($val);
	                while (list(,$item) = each($val)) {
	                    $out .= "<$key>".xml_simple::encode_as_xml($item)."<$key>";
	                }
	            } else {
	                $out .= "<$key>".xml_simple::encode_as_xml($val)."<$key>";
	            }
            }
            return $out;
        } else {
            return htmlspecialchars($value);
        }
    }

    function start_element($parser, $name, $attrs) {
        array_unshift($this->tree, array("name" => $name));
    }

    function stop_element($parser, $name) {
        if ($name != $this->tree[0]["name"]) die("incorrect nesting");
        if (count($this->tree) > 1) {
            $elem = array_shift($this->tree);
            if (isset($this->tree[0]["content"][$elem["name"]])) {
	            if (is_array($this->tree[0]["content"][$elem["name"]]) && isset($this->tree[0]["content"][$elem["name"]][0])) {
	                array_push($this->tree[0]["content"][$elem["name"]], $elem["content"]);
	            } else {
	                $this->tree[0]["content"][$elem["name"]] =
	                array($this->tree[0]["content"][$elem["name"]],$elem["content"]);
	            }
            } else {
	            if (in_array($elem["name"],$this->force_to_array)) {
	                $this->tree[0]["content"][$elem["name"]] = array($elem["content"]);
	            } else {
	                if (!isset($elem["content"])) $elem["content"] = "";
	                $this->tree[0]["content"][$elem["name"]] = $elem["content"];
	            }
            }
        }
    }

    function char_data($parser, $data) {
        # don't add a string to non-string data
        if (!is_string($this->tree[0]["content"]) && !preg_match("/\\S/", $data)) return;
        $this->tree[0]["content"] .= $data;
    }
}
