<?php
/*! Copyright (C) 2009 Apertus, All Rights Reserved
 *! Author : Apertus Team
 *! Description: php script used to set camera parameters
 *! called by the java application via http request
-----------------------------------------------------------------------------**
 *!
 *!  This program is free software: you can redistribute it and/or modify
 *!  it under the terms of the GNU General Public License as published by
 *!  the Free Software Foundation, either version 3 of the License, or
 *!  (at your option) any later version.
 *!
 *!  This program is distributed in the hope that it will be useful,
 *!  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *!  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *!  GNU General Public License for more details.
 *!
 *!  You should have received a copy of the GNU General Public License
 *!  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *!
-----------------------------------------------------------------------------**/

$param = array();
foreach($_GET as $key => $val) {
    $param[$key] = convert($val);
}

// set parameter for the next frame
$set_frame = elphel_set_P_arr ($param, elphel_get_frame()+1);

// debugging
echo "current frame: ".elphel_get_frame()."\n";
echo "frame with new parameters: ".$set_frame."\n";
echo "Setting parameter "; print_r($param); echo "\n";


function convert($s) {
    // clean up
    $s = trim($s, "\" ");
   
    // check if value is in HEX
    if(strtoupper(substr($s, 0, 2))=="0X")
        return intval(hexdec($s));
    else
        return intval($s);
}

?>


