<?php

$key = $_GET['key'];

$param = array();
$param[$_GET['key']] = myval($_GET['value']);

print_r($param);

echo elphel_set_P_arr ($param, elphel_get_frame()+1);

function myval ($s) {
	$s = trim($s, "\" ");
	if(strtoupper(substr($s, 0, 2))=="0X")
		return intval(hexdec($s));
	else 
		return intval($s);
}

?>
