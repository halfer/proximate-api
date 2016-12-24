# A Docker build file for "proximate-api" proxy control service
#
# PHP in the container takes it merely to 20M or so, but Supervisor requires
# Python, and this bumps it up to 66M.
#
# A build server would negate the need for Composer and its dependencies, but the approach
# of building in the container is fine for now.

FROM alpine:3.4

# Do a system update
RUN apk update

# Install PHP
RUN apk --update add php5

# Taken from the "alpine-supervisord-docker" repo
ENV PYTHON_VERSION=2.7.12-r0
ENV PY_PIP_VERSION=8.1.2-r0
ENV SUPERVISOR_VERSION=3.3.0

# Install Supervisor components from the same source
RUN apk add python=$PYTHON_VERSION py-pip=$PY_PIP_VERSION
RUN pip install supervisor==$SUPERVISOR_VERSION

# Composer needs all of 'php5-openssl php5-json php5-phar'
# Pest needs 'php5-curl'
RUN apk --update add openssl php5-openssl php5-json php5-phar php5-curl

# Refresh the SSL certs, which seem to be missing
RUN wget -O /etc/ssl/cert.pem https://curl.haxx.se/ca/cacert.pem

# Install Composer
# See https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
COPY install/composer.sh /tmp/composer.sh
RUN chmod u+x /tmp/composer.sh

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
RUN mkdir -p /var/proximate/queue && \
	chown proximate /var/proximate/queue

# Configure Supervisor
COPY conf/supervisord.conf /etc/supervisord.conf

# Install main body of source code after other installations, since this will change more often
COPY src /var/www/src
COPY public /var/www/public

# The port is:
#
# 8080 - API
EXPOSE 8080

# We need a shell command to interpret the env var
COPY container-start.sh /tmp/

# Use Supervisor as the entry point
ENTRYPOINT ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]
