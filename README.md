# SuperCrawl

SuperCrawl is a set of PHP web-crawlers and web-scrapers to assist in automated datamining.


## Read More
1. [Web Crawling](http://en.wikipedia.org/wiki/Web_crawler) 
2. [Web Scraping](http://en.wikipedia.org/wiki/Web_scraping)
3. [cURL](http://en.wikipedia.org/wiki/CURL)


## Installation

1. Edit the file `settings.inc` file with your preferences and database connection information.

2. Upload desired scripts to your website along with `settings.inc` and `info.php`.

3. Change the url in the `crawl.sh` and `scrape.sh` files to point to your website.
```bash
site = http://www.yoursite.com/bash_crawler.php
```

4. You can customize the settings and scripts as required.
  **Note**
    The scripts will create the table in the database when you run it for the first time.


## Files

* settings.inc

    This file includes all user settings and information for the web-crawler or web-scraper to use. This file is required to run any of the other scripts.

* info.php

    Displays information to the user about crawled and scraped pages. Auto refreshes every 30 seconds.
    
* bash_crawler.php

    This web-crawler is to be run off-screen on a unix based machine from a bash script.
    
* bash_scraper.php

    This web-scraper is to be run off-screen on a unix based machine from a bash script.
    
* crawl.sh

    A bash shell script to run `bash_crawler.php` off screen in a Unix terminal.
    
* scrape.sh

    A bash shell script to run `bash_crawler.php` off screen in a Unix terminal.


## Changes
