#!/bin/sh

# Let's make sure our cache is writeable by us
chown proximate /remote/cache/data

# Start supervisord
supervisord --nodaemon --configuration /etc/supervisord.conf
