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
  
  $numrow = 0;

	$query = "SELECT * FROM `schools` WHERE depth=1";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	/* Gather all links from each page */
	if($result->num_rows > 0) {
    $numrow = $result->num_rows;
		while($row = $result->fetch_assoc()) {
			$mysqli->query("UPDATE schools SET visited=0  WHERE url='".stripslashes($row['url'])."'");
		}
	}
  $result->close();
  
  $result->close();
	mysqli_close($mysqli);
  
  echo "Number of web-pages crawled this instance: ".$numrow."<br/>";
?>