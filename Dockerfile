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

# The port is:
#
# 8083 - API
EXPOSE 8083

# @todo This needs a folder (and, of course, something to serve)
ENTRYPOINT ["php", "-S", "localhost:8083"]
