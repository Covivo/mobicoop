<?php

namespace App\DataProvider\Service;

use App\DataProvider\Entity\CarpoolProofGouvProvider;
use App\DataProvider\Entity\CarpoolProofGouvProviderV3;
use App\DataProvider\Service\RPCv3\Tools;
use Psr\Log\LoggerInterface;

class RpcApiManager
{
    public const RPC_API_V2 = 'v2';
    public const RPC_API_V3 = 'v3';

    public const AVAILABLE_RPC_API_VERSIONS = [self::RPC_API_V2, self::RPC_API_V3];

    /**
     * @var string
     */
    private $_prefix;

    /**
     * @var string
     */
    private $_rpcApiVersion;

    /**
     * @var string
     */
    private $_token;

    /**
     * @var string
     */
    private $_uri;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var Tools
     */
    private $_tools;

    /**
     * @var string
     */
    private $_secret;

    public function __construct(
        LoggerInterface $logger,
        Tools $tools,
        string $prefix,
        string $rpcApiVersion,
        string $token,
        string $uri,
        string $secret
    ) {
        $this->_logger = $logger;
        $this->_tools = $tools;

        $this->_prefix = $prefix;
        $this->_rpcApiVersion = $rpcApiVersion;
        $this->_token = $token;
        $this->_uri = $uri;
        $this->_secret = $secret;
    }

    /**
     * @return CarpoolProofGouvProvider|CarpoolProofGouvProviderV3
     */
    public function getProvider()
    {
        return in_array($this->_rpcApiVersion, self::AVAILABLE_RPC_API_VERSIONS)
        ? (
            $this->isVersion(self::RPC_API_V2)
            ? new CarpoolProofGouvProvider($this->_tools, $this->_uri, $this->_token, $this->_prefix, $this->_logger)
            : new CarpoolProofGouvProviderV3($this->_tools, $this->_uri, $this->_token, $this->_prefix, $this->_logger, $this->_secret)
        )
        : new CarpoolProofGouvProvider($this->_tools, $this->_uri, $this->_token, $this->_prefix, $this->_logger);
    }

    public function isVersion(string $version): bool
    {
        return $this->_rpcApiVersion === $version;
    }
}
