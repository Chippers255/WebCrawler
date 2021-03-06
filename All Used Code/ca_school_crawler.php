<?php
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
  
  $numrow = 0;
  $numcrawl = 0;
  $num2crawl = 0;
  
  /* Get the number of pages already crawled */
  $query = "SELECT COUNT(*) as count FROM `canada_pages` WHERE visited=1";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $numcrawl = stripslashes($row['count']);
  }
  $result->close();
  
	/* Get the next 50 webpages to craw */
	$query = "SELECT * FROM `canada_pages` WHERE visited=0 LIMIT 30";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	/* Gather all links from each page */
	if($result->num_rows > 0) {
  $numrow = $result->num_rows;
		while($row = $result->fetch_assoc()) {
			if(stripslashes($row['depth']) < 1) {
				$url = stripslashes($row['url']);
				
				$results_page = curl($url); // Downloading the results page using our curl() funtion
    		$results_page = scrape_between($results_page, "<body", "</body>"); // Scraping out only the middle section of the results page that contains our results
     
        $dom = new DOMDocument();
        @$dom->loadHTML($results_page);

        $xPath = new DOMXPath($dom);
        $elements = $xPath->query("//a/@href");
        foreach ($elements as $e) {
          $newrl = rel2abs($e->nodeValue, $url);
    			if (stripos($newrl,'#') === false && stripos($newrl,'&') === false && stripos($newrl,'?') === false && stripos($newrl,'%') === false && stripos($newrl,'=') === false) {
    				$old = parse_url($url);
    				$new = parse_url($newrl);
						if($old['host'] != $new['host']) {
							$mysqli->query("INSERT INTO canada_pages (url, visited, scraped, depth) VALUES ('".$newrl."', 0, 0, ".(stripslashes($row['depth'])+1).")");
            }
    			}
    		}
				$mysqli->query("UPDATE canada_pages SET visited=1 WHERE url='".$url."'");
			} else {
        $mysqli->query("UPDATE canada_pages SET visited=1 WHERE url='".stripslashes($row['url'])."'");
      }
		}
	} else {
    $kill = true;
	}
  $result->close();
	
  /* Get the number of pages still to be crawled */
  $query = "SELECT COUNT(*) as count FROM `canada_pages` WHERE visited=0";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
  if($row = $result->fetch_assoc()) {
    $num2crawl = stripslashes($row['count']);
  }
  
  $result->close();
	mysqli_close($mysqli);
  
  if(empty($kill)) {
    header("refresh:3; ca_school_crawler.php");
    echo "Number of web-pages previously crawled: ".$numcrawl."<br/>";
    echo "Number of web-pages crawled this instance: ".$numrow."<br/>";
    echo "Number of web-pages crawled in total: ".($numcrawl+$numrow)."<br/>";
    echo "Number of web-pages left to crawl: ".$num2crawl."<br/>";
  } else {
    echo "There are no web-pages left to crawl";
  }
?>