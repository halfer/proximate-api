<?php

/**
 * Some file services useful for the queue module
 */

namespace Proximate\Service;

class File
{
    public function fileExists($filename)
    {
        return file_exists($filename);
    }

    public function isDirectory($filename)
    {
        return is_dir($filename);
    }

    public function filePutContents($filename, $data)
    {
        return file_put_contents($filename, $data);
    }
}
