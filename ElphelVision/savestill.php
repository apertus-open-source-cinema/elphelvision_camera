#!/usr/local/sbin/php -q
<?php
/*!
*! PHP script
*! FILE NAME  : savestill.php
*! DESCRIPTION: save a single-frame
*! AUTHOR     : Elphel, Inc.
*! Copyright (C) 2010 Elphel, Inc
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
*/

$starttime = microtime_float();

header("Content-Type: text/xml");
header("Pragma: no-cache\n");

echo "<?xml version=\"1.0\"?>\n";


$imgsrv = 'http://'.$_SERVER['HTTP_HOST'].':8081';
$ahead = 3;
$delay = 5;
$file_prefix = "still_";
$file_extension = ".jpg";
$file_targetdir = "/var/hdd/stills/";
$file_index = 1;
$file_numberpadding = 3;
$pad = "";

$parsForSnap = array('COLOR'    =>  1,
                     'QUALITY'  =>  1);

foreach($_GET as $key=>$value) switch ($key){
	case 'ahead':
		$ahead=myval($value);
		break;
	case 'delay':
		$delay=myval($value);
		break;
	case 'path':
		$file_targetdir = $value;
		break;
	case 'filename':
		$file_prefix = $value;
		break;
	case 'extension':
		$file_extension = $value;
		break;
	default:  /// treat as camera native parameters
		$parsForSnap[$key] = myval($value);
		break;
}

$parsSaved = elphel_get_P_arr($parsForSnap);

//debug
//echo "before:<br><pre>";
//print_r ($parsSaved);
//echo "</pre><br>";

//debug
//echo "for still:<br><pre>";
//print_r ($parsForSnap);
//echo "</pre>";

$thisFrameNumber = elphel_get_frame();
if ($ahead > 5) {
	elphel_wait_frame_abs($thisFrameNumber + $ahead - 5);
	$ahead -= 5;
	$thisFrameNumber = elphel_get_frame();
}

$pgmFrameNumber = $thisFrameNumber + $ahead;

//send modified parameters to the camera
elphel_set_P_arr ($parsForSnap, $pgmFrameNumber);

// wait for the frame to be acquired
elphel_wait_frame_abs($pgmFrameNumber + 2); /// the frame should be in circbuf by then

$circbuf_pointers = elphel_get_circbuf_pointers(1); /// 1 - skip the oldest frame
$meta = end($circbuf_pointers);
if (!count($circbuf_pointers) || ($meta['frame'] < $pgmFrameNumber)) {
	echo "compressor is turned off";
	echo "<pre>\n";print_r($circbuf_pointers);echo "</pre>\n";
	exit (0);
}

// look in the circbuf array (in case we already missed it and it is not the latest)
while($meta['frame'] > $pgmFrameNumber) {
	if (!prev($circbuf_pointers)) { /// failed to find the right frame in circbuf - probably overwritten
		printf ("<pre>could not find the frame %d(0x%x) in the circbuf:\n", $pgmFrameNumber, $pgmFrameNumber);
		print_r ($circbuf_pointers);
		echo "\n</pre>";
		exit (0);
	}
	$meta = current($circbuf_pointers);
}


// create folders

if (!file_exists($file_targetdir))
	exec('mkdir '.$file_targetdir);

if ($file_index > pow(10, $file_numberpadding-1))
	$pad = "";
if ($file_index > pow(10, $file_numberpadding-2))
	$pad = "0";
if ($file_index > pow(10, $file_numberpadding-3))
	$pad = "00";
if ($file_index > pow(10, $file_numberpadding-4))
	$pad = "000";

$path = $file_targetdir.$file_prefix.$pad.$file_index.$file_extension;

while (1) {
	if (!file_exists($path))
		break;
	$file_index++;

	if ($file_index > pow(10, $file_numberpadding-1))
		$pad = "";
	if ($file_index > pow(10, $file_numberpadding-2))
		$pad = "0";
	if ($file_index > pow(10, $file_numberpadding-3))
		$pad = "00";
	if ($file_index > pow(10, $file_numberpadding-4))
		$pad = "000";

	$path = $file_targetdir.$file_prefix.$pad.$file_index.$file_extension;
}

//xml output
echo "<SaveStill>";
echo "<path>".$path."</path>";

// Save Image
//echo $imgsrv.'/'.$meta['circbuf_pointer'].'/bimg';
 exec('wget '.$imgsrv.'/'.$meta['circbuf_pointer'].'/bimg -O '.$path);

echo "<size>".filesize($path)."</size>";

///set original parameters ($delay frames later)
elphel_wait_frame_abs($thisFrameNumber + $delay);
elphel_set_P_arr ($parsSaved,   $pgmFrameNumber + $delay);

$delta_t = round(microtime_float() - $starttime, 3);
echo "<save_duration>".$delta_t."</save_duration>";

echo "</SaveStill>";

function myval ($s) {
  $s = trim($s,"\" ");
  if (strtoupper(substr($s,0,2)) == "0X")   
	return intval(hexdec($s));
  else return intval($s);
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

?>
