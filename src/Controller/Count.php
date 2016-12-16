<?php

/**
 * Controller for the count function
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;

class Count extends Base
{
    public function execute()
    {
        $this->
            getResponse()->
            write("Count total pages");
    }
}
