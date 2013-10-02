<?php
  ini_set("memory_limit","256M");
	set_time_limit(0);                   // ignore php timeout
	ignore_user_abort(true);             // keep on going even if user pulls the plug*
	while(ob_get_level())ob_end_clean(); // remove output buffers
	ob_implicit_flush(true);             // output stuff directly

	$DB_NAME = 'Chippers255';
	$DB_HOST = 'Chippers255.db.10142815.hostedresource.com';
	$DB_USER = 'Chippers255';
	$DB_PASS = '';
	
	$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
	
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	// Defining the basic cURL function
  function curl($url) {
    // Assigning cURL options to an array
    $options = Array(
        CURLOPT_RETURNTRANSFER => TRUE,  // Setting cURL's option to return the webpage data
        CURLOPT_FOLLOWLOCATION => TRUE,  // Setting cURL to follow 'location' HTTP headers
        CURLOPT_AUTOREFERER => TRUE, // Automatically set the referer where following 'location' HTTP headers
        CURLOPT_CONNECTTIMEOUT => 120,   // Setting the amount of time (in seconds) before the request times out
        CURLOPT_TIMEOUT => 120,  // Setting the maximum amount of time for cURL to execute queries
        CURLOPT_MAXREDIRS => 5, // Setting the maximum number of redirections to follow
        CURLOPT_USERAGENT => "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",  // Setting the useragent
        CURLOPT_URL => $url, // Setting cURL's URL option with the $url variable passed into the function
    );
       
    $ch = curl_init();  // Initialising cURL 
    curl_setopt_array($ch, $options);   // Setting cURL's options using the previously assigned array data in $options
    $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
    curl_close($ch);    // Closing cURL 
    return $data;   // Returning the data from the function 
  }
  
  function is_available($url, $timeout = 10) {
    $ch2 = curl_init(); // get cURL handle
    // set cURL options
    $opts = array(CURLOPT_RETURNTRANSFER => true, // do not output to browser
            CURLOPT_URL => $url,            // set URL
            CURLOPT_NOBODY => true, 		  // do a HEAD request only
            CURLOPT_TIMEOUT => $timeout);   // set timeout
    curl_setopt_array($ch2, $opts); 
    curl_exec($ch2); // do it!
    $retval = curl_getinfo($ch2, CURLINFO_HTTP_CODE) == 200; // check if HTTP OK
    curl_close($ch2); // close handle
    return $retval;
  }
	
	function scrape_between($data, $start, $end){
    $data = stristr($data, $start); // Stripping all data from before $start
    $data = substr($data, strlen($start));  // Stripping $start
    $stop = stripos($data, $end);   // Getting the position of the $end of the data to scrape
    $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to scrape
    return $data;   // Returning the scraped data from the function
  }
	
	function rel2abs($rel, $base) {
    if (parse_url($rel, PHP_URL_SCHEME) != '')
      return $rel; // return if already absolute URL
      
    if ($rel[0] =='#' || $rel[0] == '?')
      return $base.$rel; // queries and anchors
      
    extract(parse_url($base)); // parse base URL and convert to local variables
    $path = preg_replace('#/[^/]*$#', '', $path); // remove non-directory element from path
    
    if ($rel[0] == '/')
      $path = ''; // destroy path if relative url points to root

    $abs = "$host$path/$rel"; // dirty absolute URL

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

    return $scheme.'://'.$abs; // absolute URL is ready!
  }
  
	/* Get the next 30 webpages to craw */
	$query = "SELECT * FROM `canada_pages` WHERE scraped=0 order by rand() LIMIT 50";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	/* Gather all emails from each page */
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$url = stripslashes($row['url']);
      $mysqli->query("UPDATE canada_pages SET scraped=1 WHERE url='".$url."'");
      if (stripos($url,'#') === false && stripos($url,'&') === false && stripos($url,'?') === false && stripos($url,'%') === false && stripos($url,'=') === false && stripos($url,'.pdf') === false && stripos($url,'.gif') === false && stripos($url,'.zip') === false && stripos($url,'.png') === false && stripos($url,'.jpg') === false && stripos($url,'.mp3') === false && stripos($url,'.mov') === false && stripos($url,'.flv') === false && stripos($url,'.wav') === false && stripos($url,'.rar') === false && stripos($url,'.ppt') === false && stripos($url,'.doc') === false && stripos($url,'.xls') === false && stripos($url,'.m4v') === false && stripos($url,'.exe') === false) {
        if(is_available($url, 10)) {
          $results_page = curl($url); // Downloading the results page using our curl() funtion
          $results_page = scrape_between($results_page, "<body", "</body>");
          $matches = array();
          $pattern = '/([\w+\.]*\w+@[\w+\.]*\w+[\w+\-\w+]*\.\w+)/is';
          preg_match_all($pattern, $results_page, $matches);
          
          $host = parse_url($url);
          $results_page = curl($host['host']); // Downloading the results page using our curl() funtion
          $title = scrape_between($results_page, "<title>", "</title>");
          
          if(!empty($matches[1])) {
            foreach($matches[1] as $email) {
              $mysqli->query("INSERT INTO canada_email_2 (name, email, url) VALUES ('".trim($title)."', '".$email."', '".$host['host']."')");
            }
          }
        }
      }
    }
  } else {
    $kill = true;
	}
  $result->close();
  mail('tn90ca@gmail.com', 'Scrape Done', 'We have finished scraping your pages good sir', null);
?>