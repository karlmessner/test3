<?PHP
	
	function calc_font_size($text){
	
	// CALCULATE FONT SIZE and LINE-HEIGHT OF NAME BASED ON NAME LENGTH
		// FROM SPOT CHECKING:
		// 50/23 FOR up to 12 
		// 40/35 for 14
		// 35/45 for 17  
		// 30/50 for 20
		// 25/61 for 23
		// 20/81 for 26  (26 is the maximum number of letters in first + space + last in the current Users)
		
	$Name = $text;	
	$nameLen = strlen($Name);
	$fontSize=50;$lineHeight=23;
	if ($nameLen > 12){$fontSize=50;$lineHeight=25;}
	if ($nameLen > 14){$fontSize=35;$lineHeight=35;}
	if ($nameLen > 17){$fontSize=30;$lineHeight=40;}
	if ($nameLen > 20){$fontSize=25;$lineHeight=51;}
	if ($nameLen > 23){$fontSize=20;$lineHeight=61;}
	
	$lineHeight=0;
	
	// wholsale shrinkage for font-to-image testing
	$fontSize = .75 * $fontSize;
	
	return $fontSize;
	}
?>