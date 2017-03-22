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
    protected $mappingId;

    public function execute()
    {
        try
        {
            $result = [
                'result' => [
                    'ok' => $this->deleteItem(),
                ]
            ];
            $statusCode = 200;
        }
        catch (\Exception $ex)
        {
            $result = $this->getErrorResponse($e);
            $statusCode = 500;
        }

        return $this->createJsonResponse($result, $statusCode);
    }

    public function setMappingId($mappingId)
    {
        $this->mappingId = $mappingId;
    }

    /**
     * Calls the delete endpoint for the currently set mapping ID
     *
     * @todo Check status from remote call
     * @tood Use a specific exception
     */
    protected function deleteItem()
    {
        if (!$this->mappingId)
        {
            throw new \Exception("No mapping ID set");
        }

        $this->getCurl()->delete('__admin/mappings/' . $this->mappingId);

        return true;
    }
}
