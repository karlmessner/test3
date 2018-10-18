<?PHP 
		// CALCULATE FONT SIZE and LINE-HEIGHT OF NAME BASED ON NAME LENGTH
		require_once 'includes/calcFontSize.php';
		
		
		// CONFIG
		
			
		// VARIABLES
			$s=$id;
			$template=file_get_contents("email/templates/Maroon5-email-template.htm");
			$contents=file_get_contents("email/contents/ALPRecipientsEmailContents.php");
			$stylesheet=file_get_contents("media/css/emailcss.css");
			$downloadLink = $shortDownloadLink; 
			$trackingPixel = $_ENV['DOMAIN'] . "open.php?s=".$s;
			
			
			// TITLE CARD OR INTRODUCING NAME
			// IF THEY SUPPLIED TITLE CARD TEXT, SPLIT THAT INTO TWO LINES IF NECESSARY AND CREATE THE TWO LINES.
			// IF NOT, SEND INTRUDUCING AS FIRST LINE AND THEIR NAME AS SECOND LINE

				$firstLineText="Audition Taping:";
				$firstLineSize = 24;
				$secondLineText = $Name;
				$secondLineSize = calc_font_size($Name) ;
			
			// PROFILE PIC OR TITLE CARD?
			// IF THEY UPLOADED A TITLE CARD PICTURE, USE THAT, IF NOT, USE THE PROFILE URL THEY SENT
			$Profile_shot = ($titleCardURL) ? $titleCardURL : $Profile_pic_url;
			
			// ASSEMBLE AND INJECT VARS
			
			$body = str_replace("{{content}}", $contents, $template);
			$variablesToInject = array(
				"stylesheet",
				"firstLineText",
				"firstLineSize",
				"secondLineText",
				"secondLineSize",
				"Role",
				"Title",
				"Profile_shot",
				"s",
				"downloadLink",
				"trackingPixel"
				);
			foreach ($variablesToInject as $thisVar){
				$thisVal = $$thisVar;
				$thisVar = "{{".$thisVar."}}";
				$body = str_replace($thisVar, $thisVal, $body);
			}
			
			$body = stripslashes($body);
		
		// TO, FROM
		$to=$Email;
		$fromEmail = 'Hello@ActorsLaunchpad.com';
		$fromEmail = "submissions@moodcaster.com";
		$fromName = "Moodcaster";
		
		// OVERRIDE RECIPIENT TO ME
		if ($overRideRecipients){ $to="karlmessner@gmail.com";}
		
		//$bcc="submissions@moodcaster.com";
		$subject = "Your audition taping: $Role in $Title by $Name (" . date("m.d.y g:ia") . ")";
		$subject = stripslashes($subject);
		
		if ($vidFileSize>0){
			// don't email unless there is a file attached	
			if ($actuallySendEmail) {
				if ($debug) {echo "sending email...<BR>";}
		
				
				// explode Recipients_emails
				$recipARR = explode(',', $Email);
				
				foreach ($recipARR as $eachEmail){
					
					// APPEND E=EMAILADDRESS FOR EACH DOWNLOAD LINK FOR TRACKING
					$appendedDownloadLink = $downloadLink . '&e=' . urlencode($eachEmail);
					$bodyToSend = str_replace($downloadLink, $appendedDownloadLink, $body);
					
					$email = new \SendGrid\Mail\Mail(); 
					$email->setFrom($fromEmail, $fromName);
					$email->setSubject($subject);
					$email->addTo($eachEmail);
					$email->addContent("text/plain", "You can view your Audition tape here: $shortDownloadLink");
					$email->addContent("text/html", $bodyToSend);
					$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
					try {
					    $response = $sendgrid->send($email);
					    if($debug){
						   echo "<pre>";
					    print $response->statusCode() . "\n";
					    print_r($response->headers());
					    print_r($response->body()) . "\n";
					    echo "</pre>";
					    }
					} catch (Exception $e) {
					    if ($debug) {echo 'Caught exception: '. $e->getMessage() ."\n";}
						}	
										
				} //foreach	
				
			} // if actuallySendEmail
		} // if vidFileSize	
		if ($result){$em_good='1';}
		if ($debug) echo "TO:$to<BR>";
		if ($debug) echo "FROM:$fromEmail<BR>";
		if ($debugBody) echo $body;	
?>