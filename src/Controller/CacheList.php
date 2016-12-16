<?php

/**
 * Controller to fetch pages of cache items from the proxy
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;

class CacheList extends Base
{
    public function execute()
    {
        $this->
            getResponse()->
            write("List pages");
    }
}
