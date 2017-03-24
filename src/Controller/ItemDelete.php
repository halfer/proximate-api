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

    /**
     * Wiremock appears to delete from memory only, not disk. So I delete from memory
     * using the WM API, and delete from disk manually. I tried deleting from disk and then
     * restarting the server, but this takes the WM server out of service for 4-5 sec.
     *
     * Wiremock should be deleting the files, says the author. @todo look into this!
     * https://github.com/tomakehurst/wiremock/issues/634
     *
     * @return \Slim\Http\Response
     */
    public function execute()
    {
        try
        {
            $result = [
                'result' => [
                    'ok' => $this->deleteItem() && $this->deleteItemByFile(),
                ]
            ];
            $statusCode = 200;
        }
        catch (\Exception $e)
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
     * Deletes the currently set mapping GUID and associated file from disk
     *
     * @todo Use the file service rather than file commands directly
     */
    protected function deleteItemByFile()
    {
        $path = $this->playbackCache . '/mappings/*.json';
        foreach (glob($path) as $jsonFile)
        {
            $found = $this->examineJsonFile($jsonFile);
            if ($found)
            {
                break;
            }
        }

        return true;
    }

    /**
     * @todo Use the file service rather than file commands directly
     */
    protected function examineJsonFile($jsonFile)
    {
        $found = false;

        $json = file_get_contents($jsonFile);
        $data = json_decode($json, true);
        if (isset($data['id']) && $data['id'] === $this->guid)
        {
            $htmlLeaf = $data['response']['bodyFileName'];
            $htmlFile = $this->playbackCache . '/__files/' . $htmlLeaf;
            $this->deleteFiles([$jsonFile, $htmlFile]);
            $found = true;
        }

        return $found;
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
