#!/bin/bash
site=http://www.yoursite.com/school_scraper.php

for i in {1..10000}
do
  T="$(date +%s)"
  curl  $site
  T="$(($(date +%s)-T))"
  echo "Run ${i} to ${T} seconds"
done 
