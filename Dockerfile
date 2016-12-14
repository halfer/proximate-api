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

# Install source code (minus dependencies)
COPY composer.json /var/www/
COPY composer.lock /var/www/
COPY src /var/www/src
COPY public /var/www/public

# Composer needs all of 'php5-openssl php5-json php5-phar'
RUN apk --update add openssl php5-openssl php5-json php5-phar

# Refresh the SSL certs, which seem to be missing
RUN wget -O /etc/ssl/cert.pem https://curl.haxx.se/ca/cacert.pem

# Install Composer
# See https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
COPY install/composer.sh /tmp/composer.sh
RUN chmod u+x /tmp/composer.sh
RUN cd /tmp && sh /tmp/composer.sh

# Install deps using Composer
RUN cd /var/www && php /tmp/composer.phar install

# The port is:
#
# 8083 - API
EXPOSE 8083

# We need a shell command to interpret the env var
COPY container-start.sh /tmp/
ENTRYPOINT ["sh", "/tmp/container-start.sh"]
