<?php

namespace App\DataProvider\Service;

use App\DataProvider\Entity\CarpoolProofGouvProvider;
use App\DataProvider\Entity\CarpoolProofGouvProviderV3;
use App\DataProvider\Entity\CarpoolProofGouvProviderV3_1;
use App\DataProvider\Entity\CarpoolProofGouvProviderV3_2;
use App\DataProvider\Entity\CarpoolProofGouvProviderV3_3;
use App\DataProvider\Service\RPCv3\Tools;
use Psr\Log\LoggerInterface;

class RpcApiManager
{
    public const RPC_API_V2 = 'v2';
    public const RPC_API_V3 = 'v3';
    public const RPC_API_V3_1 = 'v3.1';
    public const RPC_API_V3_2 = 'v3.2';
    public const RPC_API_V3_3 = 'v3.3';

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
     * @var RpcApiAuthenticator|null
     */
    private $_authenticator;

    public function __construct(
        LoggerInterface $logger,
        Tools $tools,
        string $prefix,
        string $rpcApiVersion,
        string $token,
        string $uri,
        RpcApiAuthenticator $authenticator = null
    ) {
        $this->_logger = $logger;
        $this->_tools = $tools;

        $this->_prefix = $prefix;
        $this->_rpcApiVersion = $rpcApiVersion;
        $this->_token = $token;
        $this->_uri = $uri;
        $this->_authenticator = $authenticator;
    }

    /**
     * @return CarpoolProofGouvProvider|CarpoolProofGouvProviderV3
     */
    public function getProvider()
    {
        switch ($this->_rpcApiVersion) {
            case (self::RPC_API_V3):
                return new CarpoolProofGouvProviderV3($this->_tools, $this->_uri, $this->_token, $this->_prefix, $this->_logger);
            case (self::RPC_API_V3_1):
                return new CarpoolProofGouvProviderV3_1($this->_tools, $this->_uri, $this->_token, $this->_prefix, $this->_logger);
            case (self::RPC_API_V3_2):
                return new CarpoolProofGouvProviderV3_2($this->_tools, $this->_uri, $this->_token, $this->_prefix, $this->_logger);
            case (self::RPC_API_V3_3):
                return new CarpoolProofGouvProviderV3_3($this->_tools, $this->_uri, $this->_token, $this->_prefix, $this->_logger, false, $this->_authenticator);
            default:
                return new CarpoolProofGouvProvider($this->_tools, $this->_uri, $this->_token, $this->_prefix, $this->_logger);
        }
    }

    public function isVersion(string $version): bool
    {
        return $this->_rpcApiVersion === $version;
    }
}
