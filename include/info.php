<?php
  // Import all user settings
	include "settings.inc";
	
  // Create a connection the the database
	$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME); 
	
  // Connect to the database
	if (mysqli_connect_errno()) {
    // Displays error message on a failure to connect
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
  
  // Create tables if they do not currently exist
  $mysqli->query($CRAWL_CREATE) or die($mysqli->error.__LINE__);
  $mysqli->query($SCRAPE_CREATE) or die($mysqli->error.__LINE__);
  
  /******* Database Information *******/
  $numcrawl   = 0;
  $num2crawl  = 0;
  $numscrape  = 0;
  $num2scrape = 0;
  $data       = 0;

  // Counts the number of pages previously crawled
  $query = "SELECT COUNT(*) as count FROM `CRAWL_TABLE` WHERE visited=1";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $numcrawl = stripslashes($row['count']);
  }
  $result->close();
  
  // Counts the number of pages left to crawl
  $query = "SELECT COUNT(*) as count FROM `CRAWL_TABLE` WHERE visited=0";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $num2crawl = stripslashes($row['count']);
  }
  $result->close();
  
  // Counts the number of pages previously scraped
  $query = "SELECT COUNT(*) as count FROM `CRAWL_TABLE` WHERE scraped=1";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $numscrape = stripslashes($row['count']);
  }
  $result->close();
  
  // Counts the number of pages left to scrape
  $query = "SELECT COUNT(*) as count FROM `CRAWL_TABLE` WHERE scraped=0";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $num2scrape = stripslashes($row['count']);
  }
  $result->close();
  
  // Counts the amount of data scraped from the web
  $query = "SELECT COUNT(*) as count FROM `SCRAPE_TABLE`";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $data = stripslashes($row['count']);
  }
  $result->close();
  /******* Database Information *******/
  
	mysqli_close($mysqli);
  
  header("refresh:30; info.php");
  date_default_timezone_set($DEFAULT_TIMEZONE);
  echo "<h2>Time: ".date("Y-m-d H:i:s")."</h2>";
  
  echo "<h3>Data Information</h3>";
  echo "Number of web-pages previously crawled: ".$numcrawl."<br/>";
  echo "Number of web-pages previously scrapeded: ".$numscrape."<br/>";
  echo "Number of web-pages left to crawl: ".$num2crawl."<br/>";
  echo "Number of web-pages left to scrape: ".$num2scrape."<br/>";
  echo "Number of usable data found: ".$data."<br/><br/>";
?>