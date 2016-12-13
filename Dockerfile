# A Docker build file for "proximate-api" proxy control service
#
# PHP in the container takes it merely to 20M or so, but Supervisor requires
# Python, and this bumps it up to 66M.

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
COPY src /var/www/src
COPY public /var/www/public

# Install Composer
# See https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
# wget https://raw.githubusercontent.com/composer/getcomposer.org/1b137f8bf6db3e79a38a5bc45324414a6b1f9df2/web/installer -O - -q | php -- --quiet

# The port is:
#
# 8083 - API
EXPOSE 8083

# @todo This needs a folder (and, of course, something to serve)
# We're listening to the public IP for the container, can this be obtained from ifconfig?
ENTRYPOINT ["php", "-S", "172.17.0.2:8083", "-t", "/var/www/public"]
