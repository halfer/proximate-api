#!/bin/sh

CACHE_ROOT_RECORD=/remote/cache/record
CACHE_ROOT_PLAYBACK=/remote/cache/playback

# Assuming there will be no conflicts for now
cp ${CACHE_ROOT_RECORD}/http_www_nimvelo_com/mappings/* ${CACHE_ROOT_PLAYBACK}/mappings
cp ${CACHE_ROOT_RECORD}/http_www_nimvelo_com/__files/* ${CACHE_ROOT_PLAYBACK}/__files
cp ${CACHE_ROOT_RECORD}/http_www_wealthwizards_com/mappings/* ${CACHE_ROOT_PLAYBACK}/mappings
cp ${CACHE_ROOT_RECORD}/http_www_wealthwizards_com/__files/* ${CACHE_ROOT_PLAYBACK}/__files
