<?
	$pname  = $_GET['parameter'];
	$constant=constant("ELPHEL_$pname");
	echo elphel_get_P_value($constant);
?>
