<?php
/* include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';
*/

//echo "file Upload";
define('UPLOAD_PATH_REGISTRATION', 'uploads/registration/');
define('UPLOAD_PATH_PTE', 'uploads/permit_to_enter/');

if (isset($_FILES["reg_uploaded_file"]["name"]))
{
	$name = $_FILES["reg_uploaded_file"]["name"];
	$tmp_name = $_FILES["reg_uploaded_file"]['tmp_name'];
	$error = $_FILES["reg_uploaded_file"]['error'];
	
	if(!empty($name)){
		$location = 'uploads/registration/';
		//Check if Directory Exist
		if(!is_dir($location))
			mkdir($location);
		//uploading file

		try{
			//move_uploaded_file($tmp_name, UPLOAD_PATH_REGISTRATION . $name);
			if(move_uploaded_file($tmp_name, $location.$name)){
				$response['status'] = 'true';
				$response['msg'] = 'File Uploaded';
				//echo json_encode('Uploaded');
				echo json_encode($response);
			}
			else{
				//throw new Exception("Could not upload file");
				$response['status'] = 'false';
				$response['msg'] = 'Upload Failed: Exception Error';
				//echo json_encode('Upload Failed');
				echo json_encode($response);
			}
		}catch(Exception $e){
			$response['status'] = 'false';
			$response['msg'] = 'Upload Failed: Exception Error - ' + $e;
		}

/*					
		if(move_uploaded_file($tmp_name, $location.$name)){
			$response['status'] = 'true';
			$response['msg'] = 'File Uploaded';
			//echo json_encode('Uploaded');
			echo json_encode($response);
		}
		else{
			$response['status'] = 'false';
			$response['msg'] = 'Upload Failed';
			//echo json_encode('Upload Failed');
			echo json_encode($response);
		}
*/
	}
	else {
		$response['status'] = 'false';
		$response['msg'] = 'No File Detected';
		//echo json_encode('Please select file!');
		echo json_encode($response);
	}
	
}
else if (isset($_FILES["pte_uploaded_file"]["name"]))
{
	$name = $_FILES["pte_uploaded_file"]["name"];
	$tmp_name = $_FILES["pte_uploaded_file"]['tmp_name'];
	$error = $_FILES["pte_uploaded_file"]['error'];
	
	if(!empty($name)){
		$location = 'uploads/permit_to_enter/';
		if(!is_dir($location))
			mkdir($location);
		
		//uploading file
		try{
			//move_uploaded_file($tmp_name, UPLOAD_PATH_REGISTRATION . $name);
			if(move_uploaded_file($tmp_name, $location.$name)){
				$response['status'] = 'true';
				$response['msg'] = 'File Uploaded';
				//echo json_encode('Uploaded');
				echo json_encode($response);
			}
			else{
				//throw new Exception("Could not upload file");
				$response['status'] = 'false';
				$response['msg'] = 'Upload Failed: Exception Error';
				//echo json_encode('Upload Failed');
				echo json_encode($response);
			}
		}catch(Exception $e){
			$response['status'] = 'false';
			$response['msg'] = 'Upload Failed: Exception Error - ' + $e;
		}
		
/*					
		if(move_uploaded_file($tmp_name, $location.$name)){
			$response['status'] = 'true';
			$response['msg'] = 'File Uploaded';
			//echo json_encode('Uploaded');
			echo json_encode($response);
		}
		else{
			$response['status'] = 'false';
			$response['msg'] = 'Upload Failed';
			//echo json_encode('Upload Failed');
			echo json_encode($response);
		}
*/
	}
	else {
		$response['status'] = 'false';
		$response['msg'] = 'No File Detected';
		//echo json_encode('Please select file!');
		echo json_encode($response);
	}
	
}
?>