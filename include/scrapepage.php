<?php
  ini_set("memory_limit","256M");
	set_time_limit(0);                   // ignore php timeout
	ignore_user_abort(true);             // keep on going even if user pulls the plug*
	while(ob_get_level())ob_end_clean(); // remove output buffers
	ob_implicit_flush(true);             // output stuff directly
?>

<form action="scrap.php" method="post">
  <input type="text" name="text">
  <br/>
  <input type="submit">
</form>

<?php
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

  if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $url = $_POST['text'];
    $results_page = curl($url);
    $matches = array();
    preg_match_all('/([\w+\.]*\w+@[\w+\.]*\w+[\w+\-\w+]*\.\w+)/is', $results_page, $matches);

    if(!empty($matches[1])) {
      foreach($matches[1] as $data) { 
        echo "Name,$data,N/A<br/>";
      }
    } else {
      echo "No Emails Found!";
    }
  }
?>