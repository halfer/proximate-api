#!/bin/bash
#
# Should result in a 404 error

curl \
	--verbose \
	http://localhost:8080/index.php/404
echo
