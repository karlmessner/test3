<?PHP 
		// CALCULATE FONT SIZE and LINE-HEIGHT OF NAME BASED ON NAME LENGTH
		require_once 'includes/calcFontSize.php';
		// EMBED SUBMISSION NUMBER
		
		// BODY
			$subject = "🎥 Cut! We're processing your submission.";
			$template=file_get_contents("email/templates/Maroon5-email-template.htm");
			$contents=file_get_contents("email/contents/SubmissionUploadedEmailContents.php");
			$stylesheet=file_get_contents("media/css/emailcss.css");
			$downloadLink = $shortDownloadLink; 
			$s=$id;
			//$trackingPixel = $_ENV['DOMAIN'] . "open.php?s=".$s;
			
			
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
				"fontSize",
				"lineHeight",
				"s",
				"downloadLink",
				"Name",
				"trackingPixel"
				);
			foreach ($variablesToInject as $thisVar){
				$thisVal = $$thisVar;
				$thisVar = "{{".$thisVar."}}";
				$body = str_replace($thisVar, $thisVal, $body);
			}
			
			$body = stripslashes($body);
		
		// TO, FROM
		$to = $Email;
		$fromEmail = "submissions@moodcaster.com";
		$fromName = "Moodcaster";
		
		// OVERRIDE RECIPIENT TO ME
		if ($overRideRecipients){ $to="karlmessner@gmail.com";}
		
		//$bcc="submissions@moodcaster.com";
		$subject = stripslashes($subject);
		
		if ($zipFileSize>0){
			// don't email unless there is a file attached	
				if ($debug) {echo "sending email...<BR>";}
						
					$email = new \SendGrid\Mail\Mail(); 
					$email->setFrom($fromEmail, $fromName);
					$email->setSubject($subject);
					$email->addTo($to);
					$email->addContent("text/plain", "Your submission has been received");
					$email->addContent("text/html", $body);
					$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
					try {
					    $response = $sendgrid->send($email);
					    if($debug){
						   echo "<pre>LINE 87:";
						    print $response->statusCode() . "\n";
						    print_r($response->headers());
						    print_r($response->body()) . "\n";
						    echo "(87)</pre>";
						    }
					} catch (Exception $e) {
					    if ($debug) {echo 'Caught exception: '. $e->getMessage() ."\n";}
						}	
										
				
		} // if zipsize	
		if ($result){$em_good='1';}
		if ($debug) echo "TO:$to<BR>";
		if ($debug) echo "FROM:$fromEmail<BR>";
		if ($debugBody) echo $body;	
?>