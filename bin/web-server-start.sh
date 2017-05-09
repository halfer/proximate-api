#!/bin/sh

# Let's make sure our cache is writeable by us
chown proximate /remote/cache/data

php -S ${HOSTNAME}:8080 -t /var/www/public
