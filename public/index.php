<?php

$root = realpath(__DIR__ . '/..');
require $root . '/vendor/autoload.php';

$app = new Slim\App();

/**
 * Counts the number of pages stored in the cache
 */
$app->get('/count', function ($request, $response) {
    $controller = new Proximate\Controller\Count($request, $response);
    $controller->execute();
    return $response;
});

/**
 * Counts the number of pages for a specific domain in the cache
 *
 * This information is available from GET "/__admin/mappings"
 */
$app->get('/count/:url', function ($request, $response) {
    $response->write("Count total pages at this URL base");
    return $response;
});

/**
 * List the pages in the cache, given a page and a page size
 *
 * This can use GET "/__admin/mappings" from the WireMock playback instance
 */
$app->get('/list/{page}/[{pagesize}]', function ($request, $response) {
    $controller = new \Proximate\Controller\CacheList($request, $response);
    $controller->execute();
    return $response;
});

/**
 * Requests that a specific site is cached
 *
 * Adds the item onto the queueing system and returns a unique GUID
 */
$app->post('/cache/{url}', function ($request, $response, $args) {
    $controller = new Proximate\Controller\CacheSave($request, $response);
    $controller->execute();
    return $response;
});

/**
 * Fetches the status of a specific site fetch
 *
 * @todo I think I should just use the URL rather than a GUID, since duplicates won't
 * be separately cached.
 */
$app->get('/status/{guid}', function ($request, $response, $args) {
    $controller = new Proximate\Controller\ItemStatus($request, $response);
    $controller->execute();
    return $response;
});

/**
 * Requests that a specific site is deleted from the cache
 *
 * This can use DELETE "/__admin/mappings/{stubMappingId}" from the WireMock playback instance
 */
$app->delete('/cache/{url}', function ($request, $response, $args) {
    $response->write("Delete the specified page");
    return $response;
});

$app->run();
