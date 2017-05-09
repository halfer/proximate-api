# A Docker build file for "proximate-api" proxy control service
#
# PHP in the container takes it merely to 20M or so, but Supervisor requires
# Python, and this bumps it up to 66M.

FROM alpine:3.5

# Do a system update
RUN apk update

# Install PHP
# "php7-dom" is required by the Symfony crawler
RUN apk --update add php7 php7-dom

# Composer needs all of 'phpX-openssl phpX-json phpX-phar phpX-mbstring' and 'zlib' is
# recommended
RUN apk --update add openssl php7-openssl php7-json php7-phar php7-mbstring php7-zlib
# Pest needs 'php5-curl', clue/socket-raw requires sockets
RUN apk add php7-curl php7-sockets

# Refresh the SSL certs, which seem to be missing
RUN wget -O /etc/ssl/cert.pem https://curl.haxx.se/ca/cacert.pem

# Install Composer
# See https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
COPY install/composer.sh /tmp/composer.sh
RUN chmod u+x /tmp/composer.sh

# Ooh, non-standard PHP binary name
RUN ln -s /usr/bin/php7 /usr/bin/php

# Install Composer
RUN cd /tmp && sh /tmp/composer.sh

# Install dependencies first
COPY composer.json /var/www/
COPY composer.lock /var/www/

# Install deps using Composer (ignore dev deps)
RUN cd /var/www && php /tmp/composer.phar install --no-dev

# -s specify a (null) shell; -D = don't assign a password; -H don't create a home directory
RUN adduser -s /bin/false -D -H proximate

# Create a folder to use as a queue
RUN mkdir -p /remote/queue && \
	chown proximate /remote/queue

# Install main body of source code after other installations, since this will change more often
COPY src /var/www/src
COPY public /var/www/public

# Configure cache and queue folders
RUN ln -s /remote/queue/ /var/www/queue
RUN ln -s /remote/cache /var/www/cache

# The port is:
#
# 8080 - API
EXPOSE 8080

# Copy start script
COPY bin/web-server-start.sh /tmp/bin/

# Use Supervisor as the entry point
ENTRYPOINT ["sh", "/tmp/bin/web-server-start.sh"]
