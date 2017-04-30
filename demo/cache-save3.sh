#!/bin/bash

JSON=`php -r "echo json_encode(['url' => 'https://www.boxuk.com/about-us/careers', 'path_regex' => '.*(/about-us/careers/.*)']);"`
echo "Input doc: $JSON"
curl \
	--data $JSON \
	--verbose \
	http://localhost:8080/index.php/cache
echo
