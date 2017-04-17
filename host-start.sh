# Outside of Docker Compose, add the following line to this to get it to work:
#
# -v (fully qualified path):/remote/cache \

docker run \
        -p 127.0.0.1:8080:8080 \
        -t \
        proximate-api
