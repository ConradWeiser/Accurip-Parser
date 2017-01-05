# Accurip Device Offset Databasing

A PHP class which parses through the following website, and populates a MySQL database with each entry.

 * http://www.accuraterip.com/driveoffsets.htm

Expect some updates in the future which may include creating the SQL table that this makes use of, as well as other various improvements.





# Usage

Be sure to setup access to your MYSQL database in the PHP class before running.


```
//Create the class
$acrip = new AccurateRip();

//Be sure to include the URL of the Accurip Offset page 
//Everything is processed and inserted into the database.
$acrip->parseOffsetPage("http://www.accuraterip.com/driveoffsets.htm");