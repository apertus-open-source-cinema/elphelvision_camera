<?php
/*!***************************************************************************
*! FILE NAME  : project_interface.php
*! DESCRIPTION: interface for the ElphelVision viewfinder software to manage projects and file structure for Apertus open cinema camera
*! Copyright (C) 2011 Apertus
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

$cmd = $_GET['cmd'];


if ($cmd == "list") { // list all projects
	if (isset($_GET['path']))
		$path = $_GET['path'];		
	else
		$path = "Projects/";

	header("Content-Type: text/xml");
	header("Pragma: no-cache\n");
	echo "<?xml version=\"1.0\"?>\r\n";
	echo "<projects>\r\n";

	if ($handle = opendir($path)) {
		while (false !== ($file = readdir($handle))) {
			if (($file != "..") && ($file != ".")) {
				$fh = fopen($path."/".$file, 'r');
				$content = fread($fh, filesize($path."/".$file));
				fclose($fh);
				$lines = explode("\r\n", $content);
				foreach ($lines as $line) {
					$parts = explode("=", $line);
					$key = $parts[0];
					$value = $parts[1];
					if ($key == "projectname") {
						$projectname = $value;
					} else if ($key == "projectfps") {
						$projectfps = $value;
					} else if ($key == "totalclips") {
						$projecttotalclips = $value;
					} else if ($key == "creationdate") {
						$projectcreationdate = $value;
					}
				}

				echo "<project>\r\n";
				echo "<name>".$projectname."</name>\r\n";
				echo "<fps>".$projectfps."</fps>\r\n";
				echo "<totalclips>".$projecttotalclips."</totalclips>\r\n";
				echo "<creationdate>".$projectcreationdate."</creationdate>\r\n";
				echo "</project>\r\n";
			}
		}
	echo "</projects>\r\n";
    	closedir($handle);
	}


} else if ($cmd == "add") { //add a new project and create folder structure
	//validate	
	if (!isset($_GET['name'])) {
		die("error - no project name specified");
	}
	if (!isset($_GET['fps'])) {
		die("error - no project fps specified");
	}
	if (!isset($_GET['hddroot'])) {
		die("error - no HDD root mount point specified");
	}
	// check if HDD is mounted
	$hdd_mounted = false;
	exec('mount', $mount_ret); 
	if (strpos(implode("", $mount_ret), $_GET['hddroot']))
		$hdd_mounted = true;
	if (!$hdd_mounted) {
		die("error - HDD not mounted or connected");
	}

	//create folder structure
	$newfoldername = $_GET['hddroot']."/".$_GET['name'];
	if (file_exists($newfoldername)) {
		die("error - folder already exists");
	}
	echo "creating new folder: ".$newfoldername;
	if (mkdir($newfoldername))
		echo " successful\r\n";
	else
		die (" failed");


	//collect conf file content
	$conffilecontent  = "Apertus - open source cinema (www.apertus.org)\r\n";
	$conffilecontent .= "Project Configuration File\r\n";
	$conffilecontent .= "projectname=".$_GET['name']."\r\n";
	$conffilecontent .= "projectfps=".$_GET['fps']."\r\n";
	$conffilecontent .= "totalclips=0\r\n";
	$conffilecontent .= "creationdate=".date("U")."\r\n";

	//create conf files
	//on HDD
	$metafile = $newfoldername."/meta.txt";
	if (file_exists($metafile)) {
		die("error - file:".$metafile." already exists");
	}
	$fh1 = fopen($metafile, 'w') or die("can't open file: ".$metafile);
	if ($byteswritten = fwrite($fh1, $conffilecontent))
		echo "wrote project config file: ".$metafile." (".$byteswritten." bytes) successfully\r\n";
	else
		echo "error writing project config file: ".$metafile;
	fclose($fh1);

	//and in cameras flash memory
	if (!file_exists("Projects/")) {
		mkdir("Projects");
		echo "did not find Projects folder on camera - created it\r\n";
	}
	$projectfile = "Projects/".$_GET['name'].".conf";
	if (file_exists($projectfile)) {
		die("error - file:".$projectfile." already exists");
	}
	$fh2 = fopen($projectfile, 'w') or die("can't open file: ".$projectfile);
	if ($byteswritten = fwrite($fh2, $conffilecontent))
		echo "wrote project config file: ".$projectfile." (".$byteswritten." bytes) successfully\r\n";
	else
		echo "error writing project config file: ".$projectfile;
	fclose($fh2);
	
	echo "Configfile Content:\r\n";
	echo $conffilecontent;
} else {
	die("no ?cmd=... parameter specified");
}

?>
