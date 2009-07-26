<?php

$cmd = $_GET['cmd'];

// is camogm running
$camogm_running = false;
exec('ps | grep "camogm"', $arr); 
function low_daemon($v)
{
	return (substr($v, -1) != ']');
}

$p = (array_filter($arr, "low_daemon"));
$check = implode("<br />",$p);

if (strstr($check, "camogm /var/state/camogm_cmd"))
	$camogm_running = true;
else
	$camogm_running = false;


if ($cmd == "run_camogm")
{
	if(!$camogm_running)
		exec('camogm /var/state/camogm_cmd > /dev/null 2>&1 &'); // "> /dev/null 2>&1 &" makes sure it is really really run as a background job that does not wait for input
}

$camogm_state = "none";	
$camogm_fileframeduration = 0;
//camogm data		
if ($camogm_running) {
	$pipe="/var/state/camogm.state";
	$cmd_pipe="/var/state/camogm_cmd";
	$mode=0777;
	if(!file_exists($pipe)) {
		umask(0);
		posix_mkfifo($pipe,$mode);
	}
	$fcmd = fopen($cmd_pipe,"w");
	fprintf($fcmd, "xstatus=%s\n",$pipe);
	fclose($fcmd);
	$status = file_get_contents($pipe);
	
	require_once('xml_simple.php');
	function parse_array($element) 
	{
		global $logdata, $logindex;
		foreach ($element as $header => $value) 
		{
		if (is_array($value)) 
			{
			parse_array($value);
			$logindex++;
		} else 
			{
			$logdata[$logindex][$header] = $value;
		}
		}
	}

	$parser =& new xml_simple('UTF-8');
	$request = $parser->parse($status);

	// catch errors
	$error_code = 0;
	if (!$request) {
		$error_code = 1;
		//echo("XML error: ".$parser->error);
	}

	$logdata = array();
	$logindex = 0;
	parse_array($parser->tree);
	
	// Load data from XML
	$camogm_format = substr($logdata[0]['format'], 1, strlen($logdata[0]['format'])-2);
	$camogm_fileframeduration = substr($logdata[0]['frame_number'], 0, strlen($logdata[0]['frame_number']));
	$camogm_state = substr($logdata[0]['state'], 1, strlen($logdata[0]['state'])-2);
	$record_dir = substr($logdata[0]['prefix'], 1, strlen($logdata[0]['prefix'])-2);
}			
	
header("Content-Type: text/xml");
header("Pragma: no-cache\n");
echo "<?xml version=\"1.0\"?>\n";
echo "<elphel_vision_data>\n";

switch ($cmd) {
	case "camogmstate":
		echo "<camogm_state>".$camogm_state."</camogm_state>";
		break;	

	case "fileframeduration":
		echo "<camogm_fileframeduration>".$camogm_fileframeduration."</camogm_fileframeduration>";
		break;	

	default:
		echo "<image_width>".elphel_get_P_value(ELPHEL_WOI_WIDTH)."</image_width>\n";
		echo "<image_height>".elphel_get_P_value(ELPHEL_WOI_HEIGHT)."</image_height>\n";
		echo "<fps>".elphel_get_P_value(ELPHEL_FP1000S)."</fps>\n";
		echo "<jpeg_quality>".elphel_get_P_value(ELPHEL_QUALITY)."</jpeg_quality>\n";
		
		if ($camogm_running)
			echo "<camogm>running</camogm>";
		else
			echo "<camogm>not running</camogm>";		
		
		
		echo "<camogm_state>".$camogm_state."</camogm_state>";
		
		if ($camogm_running)
			echo "<camogm_format>".$camogm_format."</camogm_format>";
			
		$disk = "/var/hdd";
		
		$hdd_mounted = false;
		exec('mount', $mount_ret); 
		if (strpos(implode("", $mount_ret), "/var/hdd"))
			$hdd_mounted = true;
		
		if ($hdd_mounted) {
			$hdd_totalspace = round(disk_total_space($disk)/1024/1024, 2);
			$hdd_freespace = round(disk_free_space($disk)/1024/1024, 2);
			$hdd_ratio = $hdd_freespace / $hdd_totalspace;
			echo "<record_directory>".$record_dir."</record_directory>";
			echo "<hdd_freespaceratio>".round($hdd_ratio*100, 2)."</hdd_freespaceratio>";
		}
		else
			echo "<hdd_freespaceratio>unmounted</hdd_freespaceratio>";
		
		echo "<camogm_fileframeduration>".$camogm_fileframeduration."</camogm_fileframeduration>";
		break;
}




echo "</elphel_vision_data>\n";	
?>


