<?php

/** 
 * Controller to delete a specific site from the cache
 *
 * @todo Decide if this deletes a URL or a whole site? Or should that be two endpoints?
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;

class ItemDelete extends Base
{
    public function execute()
    {
        $this->
            getResponse()->
            write("Delete the specified page");
    }
}
