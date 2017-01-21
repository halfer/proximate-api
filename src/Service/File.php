<?php

/**
 * Some file services useful for the queue module
 */

namespace Proximate\Service;

use Proximate\Exception\NotWritable as NotWritableException;

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
        if (!is_writable($filename)) {
            throw new NotWritableException(
                sprintf("Could not write to file `%s`", $filename)
            );
        }

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
        $ok = @rename($oldname, $newname);
        if (!$ok)
        {
            throw new NotWritableException(
                sprintf("Could not rename `%s` to `%s`", $oldname, $newname)
            );
        }
    }

    public function copy($pattern, $targetDir)
    {
        foreach ($this->glob($pattern) as $file)
        {
            $targetFile = $targetDir . DIRECTORY_SEPARATOR . basename($file);
            if (!is_writable($targetFile)) {
                throw new NotWritableException(
                    sprintf("Could not copy to file target `%s`", $targetFile)
                );
            }
            copy($file, $targetFile);
        }
    }

    public function mkdir($pathname)
    {
        $ok = @mkdir($pathname);
        if (!$ok)
        {
            throw new NotWritableException(
                sprintf("Could not create folder `%s`", $pathname)
            );
        }
    }

    public function unlinkFile($path)
    {
        if (!is_writable($path)) {
            throw new NotWritableException(
                sprintf("Could not remove file `%s`", $path)
            );
        }

        unlink($path);
    }

    /**
     * @param string $folderPath
     */
    public function unlinkFiles($folderPath)
    {
        foreach ($this->glob($folderPath . DIRECTORY_SEPARATOR . '*') as $file)
        {
            $this->unlinkFiles($file);
        }
    }

    public function rmDir($path)
    {
        if (!is_writable($path)) {
            throw new NotWritableException(
                sprintf("Could not remove directory `%s`", $path)
            );
        }

        rmdir($path);
    }
}
