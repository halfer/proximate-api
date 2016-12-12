<?php

$root = realpath(__DIR__ . '/..');
require $root . '/vendor/autoload.php';

$app = new Slim\App();

/**
 * Counts the number of pages stored in the cache
 */
$app->get('/count', function ($request, $response) {
    $response->write("Count total pages");
    return $response;
});

/**
 * Counts the number of pages for a specific domain in the cache
 *
 * (Not sure if this is possible)
 */
$app->get('/count/:url', function ($request, $response) {
    $response->write("Count total pages at this URL base");
    return $response;
});

/**
 * List the pages in the cache, given a page and a page size
 */
$app->get('/list/[{page}]/[{pagesize}]', function ($request, $response) {
    $response->write("List pages");
    return $response;
});

/**
 * Requests that a specific site is cached
 */
$app->post('/cache/{url}', function ($request, $response, $args) {
    $response->write("Cache the specified page");
    return $response;
});

/**
 * Requests that a specific site is deleted from the cache
 */
$app->delete('/cache/{url}', function ($request, $response, $args) {
    $response->write("Delete the specified page");
    return $response;
});

$app->run();
