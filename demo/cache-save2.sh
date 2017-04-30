#!/bin/bash

JSON=`php -r "echo json_encode(['url' => 'http://www.wealthwizards.com/careers/', 'path_regex' => '.*(/careers/.*)']);"`
echo "Input doc: $JSON"
curl \
	--data $JSON \
	--verbose \
	http://localhost:8080/index.php/cache
echo
