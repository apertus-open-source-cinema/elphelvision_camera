<?

if (!$frame) 
	$frame=elphel_get_frame()-1;

$colors = array(0=>"R",1=>"G",2=>"GB",3=>"B");
$h_arr = elphel_histogram_get(0xfff,$frame);
$a=0;
$offset2sum=1024+255; /// last in cumulative histogram for the same color
//echo "<pre>\n";
//for ($color=1;$color<4;$color++) {
	//printf("%s:", $colors[1]);
	for ($i=0; $i<256; $i++) {
		printf ("%d;",$h_arr[$a++]);
	}
//	printf ("\n");
//}
//echo "</pre>\n";

