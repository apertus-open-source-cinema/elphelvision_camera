<?php
/*!***************************************************************************
*! FILE NAME  : gamma.php
*! DESCRIPTION: interface to gamma values for the ElphelVision viewfinder software for Apertus open cinema camera
*! Copyright (C) 2009 Apertus
*! -----------------------------------------------------------------------------**
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
*! -----------------------------------------------------------------------------**
*!
*/

// get the number (page) of the table currently in use from gamma cache
$gammas_file = fopen("/dev/gamma_cache", "r");
fseek($gammas_file, 0, SEEK_END);
$numberOfEntries = ftell($gammas_file);
fclose($gammas_file);
$gammaStructure = array();
$g_raw = elphel_gamma_get_raw(0);
$g_raw_ul = unpack('V*', $g_raw);
$gammaStructure["num_locked"] = $g_raw_ul[10];
$gammaStructure["locked_col"] =  array ($g_raw_ul[11],$g_raw_ul[12],$g_raw_ul[13],$g_raw_ul[14]);

$page = $gammaStructure["locked_col"][0];

// read gamma table from the page we just got
$g_raw = elphel_gamma_get_raw($page);
$g_raw_ul = unpack('V*', $g_raw);
$a = 11; // skip all the header bits and just start where the gamma table begins

for ($i=0; $i<128; $i++) {
	$d = $g_raw_ul[$a++];
	$Y[$i*2] = (int)(($d & 0xffff)/256); 
	$Y[$i*2+1] = (int)((($d>>16) & 0xffff)/256);
}

for ($j = 0; $j < 256; $j++) {
	echo $Y[$j].";";
}

?>			
