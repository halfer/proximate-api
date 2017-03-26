<?php

/**
 * Site fetcher service
 */

namespace Proximate\Service;

use Proximate\Exception\SiteFetch as SiteFetchException;

class SiteFetcher
{
    protected $httpProxy;
    protected $httpsProxy;
    protected $lastLog;

    public function __construct($httpProxy, $httpsProxy)
    {
        $this->setProxies($httpProxy, $httpsProxy);
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

        // Construct a site fetch command.
        //
        // output-file: redirects verbose log output. Use /dev/stdout to capture it in PHP
        // directory-prefix: redirect file operations to a writeable location
        // delete-after: the saved files will be deleted when wget finishes
        // no-directories: turns off the hierarchical directory-based save format
        $raw = "
            wget \\
                --output-file /tmp/wget.log \\
                --directory-prefix=/tmp/wget/ \\
                --recursive \\
                --wait 3 \\
                --limit-rate=20K \\
                --delete-after \\
                --no-directories \\
                {$rejectFilesCmd} \\
                {$urlRegexCmd} \\
                -e use_proxy=yes \\
                -e http_proxy={$this->httpProxy} \\
                -e https_proxy={$this->httpsProxy} \\
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
     * @param string $httpProxy
     * @param string $httpsProxy
     */
    public function setProxies($httpProxy, $httpsProxy)
    {
        $this->httpProxy = $httpProxy;
        $this->httpsProxy = $httpsProxy;
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
