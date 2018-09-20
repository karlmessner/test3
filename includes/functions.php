<?PHP
function uploadFile ($fieldname,$bucket){	
	// READ ENVIRONMENT VARS
	$awsKey=$_ENV['AWSKEY'];
	$awsSecret=$_ENV['AWSSECRET'];
	$result = '';
	if(isset($_FILES[$fieldname])){
		$file_name = $_FILES[$fieldname]['name']; 
		
		//prepend timestamp to avoid overwrite
		$file_name = microtime() . "_" . $file_name;
		$file_name = urlencode($file_name);
		
		  
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
	} // if
	return $result;	
	echo "<pre>"; print_r($result);echo "</pre>";
} // function uploadFile


?>
