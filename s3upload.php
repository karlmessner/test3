<?php
	require './vendor/autoload.php';
	
	// READ ENVIRONMENT VARS
	$awsKey=$_ENV['awsKey'];
	$awsSecret=$_ENV['awsSecret'];
	
	
	$fieldname = 'image';
	$bucket = 	'mc-vid-submissions'
	
	
	if(isset($_FILES[$fieldname])){
		$file_name = $_FILES[$fieldname]['name'];   
		$temp_file_location = $_FILES[$fieldname]['tmp_name']; 

		$s3 = new Aws\S3\S3Client([
			'region'  => 'us-east-1',
			'version' => 'latest',
			'credentials' => [
				'key'    => "$awsKey",
				'secret' => "$awsSecret",
			]
		]);		

		$result = $s3->putObject([
			'Bucket' => $bucket,
			'Key'    => $file_name,
			'SourceFile' => $temp_file_location,
			'ACL' => 'public-read'		
		]);

		if ($debug) {echo "<pre>" ; var_dump($result); echo "</pre";}
	}
?>

   