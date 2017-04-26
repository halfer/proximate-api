Proximate/Api
===

[![Build Status](https://travis-ci.org/halfer/proximate-api.svg?branch=master)](https://travis-ci.org/halfer/proximate-api)

Introduction
---

This system contains a HTTP RESTful API to read from, and control, a Proximate recording
proxy. The core of Proximate is called Proximate/Requester, and
[can be found here](https://github.com/halfer/proximate-requester).

Usage
---

The following endpoints are currently implemented:

* `GET /list` - the first page of cache entries (up to 10 items)
* `GET /list/<n>` - the nth page of cache entries (up to 10 items)
* `GET /list/<n>/<c>` - the nth page of cache entries (up to c items)
* `GET /count` - the number of items in the cache
* `GET /cache/<key>` - reads the response of a specific item in the cache
* `DELETE /cache/<key>` - removes a specific item from the cache
* `POST /cache` - crawl a site based on the JSON document sent in the request body (supported values
are `url` for the starting URL, and `path_regex` for a regex to match)

Configuration
---

The project needs access to two directories, which are set up at the root:

* `queue` - contains crawler queue entries
* `cache` - contains a subfolder called `data`, which in turn contains recording files from
the proxy

Each of these directories can be a symlink to the real path if required.
