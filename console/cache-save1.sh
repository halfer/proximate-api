#!/bin/bash
#
# Adds a new cache requirement to the queue

JSON=`php -r "echo json_encode(['url' => 'http://www.nimvelo.com/about/careers/', 'path_regex' => '.*(/about/careers/.*)|(/job/.*)']);"`
echo "Input doc: $JSON"
curl \
	--data $JSON \
	--verbose \
	http://localhost:8080/index.php/cache
echo
