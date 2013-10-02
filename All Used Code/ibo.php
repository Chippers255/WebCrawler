<?php
  ini_set("memory_limit","256M");
	set_time_limit(0);                   // ignore php timeout
	ignore_user_abort(true);             // keep on going even if user pulls the plug*
	while(ob_get_level())ob_end_clean(); // remove output buffers
	ob_implicit_flush(true);             // output stuff directly
  // <ul class="secondary_navcontainer"><li> </li>
  
  $DB_NAME = 'Chippers255';
	$DB_HOST = 'Chippers255.db.10142815.hostedresource.com';
	$DB_USER = 'Chippers255';
	$DB_PASS = '';
	
	$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
	
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

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
  
  function scrape_between($data, $start, $end){
    $data = stristr($data, $start);        // Stripping all data from before $start
    $data = substr($data, strlen($start)); // Stripping $start
    $stop = stripos($data, $end);          // Getting the position of the $end of the data to scrape
    $data = substr($data, 0, $stop);       // Stripping all data from after and including the $end of the data to scrape
    return $data;                          // Returning the scraped data from the function
  }
  
  for($i=101; $i<=185; $i++) {
    $url = "http://www.ibo.org/school/search/index.cfm?nextStart=$i";
    $results_page = curl($url);
    $results_page = scrape_between($results_page, "</thead>", "</table>");
          
    $dom = new DOMDocument();               // Create new DOM document
    $dom->loadHTML($results_page);          // Load webpage into DOM Document
    $xPath = new DOMXPath($dom);            // Create DOM XPath to query
    $elements = $xPath->query("//a/@href"); // Query XPath for all links
          
    foreach ($elements as $e) {
      $newrl = rel2abs($e->nodeValue, $url);
      echo $newrl."<br/>";
    }
  }
  
	mysqli_close($mysqli);
?>