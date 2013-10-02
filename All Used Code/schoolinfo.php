<?php
	$DB_NAME = 'Chippers255';
	$DB_HOST = 'Chippers255.db.10142815.hostedresource.com';
	$DB_USER = 'Chippers255';
	$DB_PASS = '';
	
	$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
	
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
  
  /******* Canadian Schools *******/
  $caemail = 0;
  
  $query = "SELECT COUNT(*) as count FROM `canada_email`";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $caemail = stripslashes($row['count']);
  }
  $result->close();
  
  $query = "SELECT COUNT(*) as count FROM `canada_email_2`";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $caemail += stripslashes($row['count']);
  }
  $result->close();
  /******* Canadian Schools *******/
  
  /******* American Schools *******/
  $usemail = 0;

  $query = "SELECT COUNT(*) as count FROM `states_email`";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $usemail = stripslashes($row['count']);
  }
  $result->close();
  /******* American Schools *******/
  
  /******* Indian Schools *******/
  $inemail = 0;
  
  $query = "SELECT COUNT(*) as count FROM `india_email`";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $inemail = stripslashes($row['count']);
  }
  $result->close();
  /******* Indian Schools *******/
  
  /******* IBO Schools *******/
  $iboemail = 0;
  
  $query = "SELECT COUNT(*) as count FROM `ibo_email`";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $iboemail = stripslashes($row['count']);
  }
  $result->close();
  /******* IBO Schools *******/
  
  /******* UK Schools *******/
  $ukemail = 0;
  
  $query = "SELECT COUNT(*) as count FROM `uk_email`";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $ukemail = stripslashes($row['count']);
  }
  $result->close();
  /******* UK Schools *******/
  
  
	mysqli_close($mysqli);
  
  header("refresh:10; schoolinfo.php");
  date_default_timezone_set("America/Toronto");
  echo "<h2>Time: ".date("Y-m-d H:i:s")."</h2>";
  
  echo "<h3>Canadian School Information</h3>";
  echo "Number of emails for Canadian schools: ".$caemail."<br/><br/>";
  
  echo "<h3>American School Information</h3>";
  echo "Number of emails for American schools: ".$usemail."<br/><br/>";
  
  echo "<h3>Indian School Information</h3>";
  echo "Number of emails for Indian schools: ".$inemail."<br/><br/>";
  
  echo "<h3>International Baccalaureate School Information</h3>";
  echo "Number of emails for International Baccalaureate schools: ".$iboemail."<br/><br/>";
  
  echo "<h3>United Kingdom School Information</h3>";
  echo "Number of emails for United Kingdom schools: ".$ukemail."<br/><br/>";
?>