<?php
  /**
   * PHP.ini Settings
  **/
  ini_set("memory_limit","256M");
	set_time_limit(0);                   // Sets PHP timeout legnth *0 = unlimited*
	ignore_user_abort(true);             // If set to true finishes current task before stopping
	while(ob_get_level())ob_end_clean(); // Flushes any current output buffers
	ob_implicit_flush(true);             // Flushes output buffer after every echo

  /**
   * Database Login Information
  **/
  $DB_NAME = "yourDBname";
	$DB_HOST = "yourDBhost";
	$DB_USER = "yourDBlogin";
	$DB_PASS = "yourDBpassword";
  
  /**
   * Database Table Information
  **/
  $CRAWL_TABLE  = "crawler_table"; // Crawler database table name
  $SCRAPE_TABLE = "scraper_table"; // Scraper database table name
  
  /**
   * General Settings
  **/
  $CRAWL_DEPTH = 4;
  $CRAWL_SEED  = "http://www.something.com";
  $USE_SEED    = false;
  $CRAWL_LIMIT = 50;
  $CRAWL_RAND  = true;
  
  $SCRAPE_LIMIT = 5;
  $SCRAPE_RAND  = true;
  
  $DEFAULT_TIMEZONE = "America/Toronto";
  
  /**
   * Web-Scraper Regular Expressions
  **/
  $SCRAPER_REGEX = '/([\w+\.]*\w+@[\w+\.]*\w+[\w+\-\w+]*\.\w+)/is';
  // Email Regex /([\w+\.]*\w+@[\w+\.]*\w+[\w+\-\w+]*\.\w+)/is
  // Phone Regex (\d{3}[-\.\s]??\d{3}[-\.\s]??\d{4}|\(\d{3}\)\s*\d{3}[-\.\s]??\d{4}|\d{3}[-\.\s]??\d{4})
  
  /**
   * cURL Settings
  **/
  $RETURN_TRANSFER   = TRUE; // Setting cURL's option to return the webpage data
  $FOLLOW_LOCATION   = TRUE; // Setting cURL to follow 'location' HTTP headers
  $AUTO_REFERER      = TRUE; // Automatically set the referer where following 'location' HTTP headers
  $CONNECT_TIMEOUT   = 120;  // Setting the amount of time (in seconds) before the request times out
  $TIMEOUT           = 120;  // Setting the maximum amount of time for cURL to execute queries
  $MAX_REDIRS        = 5;    // Setting the maximum number of redirections to follow
  $DEFAULT_USERAGENT = "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)";
  $CUSTOM_USERAGENT  = "User-Agent: MyCoolTool/1.1 (http://example.com/MyCoolTool/; MyCoolTool@example.com) BasedOnSuperCrawl/1.0";
  
  $USE_CUSTOM_USERAGENT = false; // Set whether to use custom or default useragent
  
  /**
   * Ivalid list
   *
   * This is a list of all files types that you do not wish to scrape or crawl
  **/
  $invalid = array(
    '#',
    '&',
    '?',
    '%',
    '=',
    '.pdf',
    '.gif',
    '.zip',
    '.png',
    '.jpg',
    '.mp3',
    '.mov',
    '.flv',
    '.wav',
    '.rar',
    '.ppt',
    '.doc',
    '.xls',
    '.m4v',
    '.exe'
  );

  
  /**
   * Database Table Creation Scripts
  **/
  $CRAWL_CREATE  = "CREATE TABLE IF NOT EXISTS `$CRAWL_TABLE` (`url` varchar(200) NOT NULL,`visited` int(11) NOT NULL default '0',`scraped` int(11) NOT NULL default '0',`depth` int(11) NOT NULL default '0',PRIMARY KEY (`url`)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
  $SCRAPE_CREATE = "CREATE TABLE `$SCRAPE_TABLE` (`data` varchar(100),`url` varchar(200),UNIQUE KEY `data` (`data`)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
?>