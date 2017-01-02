<?php

/**
 * Site fetcher service
 */

namespace Proximate\Service;

use Proximate\Exception\SiteFetch as SiteFetchException;

class SiteFetcher
{
    protected $proxy;
    protected $lastLog;

    public function __construct($proxy)
    {
        $this->setProxy($proxy);
    }

    public function execute($url, $urlRegex, $rejectFiles)
    {
        // Here are some optional parameters
        $rejectFilesCmd = $rejectFiles ?
            "--reject \"{$rejectFiles}\"" :
            '';
        $urlRegexCmd = $urlRegex ?
            "--accept-regex \"{$urlRegex}\"" :
            '';

        // Construct a site fetch command. The output-file switch ensures the
        // log output is capturable
        $raw = "
            wget \\
                --output-file /tmp/wget.log \\
                --recursive \\
                --wait 3 \\
                --limit-rate=20K \\
                --delete-after \\
                {$rejectFilesCmd} \\
                {$urlRegexCmd} \\
                -e use_proxy=yes \\
                -e http_proxy={$this->proxy} \\
                {$url}
        ";
        $skipLines = $this->trimBlankLines($raw);
        $command = rtrim($skipLines);

        $ok = $this->runCommand($command);
        if (!$ok)
        {
            throw new SiteFetchException(
                "There was a problem with the site fetch call"
            );
        }
    }

    /**
     * Useful for testing (when the constructor is not called)
     *
     * @param string $proxy
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
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

    /**
     * Runs a system command
     *
     * Note that the log output needs to be sent to /dev/stdout for the output logger to work
     *
     * @param string $command
     * @return boolean
     */
    protected function runCommand($command)
    {
        $this->lastLog = $return = null;
        exec($command, $this->lastLog, $return);

        return $return === 0;
    }

    public function getLastLog()
    {
        return $this->lastLog;
    }
}
