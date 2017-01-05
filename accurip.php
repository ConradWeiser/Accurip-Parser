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
		$stmt = $db->prepare("INSERT IGNORE INTO CD_Drives (name, offset, endorsed) VALUES(:name, :offset, :endorsed)");

		$stmt->bindParam(':name', $cdDrive, PDO::PARAM_STR);
		$stmt->bindParam(':offset', $correctionOffset, PDO::PARAM_INT);
		$stmt->bindParam(':endorsed', $agreeLevel, PDO::PARAM_INT);

		if($stmt->execute()) {

			return true;
		}

		else{

			return false;
		}
	}

	/**
	*
	* Take a string and retrieve the Accurip offset from the SQL database.
	*
	* @param string $driveName The string to compare to the database
	* @return  boolean  if there is no entry or the endormsnet level is below 50% (Can only be false if returned)
	* @return  integer  giving the offset for the device
	*
	*/
	public function getDriveOffset($driveName) {

		$db = $this->createSQLConnection();

		//TODO: Make the SQL DB connection persist as this function will be used often
		$stmt = $db->prepare("SELECT offset, endorsed FROM CD_Drives WHERE (name LIKE :driveName)");

		//Add % tags so the querey runs properly
		$driveName = '%' . $driveName . '%';

		$stmt->bindParam(':driveName', $driveName);

		if($stmt->execute()) {

			$result = $stmt->fetchAll();
			
			//If the result doesn't include any data
			if (empty($result)) {

				return false;
			}

			//If the endorsment level for this Accurip entry is below 50%, don't use it.
			if($result[0][1] <= 50) {

				return false;
			}

			//Otherwise, return the offset
			return $result[0][0];

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
					//Run through and change all multiple whitespace values to just one space
					$driveName = preg_replace('/\s+/', ' ', $element->nodeValue);
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