<?php

/**
 * Site fetcher service
 */

namespace Proximate\Service;

class SiteFetcher
{
    public function execute($url, $urlRegex, $rejectFiles)
    {
        // Here are some optional parameters
        $rejectFilesCmd = $rejectFiles ?
            "--reject \"{$rejectFiles}\"" :
            '';
        $urlRegexCmd = $urlRegex ?
            "--accept-regex \"{$urlRegex}\"" :
            '';        

        // Construct a site fetch command
        $command = "
            wget \\
                --recursive \\
                --wait 3 \\
                --limit-rate=20K \\
                --delete-after \\
                {$rejectFilesCmd} \\
                {$urlRegexCmd} \\
                -e use_proxy=yes \\
                -e http_proxy=127.0.0.1:8082 \\
                {$url}
        ";
        $this->runCommand($command);
    }

    protected function runCommand($command)
    {
        system($command);
    }
}
