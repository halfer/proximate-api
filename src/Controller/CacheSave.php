<?php

/**
 * Controller to save a single site to the cache
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;

class CacheSave extends Base
{
    public function execute()
    {
        $this->
            getResponse()->
            write("Cache the specified page");
    }
}
