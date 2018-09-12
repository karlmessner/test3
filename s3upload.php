<?php
	require './vendor/autoload.php';
	
	// READ ENVIRONMENT VARS
	$awsKey=$_ENV['awsKey'];
	$awsSecret=$_ENV['awsSecret'];	
	
	
	if(isset($_FILES['image'])){
		$file_name = $_FILES['image']['name'];   
		$temp_file_location = $_FILES['image']['tmp_name']; 

		

		$s3 = new Aws\S3\S3Client([
			'region'  => 'us-east-1',
			'version' => 'latest',
			'credentials' => [
				'key'    => "$awsKey",
				'secret' => "$awsSecret",
			]
		]);		

		$result = $s3->putObject([
			'Bucket' => 'mc-vid-submissions',
			'Key'    => $file_name,
			'SourceFile' => $temp_file_location,
			'ACL' => 'public-read'		
		]);

		var_dump($result);
	}
?>

   