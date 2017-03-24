<?php

/**
 * Controller to view the status of a specified site
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;

class ItemStatus extends Base
{
    public function execute()
    {
        $this->
            getResponse()->
            write("Determines the status of the specified item");
    }

    public function setGuid($guid)
    {
        // todo
    }
}
