<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class AccurateRip {


	private function createSQLConnection() {

		//This will need changed depending on the server it's installed on
		$dsn = 'mysql:host=localhost;dbname=whattest';
		$user = 'test';
		$pass = 'some_pass';

		return $db = new PDO($dsn, $user, $pass);
	}

	private function insertOffsetElement($db, $cdDrive, $correctionOffset, $agreeLevel) {

		//Use a prepared statement to prevent SQL injection
		$stmt = $db->prepare("INSERT IGNORE INTO cd_drives (name, offset, endorsed) VALUES(:name, :offset, :endorsed)");

		$stmt->bindParam(':name', $cdDrive);
		$stmt->bindParam(':offset', $correctionOffset);
		$stmt->bindParam(':endorsed', $agreeLevel);

		if($stmt->execute()) {

			return true;
		}

		else{

			return false;
		}
	}


	public function parseOffsetPage($url) {

		//Get the webpage of the AccurateRip offset list.
		//Suppress errors due to imperfect HTML in the source page
		$doc = new DOMDocument;

		//Check if we're loading the URL properly. If not, exit.
		if(!@$doc->loadHTMLFile($url)) {

			return false;
		}

		$doc->preserveWhiteSpace = false;

		//Grab the table of values and pull each cell
		$table = $doc->getElementsByTagName('table');

		$elements = $table->item(1)->getElementsByTagName('td');

		//Create a database instance to store the data to.
		$db = $this->createSQLConnection();

		//Create a counter value to keep track of what variable goes where
		$counter = 0;
		$driveName = '';
		$offset = '';
		$endorsed = '';

		foreach ($elements as $element) {
			
			switch($counter) {

				case 0: 
					$driveName = $element->nodeValue;
					$counter++;
					break;

				case 1:
					$offset = $element->nodeValue;
					$counter++;
					break;

				case 2:
					//"Submitted By" field. We don't need these.
					$counter++;
					break;

				case 3:
					$endorsed = $element->nodeValue;
					$counter = 0;
					break;

				default:
					return false;
			}

			//If all of the variables have been populated, check that they're not empty and database them
			if($driveName != '' && $offset != '' && $endorsed != '') {



				if ($this->insertOffsetElement($db, $driveName, $offset, $endorsed)) {

					$driveName = '';
					$offset = '';
					$endorsed = '';
				}

			}

		}

	}
}

?>