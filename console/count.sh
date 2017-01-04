#!/bin/bash
#
# Runs the count endpoint in the API

curl \
	--verbose \
	http://localhost:8080/index.php/count
echo
