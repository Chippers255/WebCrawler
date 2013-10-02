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
  
	/* Get the next 30 webpages to craw */
	$query = "SELECT * FROM `schools` WHERE depth=3 LIMIT 500";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	/* Gather all links from each page */
	if($result->num_rows > 0) {
	  while($row = $result->fetch_assoc()) {
	  	$url = stripslashes($row['url']);
	  	$visited = stripslashes($row['visited']);
	  	$scraped = stripslashes($row['scraped']);
	  	$depth = stripslashes($row['depth']);
	  	
        $mysqli->query("INSERT IGNORE INTO schools2 (url, visited, scraped, depth) VALUES ('$url', $visited, $scraped, $depth)") or die($mysqli->error.__LINE__);
        $mysqli->query("DELETE FROM schools WHERE 'url' = '$url'") or die($mysqli->error.__LINE__);
      }
    } else {
      $kill = true;
    }
    $result->close();
	mysqli_close($mysqli);
  
  if(!empty($kill)) {
    echo "There are no web-pages left to crawl";
  } else {
    echo "done!";
  }
?>