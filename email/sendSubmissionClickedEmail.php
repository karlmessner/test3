<?PHP 		

		// CALCULATE FONT SIZE and LINE-HEIGHT OF NAME BASED ON NAME LENGTH
		require_once 'includes/calcFontSize.php';
		$debug=false;	
		// BODY
			$subject = "🍿 Whoop! They’re watching your audition.";
			$template=file_get_contents("email/templates/Maroon5-email-template.htm");
			$contents=file_get_contents("email/contents/SubmissionClickedEmailContents.php");
			$stylesheet=file_get_contents("media/css/emailcss.css");
			//$s=$id;
			//$trackingPixel = $_ENV['DOMAIN'] . "open.php?s=".$s;
			
						
			$body = str_replace("{{content}}", $contents, $template);
			$variablesToInject = array(
				"stylesheet",
				"Role",
				"Title",
				"shortRecipEmail",
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
		
			// don't email unless there is a file attached	
				if ($debug) {echo "sending email...<BR>";}
						
					$email = new \SendGrid\Mail\Mail(); 
					$email->setFrom($fromEmail, $fromName);
					$email->setSubject($subject);
					$email->addTo($to);
					$email->addContent("text/plain", "Your audition is being watched!");
					$email->addContent("text/html", $body);
					$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
					try {
					    $response = $sendgrid->send($email);
					    if($debug){
						   echo "<pre>LINE 251:";
					    print $response->statusCode() . "\n";
					    print_r($response->headers());
					    print_r($response->body()) . "\n";
					    echo "(251)</pre>";
					    }
					} catch (Exception $e) {
					    if ($debug) {echo 'Caught exception: '. $e->getMessage() ."\n";}
						}	
										
				
		if ($result){$em_good='1';}
		if ($debug) echo "TO:$to<BR>";
		if ($debug) echo "FROM:$fromEmail<BR>";
		if ($debug) echo "BODY:$body<BR>";
		if ($debugBody) echo $body;	
?>