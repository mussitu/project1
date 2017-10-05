<?php
	//Variable containing the title of the upload form
	$title = "Upload your CSV file:";
	//Definition of a Megabyte
	define('MB', 1048576);
	/*
		Helper object to check the file for errors, upload the file and display the file contents
	*/
	class CSVUploader{
		//Variables
		private $csv_file = NULL;		//Contains the file you upload
		private $upload_dir = "uploads/";	//Directory where file will be uploaded
		/*
			Constructor takes one argument which is the file to be uploaded
		*/
		public function __construct($new_csv){	
			//Sets the private csv file variable with the file that was passed in
			$this->csv_file = $new_csv;
		}
		/*
			Upload function checks if the upload directory exists, if it does not 
			it attempts to create it, then uploads the file to the directory.
		*/
		public function upload(){
			echo "-->Uploading your file.<br/>";
			//Check if the upload directory exists.
			if(!is_dir($this->upload_dir)){
				//Advice the user the upload directory does not exist
				echo $this->upload_dir . " directory does not exist.<br/>";
				//And that we will attempt to create it
				echo "Attempting to create " . $this->upload_dir . " directory.<br/>";
				//Attempt to make a new upload directory
				if(!mkdir($this->upload_dir, 0777, true)){
					//If it fails inform the user that the operation failed
					echo "Error creating the " . $this->upload_dir . " directory.<br/>";
				}
			}
			//Move the uploaded CSV file into the upload directory
			if(move_uploaded_file($this->csv_file['tmp_name'],$this->upload_dir . "/" . $this->csv_file['name'])){
				//If the moving operation succeeds the function will return true
				return true;
			}else{
				//Otherwise it will return false
				return false;
			}
		}

		/*
			The display function will attempt to open the uploaded CSV file and display it on a table.
		*/
		public function display(){
			//Inform the user that we will display the file below
			echo "-->Displaying your file below:<br/><br/>";
			//Attempt to open the file in reading mode
			if($file = fopen($this->upload_dir . "/" . $this->csv_file['name'], "r")){
				//If opening the file for reading succeeds
				$current_line = 0;	//Variable to keep track of the line we are displaying
				echo "<table border='1'>";	//Begin the table with an opening table tag
				//Iterate through the lines in the csv file
				while(($line = fgetcsv($file))){
					//For each line in the file
					echo "<tr>";	//Create a table row with an opening table row tag
					//Iterate through all the cells in each line of the CSV file
					foreach($line as $cell){
						if($current_line == 0){
							//If the current line is the first, we display the cells as table head cells
							echo "<th>" . htmlspecialchars($cell) . "</th>";
						}else{
							//Otherwise we display the cells as regular table cells
							echo "<td>" . htmlspecialchars($cell) . "</td>";
						}
					}
					echo "</tr>";		//And once we complete each line, end the table row with a closing table row tag
					$current_line++;	//And increment the current line by one
				}
				//Once we reach the end of the CSV file, close it
				fclose($file);
				//And complete the table display with a closing table tag
				echo "</table>";
			}else{
				//If the file could not be opened for reading inform the user that there was a problem
				echo "There was an error opening the CSV file for reading.<br/>";
			}	
		}
		
		/*
			The check function validates that the file the user is attempting to upload is less than 20MB in size
			and that its file extension is a valid .CSV or .csv extension. If the file validation is successful
			the function returns true, otherwise it returns false.
		*/
		public function check(){
			//Inform the user that the file is being checked for validity
			echo "-->Checking for file validity.<br/>";
			$allowed_extensions = array('csv','CSV');	//Array containing valid csv extensions	
			$csv_filename	= $this->csv_file['name'];	//Variable containing the name of the file
			$csv_size     	= $this->csv_file['size'];	//Variable containing the size of the file
			$csv_extension 	= explode(".",$csv_filename);	//Variable containing the extension of the file
			$max_file_size  = 20; //MB			//Variable containing the maximum allowed file size
			//Check if the file was posted, if it's less than 20MB, and if it has an allowed CSV extension
			if(empty($this->csv_file) || $csv_size > (20 * MB) || !in_array($csv_extension[1], $allowed_extensions)){
				//If the combined check fails	
				$errors = "";	//We create an error variable
				if(empty($this->csv_file)){
					//We check that a file was indeed selected for upload, and if not we append an error message
					//to the errors variable detailing the problem
					$errors .= "Please make sure you select a CSV file for upload before attempting to upload.<br/>";
				}
				if($csv_size > (20 * MB)){
					//We then check that the file is within the 20MB upload limit, and it it is not we append 
					//a message detailing the problem to the error variable.
					$errors .= "The maximum allowed filesize is " . $max_file_size . "MB, please select a smaller file.</br>";
				}
				if(!in_array($csv_extension[1], $allowed_extensions)){
					//We then check that the file's extension is contained in the allowed extensions array
					//And if it is not, we advice the user that the extension is invalid and display the 
					//allowed extensions for reference.
					$errors .= "The file you are trying to upload has the extension '." . $csv_extension[1] . "'.";
					$errors .= "The allowed extensions are:<br/>";
					foreach($allowed_extensions as $ext){
						$errors .= "<font color='orange'>." . $ext . "</font><br/>";
					}
				}
				//Then we display the combined error messages.
				echo $errors;
				//And return false to indicate the file is invalid
				return false;
			}else{
				//If the file passes all the checks, we inform the user that the file is a valid one
				echo "-->File is valid.<br/>";
				//And return true to indicate the file is a valid one
				return true;
			}
		}
	}
	
	//Once the user presses the submit button, the submit button will be posted, we check for this condition
	if(isset($_POST['submit'])){
		//And if the form has been submitted we create an instance of the CVSUploader class
		$uploader = new CSVUploader($_FILES['csvfile']);
		//We use the instance to check the file's validity
		if($uploader->check()){
			//And if the check passes
			//We use the instance of the CVSUploader to attempt to upload the file
			if($uploader->upload()){
				//If the file uploads successfully, we change the title of the form to reflect 
				//the fact a file has already been uploaded 
				$title = "Upload Another CSV File:";
				//And we use the instance of the CSVUploader class to display the contents of the uploaded CSV file.
				$uploader->display();
			}else{
				//If the upload fails, we inform the user what the problem was
				echo "Error uploading the CSV file.";
			}
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Upload/Display CSV</title>
	</head>
	<body>
		<!--We create a form with a file chooser and a submit button-->
		<form action="index.php" method="post" enctype="multipart/form-data">
			<!--We display the title of the form at the top-->
			<h1><?php echo $title;?></h1>
			<!--Create the file chooser input-->
			<input type="file" name="csvfile" id="csvfile"/>
			<br/>
			<br/>
			<!--And the submit button used to submit the form-->
			<input type="submit" value="Upload CSV" name="submit"/>
		</form>
	</body>
</html>
