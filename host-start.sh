# Docker command to launch API system

# Get the FQ path of this project
STARTDIR=`pwd`
cd `dirname $0`
ROOTDIR=`pwd`

docker run \
    -p 127.0.0.1:8080:8080 \
    -v ${ROOTDIR}/cache:/remote/cache \
    -v /etc/localtime:/etc/localtime:ro \
    -v /etc/timezone:/etc/timezone:ro \
    -e PHP_TIMEZONE=Europe/London \
    -t \
    proximate-api

# Go back to original dir
cd $STARTDIR
