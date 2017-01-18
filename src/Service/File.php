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

    public function fileGetContents($filename)
    {
        return file_get_contents($filename);
    }

    public function glob($pattern)
    {
        return glob($pattern);
    }

    public function rename($oldname, $newname)
    {
        rename($oldname, $newname);
    }

    public function copy($pattern, $targetDir)
    {
        foreach ($this->glob($pattern) as $file)
        {
            $targetFile = $targetDir . DIRECTORY_SEPARATOR . basename($file);
            copy($file, $targetFile);
        }
    }

    public function mkdir($pathname)
    {
        return mkdir($pathname);
    }

    public function deleteFiles($path)
    {
        foreach ($this->glob($path . DIRECTORY_SEPARATOR . '*') as $file)
        {
            unlink($file);
        }
    }

    public function rmDir($path)
    {
        rmdir($path);
    }
}
