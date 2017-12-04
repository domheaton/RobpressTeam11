<?php

class File {

	public static function Upload($array,$local=false) {
		$f3 = Base::instance();
		extract($array);
		$directory = getcwd() . '/uploads';
		$destination = $directory . '/' . $name;
		$webdest = '/uploads/' . $name;

		//INSECURE UPLOAD VULNERABILITY
		//Change permissions (chmod) from 0666 to 0644 to prevent execution of embedded scripts
		//Add filter to check extensions are image file extensions
		//Rename file with unique id
		//If upload not allowed, default picture is set

		//Uploaded File Properties
		$file_name = $name;
		$file_tmp = $tmp_name;
		$file_size = $size;
		$file_error = $error;

		//Find file extensions
		$file_ext = explode('.', $file_name);
		$file_ext = strtolower(end($file_ext));

		//Allowed file extensions
		$allowed = array('jpg','png','gif');

		//Checks for allowed extension
		if(in_array($file_ext, $allowed)) {
			if($file_error === 0) {
				if($file_size <= 2097152) { //2MB file limit

					//Creates new file name with unique id, ending in an allowed extension
					$file_name_new = uniqid('', true) . '.' . $file_ext;
					$file_destination = $directory . '/' . $file_name_new;
					$file_webdest = '/uploads/' . $file_name_new;

					//Local files get moved
					if($local) {
						if (copy($file_tmp,$file_destination)) {
							chmod($file_destination,0644);
							return $file_webdest;
						} else {
							return false;
						}
					// POSTed files are done with move_uploaded_file
					} else {
						if (move_uploaded_file($file_tmp,$file_destination)) {
							chmod($file_destination,0644);
							return $file_webdest;
						} else {
							return false;
						}
					}

				}
			}
		}
		else {
			\StatusMessage::add('File Not Accepted','danger');
		}
		// //Local files get moved
		// if($local) {
		// 	if (copy($tmp_name,$destination)) {
		// 		chmod($destination,0644);
		// 		return $webdest;
		// 	} else {
		// 		return false;
		// 	}
		// //POSTed files are done with move_uploaded_file
		// } else {
		// 	if (move_uploaded_file($tmp_name,$destination)) {
		// 		chmod($destination,0644);
		// 		return $webdest;
		// 	} else {
		// 		return false;
		// 	}
		//}
	}
}

?>
