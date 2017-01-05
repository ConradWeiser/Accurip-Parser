# Accurip Device Offset Databasing

A PHP class which parses through the following website, and populates a MySQL database with each entry.

 * http://www.accuraterip.com/driveoffsets.htm

Expect some updates in the future which may include creating the SQL table that this makes use of, as well as other various improvements.





# Usage

Be sure to setup access to your MYSQL database in the PHP class before running.

The following example populates the MYSQL database with each of the Accurip entries


```
//Create the class
$acrip = new AccurateRip();

//Be sure to include the URL of the Accurip Offset page 
$acrip->parseOffsetPage("http://www.accuraterip.com/driveoffsets.htm");
```

To check what offset a drive has, you can be returned two different things. 

*A boolean (false) - which is returned when either the drive did not exist in the SQL database, or the endorsment level of the drive is below 50%

*An integer - Which gives the offset of the drive.
```
//Create the class
$acrip = new AccurateRip();

if($offset = $acrip->getDriveOffset($string)) {

//Do code given $offset is the correct number

} else {

//Do error handling
}