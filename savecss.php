<?php 
	/* AJAX check  */
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		$file = $_POST["file"];
		$contents = $_POST["contents"];
		
		if(file_exists($file)){
			if(file_put_contents ($file,$contents)){
				echo "UPDATE SUCCESSFUL";
			}
			else{
				echo "UPDATE DID NOT SUCCEED";
			}
		}
	}
	else{
		die("NOT ALLOWED");
	}
?>