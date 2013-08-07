<?php
  /**
   * bash_crawler.php
   *
   * This script will crawl through a database list of seed web pages.
   *
   * This bash version is to be run from a bash script so it does not provide
   * any output other then an indication of being finished.
   *
   * @version 1.0
   * @author Thomas Nelson <tn90ca@gmail.com>
   * @project Web Crawler
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
   * function is_valid
   *
   * This function checks if a provided URL is of a valid file type
   *
   * @param string $url
   * @return bool
  **/
  function is_valid($url) {
    global $invalid;
    
    foreach($invalid as $bad) {
      if (stripos($url,$bad) !== false)
        return false;
    }
    return true;
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
	
  /**
   * function rel2abs
   *
   * This function takes a relative URL then finds and returns the absolute URL
   *
   * @param string $rel
   * @param string $base
   * @return string
  **/
	function rel2abs($rel, $base) {
    // Checks if URL is already an absolute link 
    if (parse_url($rel, PHP_URL_SCHEME) != '')
      return $rel; // Return URL if it is already an absolute link
    
    // Checks if URL is an anchor or get query
    if ($rel[0] =='#' || $rel[0] == '?')
      return $base.$rel; // Concatenate URL to base URL is it is an anchor or query
      
    extract(parse_url($base));                    // Parse base URL and convert to local variables
    $path = preg_replace('#/[^/]*$#', '', $path); // Remove non-directory element from path
    
    // Destroy path if URL points to root page
    if ($rel[0] == '/')
      $path = '';
    
     // Absolute dirty URL needs to be cleaned up
    $abs = "$host$path/$rel";

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    $n=1;
    while($n)
      $abs = preg_replace($re, '/', $abs, -1, $n))

    // Return finalized absolute clean URL
    return $scheme.'://'.$abs;
  }
  
	// Get the next 30 webpages to scrape in random order to avoid overlap
	$query = "SELECT * FROM $CRAWL_TABLE WHERE visited=0 order by rand() LIMIT $CRAWL_LIMIT";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// Gather all data matching regular expression from web pages
	if($result->num_rows > 0) { // If there are pages to scrape in the database
		while($row = $result->fetch_assoc()) { // Loop for each web page found in the database
      if(stripslashes($row['depth']) < $CRAWL_DEPTH) {
        // Assign URL from database
        $url = stripslashes($row['url']);
        // Set URL as being scraped in the database
        $mysqli->query("UPDATE $CRAWL_TABLE SET visited=1 WHERE url='$url'");
        // Check if the URL is a valid link
        if(is_available($url, 10)) {
          // Downloading the results page using our curl() funtion
          $results_page = curl($url);
          // Reduce search space by croping data between the body tags
          $results_page = scrape_between($results_page, "<body", "</body>");
          
          $dom = new DOMDocument();               // Create new DOM document
          $dom->loadHTML($results_page);          // Load webpage into DOM Document
          $xPath = new DOMXPath($dom);            // Create DOM XPath to query
          $elements = $xPath->query("//a/@href"); // Query XPath for all links
          
          foreach ($elements as $e) {
            $newrl = rel2abs($e->nodeValue, $url);
            if(is_valid($newrl)) {
              $old = parse_url($url);
              $new = parse_url($newrl);
              if($old['host'] == $new['host']) {
                $mysqli->query("INSERT IGNORE INTO $CRAWL_TABLE (url, visited, scraped, depth) VALUES ('".$newrl."', 0, 0, ".(stripslashes($row['depth'])+1).")");
              }
            }
          }
        } else {
          // If the url is not a valid link then delete it from the database
          $mysqli->query("DELETE FROM $CRAWL_TABLE WHERE url='$url'");
        }
    }
  } else {
    // If there are no pages left to crawl in the database then mark as empty
    $kill = true;
	}
  
  // Close all database connections
  $result->close();
	mysqli_close($mysqli);
  
  if(!empty($kill)) {
    // Display empty database message
    echo "There are no web-pages left to crawl";
  }
?>