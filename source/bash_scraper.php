<?php
  /**
   * bash_scraper.php
   *
   * This script will scrape any information off of a webpage that matches 
   * a desired regular expression.
   *
   * This bash version is to be run from a bash script so it does not provide
   * any output other then an indication of being finished.
   *
   * @version 1.0
   * @author Thomas Nelson <tn90ca@gmail.com>
   * @project WebCrawler
  **/
  
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
	
	/**
   * function cURL
   *
   * This function sets up and runs cURL with a provided url and returns
   * the HTML code of the webpage.
   *
   * @param string $url
   * @return string
  **/
  function curl($url) {
    // Grab global variables from `settings.inc`
    global $USE_CUSTOM_USERAGENT,$CUSTOM_USERAGENT,$DEFAULT_USERAGENT;
    global $RETURN_TRANSFER,$FOLLOW_LOCATION,$AUTO_REFERER,$CONNECT_TIMEOUT,$TIMEOUT,$MAX_REDIRS;
    
    $USER_AGENT = !empty($USE_CUSTOM_USERAGENT) ? $CUSTOM_USERAGENT : $DEFAULT_USERAGENT;
    // Assigning cURL options to an array
    $options = Array(
      CURLOPT_RETURNTRANSFER => $RETURN_TRANSFER, // Setting cURL's option to return the webpage data
      CURLOPT_FOLLOWLOCATION => $FOLLOW_LOCATION, // Setting cURL to follow 'location' HTTP headers
      CURLOPT_AUTOREFERER    => $AUTO_REFERER,    // Automatically set the referer where following 'location' HTTP headers
      CURLOPT_CONNECTTIMEOUT => $CONNECT_TIMEOUT, // Setting the amount of time (in seconds) before the request times out
      CURLOPT_TIMEOUT        => $TIMEOUT,         // Setting the maximum amount of time for cURL to execute queries
      CURLOPT_MAXREDIRS      => $MAX_REDIRS,      // Setting the maximum number of redirections to follow
      CURLOPT_USERAGENT      => $USER_AGENT,      // Setting the useragent
      CURLOPT_URL            => $url,             // Setting cURL's URL option with the $url variable passed into the function
    );
       
    $ch = curl_init();                // Initialising cURL 
    curl_setopt_array($ch, $options); // Setting cURL's options using the previously assigned array data in $options
    $data = curl_exec($ch);           // Executing the cURL request and assigning the returned data to the $data variable
    curl_close($ch);                  // Closing cURL 
    return $data;                     // Returning the data from the function 
  }
  
  /**
   * function is_available
   *
   * This function checks if a provided URL is valid or dead.
   *
   * @param string $url
   * @param int $timeout
   * @return bool
  **/
  function is_available($url, $timeout) {
    // Assigning cURL options to an array
    $options = array(
      CURLOPT_RETURNTRANSFER => true, // Setting cURL's option to return the webpage data
      CURLOPT_NOBODY => true, 		    // Set cURL to only request what is between the <head></head> tags
      CURLOPT_TIMEOUT => $timeout,    // Setting the maximum amount of time for cURL to execute queries
      CURLOPT_URL => $url,            // Setting cURL's URL option with the $url variable passed into the function
    );
    
    $ch = curl_init();                // Initialising cURL 
    curl_setopt_array($ch, $options); // Setting cURL's options using the previously assigned array data in $options
    curl_exec($ch);                   // Executing the cURL request and assigning the returned data to the $data variable
    
    $data = curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200; // Check the HTTP status code for 200(OK Status)
    
    curl_close($ch); // Closing cURL
    return $data;    // Returning the data from the function
  }
	
  /**
   * function scrape_between
   *
   * This function returns all data between two HTML tags to reduce search space.
   *
   * @param string $data
   * @param string $start
   * @param string $end
   * @return string
  **/
	function scrape_between($data, $start, $end){
    $data = stristr($data, $start);        // Stripping all data from before $start
    $data = substr($data, strlen($start)); // Stripping $start
    $stop = stripos($data, $end);          // Getting the position of the $end of the data to scrape
    $data = substr($data, 0, $stop);       // Stripping all data from after and including the $end of the data to scrape
    return $data;                          // Returning the scraped data from the function
  }
  
	// Get the next 30 webpages to scrape in random order to avoid overlap
	$query = "SELECT * FROM $CRAWL_TABLE WHERE scraped=0 order by rand() LIMIT $SCRAPE_LIMIT";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// Gather all data matching regular expression from web pages
	if($result->num_rows > 0) { // If there are pages to scrape in the database
		while($row = $result->fetch_assoc()) { // Loop for each web page found in the database
      // Assign URL from database
			$url = stripslashes($row['url']);
      // Set URL as being scraped in the database
      $mysqli->query("UPDATE $CRAWL_TABLE SET scraped=1 WHERE url='$url'");
      // Check if the URL is a valid link
      if(is_available($url, 10)) {
        // Downloading the results page using our curl() funtion
        $results_page = curl($url);
        // Reduce search space by croping data between the body tags
        $results_page = scrape_between($results_page, "<body", "</body>");
        $matches = array(); // Empty the matches array
        // Find all data on web-page that matches the given regular expression
        preg_match_all($SCRAPER_REGEX, $results_page, $matches);

        
        if(!empty($matches[1])) { // If there are matches
          foreach($matches[1] as $data) { // For each match on the page insert the data into the database
            $mysqli->query("INSERT IGNORE INTO $SCRAPE_TABLE (data,  url) VALUES ('".$data."', '".$host['host']."')");
          }
        }
      } else {
        // If the url is not a valid link then delete it from the database
        $mysqli->query("DELETE FROM $CRAWL_TABLE WHERE url='$url'");
      }
    }
  } else {
    // If there are no pages left to scrape in the database then mark as empty
    $kill = true;
	}
  
  // Close all database connections
  $result->close();
	mysqli_close($mysqli);
  
  if(!empty($kill)) {
    // Display empty database message
    echo "There are no web-pages left to scrape";
  }
?>