<?php

namespace App\DataProvider\Entity\MessageBrokerV3;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class MessageBrokerProviderTest extends TestCase
{
    private $_messageBrokerProvider;

    public function setUp(): void
    {
        $uri = 'http://uri';
        $port = 111111;
        $username = 'http://uri';
        $password = 'http://uri';
        $this->_messageBrokerProvider = new MessageBrokerProvider($uri, $port, $username, $password);
    }

    /**
     * @test
     */
    public function testHasUri()
    {
        $this->assertNotNull($this->_messageBrokerProvider->getUri()) && $this->assertIsString($this->_messageBrokerProvider->getUri());
    }
}
