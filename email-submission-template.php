<?PHP 
$body=file_get_contents("email-template.htm");
$stylesheet=file_get_contents("media/css/emailcss.css");
$downloadLink = $shortDownloadLink; 

// TITLE CARD OR INTRODUCING NAME
// IF THEY SUPPLIED TITLE CARD TEXT, SPLIT THAT INTO TWO LINES IF NECESSARY AND CREATE THE TWO LINES.
// IF NOT, SEND INTRUDUCING AS FIRST LINE AND THEIR NAME AS SECOND LINE
if (strlen($title_card_text) >0){
	$firstLineText=$title_card_text;
	$firstLineSize = calc_font_size($title_card_text);
	$secondLineText = '';
	$secondLineSize = 1 ;	
}else{
	$firstLineText="Introducing";
	$firstLineSize = 40;
	$secondLineText = $Name;
	$secondLineSize = calc_font_size($Name) ;
}

// PROFILE PIC OR TITLE CARD?
// IF THEY UPLOADED A TITLE CARD PICTURE, USE THAT, IF NOT, USE THE PROFILE URL THEY SENT
$Profile_shot = ($title_card_url) ? $title_card_url : $Profile_pic_url;

//injections
$variablesToInject = array("stylesheet","firstLineText","firstLineSize","secondLineText","secondLineSize","Role","Title","Profile_shot","fontSize","lineHeight","s","downloadLink");
foreach ($variablesToInject as $thisVar){
	$thisVal = $$thisVar;
	$thisVar = "{{".$thisVar."}}";
	$body = str_replace($thisVar, $thisVal, $body);
}
$body = stripslashes($body);	
?>