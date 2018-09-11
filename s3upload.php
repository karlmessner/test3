<?php
	if(isset($_FILES['image'])){
		$file_name = $_FILES['image']['name'];   
		$temp_file_location = $_FILES['image']['tmp_name']; 

		require './vendor/autoload.php';

		$s3 = new Aws\S3\S3Client([
			'region'  => 'us-east-1',
			'version' => 'latest',
			'credentials' => [
				'key'    => "AKIAIYK4QWH5PDWMC2XQ",
				'secret' => "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",
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

   