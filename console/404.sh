#!/bin/bash
#
# Should result in a 404 error

curl \
	--verbose \
	http://localhost:8080/404
echo
