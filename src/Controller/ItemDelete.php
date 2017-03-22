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
    protected $guid;
    protected $playbackCache;

    public function execute()
    {
        try
        {
            $result = [
                'result' => [
                    'ok' => $this->deleteItemByFile(),
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

    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * Calls the delete endpoint for the currently set mapping ID
     *
     * @todo Check status from remote call
     * @tood Use a specific exception
     */
    protected function deleteItem()
    {
        if (!$this->guid)
        {
            throw new \Exception("No GUID set");
        }

        $this->getCurl()->delete('__admin/mappings/' . $this->guid);

        return true;
    }

    public function setPlaybackCache($playbackCache)
    {
        $this->playbackCache = $playbackCache;
    }

    /**
     * @todo Use the file service rather than file commands directly
     */
    protected function deleteItemByFile()
    {
        $path = $this->playbackCache . '/mappings/*.json';
        foreach (glob($path) as $jsonFile)
        {
            $this->examineJsonFile($jsonFile);
        }

        // Soft restart of WM server
        #$this->getCurl()->post('__admin/shutdown');

        return true;
    }

    /**
     * @todo Use the file service rather than file commands directly
     */
    protected function examineJsonFile($jsonFile)
    {
        $json = file_get_contents($jsonFile);
        $data = json_decode($json, true);
        if (isset($data['id']) === $this->guid)
        {
            $htmlLeaf = $data['response']['bodyFileName'];
            $htmlFile = $this->playbackCache . '/__files/' . $htmlLeaf;
            $this->deleteFiles([$jsonFile, $htmlFile]);
        }
    }

    /**
     * @todo Use the file service rather than file commands directly
     */
    protected function deleteFiles(array $files)
    {
        foreach ($files as $file)
        {
            if (file_exists($file))
            {
                unlink($file);
            }
        }
    }
}
