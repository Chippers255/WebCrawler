#!/bin/bash
ulimit -t unlimited

site=http://www.yoursite.com/bash_scraper.php

for i in {1..10000}
do
  T="$(date +%s)"
  curl  $site
  T="$(($(date +%s)-T))"
  echo "Run ${i} took ${T} seconds...\n\n"
done 
