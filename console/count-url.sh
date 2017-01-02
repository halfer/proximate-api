#!/bin/bash
#
# Runs the URL-specific count endpoint in the API

curl \
	--verbose \
	http://localhost:8080/count/http%3A%2F%2Fwww.nimvelo.com%2F
echo

curl \
	--verbose \
	http://localhost:8080/count/http%3A%2F%2Fwww.google.com%2F
