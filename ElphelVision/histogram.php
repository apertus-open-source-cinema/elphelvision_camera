<?php
/*!***************************************************************************
*! FILE NAME  : histogram.php
*! DESCRIPTION: histogram interface for the ElphelVision viewfinder software for Apertus open cinema camera
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

if (!$frame) 
	$frame = elphel_get_frame()-1;

$colors = array(0=>"R",1=>"G",2=>"GB",3=>"B");
$h_arr = elphel_histogram_get(0xfff, $frame);
$offset2sum=1024+255; /// last in cumulative histogram for the same color

// scale histograms always so the highest peak is not clipping
$highest_value = 1;
for ($a=0; $a<768; $a++) {
	if ($highest_value < $h_arr[$a])
		$highest_value = $h_arr[$a];
}

$a=0;
// RED
$color = 0;
//printf("\npercentile for color %s:", $colors[$color]);
for ($i=0; $i<256; $i++) {
	printf ("%d;", $h_arr[$a]/$highest_value*256);		
	//printf ("%d;", $h_arr[$a] / $pixelcount * 100);
	$a++;
}

// GREEN
$color = 1;
//printf("\npercentile for color %s:", $colors[$color]);
for ($i=0; $i<256; $i++) {
	printf ("%d;", $h_arr[$a]/$highest_value*256);		
	//printf ("%d;", $h_arr[$a] / $pixelcount * 100);
	$a++;
}

// skip GB
$a += 255;

// BLUE
$color = 3;
//printf("\npercentile for color %s:", $colors[$color]);
for ($i=0; $i<256; $i++) {
	printf ("%d;", $h_arr[$a]/$highest_value*256);		
	//printf ("%d;", $h_arr[$a] / $pixelcount * 100);
	$a++;
}

?>
