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
        $raw = "
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
        $skipLines = $this->trimBlankLines($raw);
        $command = rtrim($skipLines);

        $this->runCommand($command);
    }

    /**
     * Removes empty command lines just containing a backslash and whitespace
     *
     * @param string $text
     * @return string
     */
    protected function trimBlankLines($text)
    {
        return array_reduce(
            explode("\n", $text),
            function($output, $line) {
                if (trim($line) != '\\') {
                    $output .= $line . "\n";
                }
                return $output;
            },
            ''
        );
    }

    protected function runCommand($command)
    {
        system($command);
    }
}
