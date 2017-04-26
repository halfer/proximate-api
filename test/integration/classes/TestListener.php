<?php

namespace Proximate\Test;

use halfer\SpiderlingUtils\TestListener as BaseTestListener;
use halfer\SpiderlingUtils\Server;

/**
 * Listener to turn server on for integration tests
 */

class TestListener extends BaseTestListener
{
    /**
	 * Specifies which suites and namespaces to respond to
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function switchOnBySuiteName($name)
	{
		return
			($name == 'integration') ||
			(strpos($name, 'Proximate\\Test\\Integration\\') !== false);
	}

    /**
     * Set up fake and real API web servers
     */
	public function setupServers()
	{
		$testServer = new Server($this->getFakeServerRoot(), '127.0.0.1:10000');
        $testServer->setServerPidPath('/tmp/proximate-api-server-test.pid');
		$this->addServer($testServer);

        $realServer = new Server($this->getRealServerRoot(), '127.0.0.1:10001');
        $testServer->setServerPidPath('/tmp/proximate-api-server-real.pid');
		$this->addServer($realServer);
	}

    /**
     * Adds some server settling-down time
     *
     * (I could implement a check URL, but this seems to be just fine).
     *
     * @param Server $server
     */
    protected function checkServer(Server $server)
	{
        sleep(1);
    }

	protected function getFakeServerRoot()
	{
		return realpath(__DIR__ . '/../webroots/fake');
	}

    protected function getRealServerRoot()
    {
        return realpath(__DIR__ . '/../webroots/real');
    }
}
