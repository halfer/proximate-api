# Docker command to launch API system

# Get the FQ path of this project
STARTDIR=`pwd`
cd `dirname $0`
ROOTDIR=`pwd`

docker run \
        -p 127.0.0.1:8080:8080 \
        -v ${ROOTDIR}/cache:/remote/cache \
        -t \
        proximate-api

# Go back to original dir
cd $STARTDIR
