<?php

namespace App\DataProvider\Service;

use Psr\Log\LoggerInterface;

class RpcApiAuthenticator
{
    private $accessKey;
    private $secretKey;
    private $authUri;
    private $logger;
    private $accessToken;
    private $tokenFetchedAt;
    private $tokenTTL;
    public const V3_3_AUTH_ENDPOINT = 'v3.3/auth/access_token';

    public function __construct(string $accessKey, string $secretKey, string $authUri, LoggerInterface $logger)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->authUri = $authUri;
        $this->logger = $logger;
        $this->accessToken = null;
        $this->tokenFetchedAt = null;
        $this->tokenTTL = 300; // 5 minutes, selon la documentation de l'API https://tech.covoiturage.beta.gouv.fr/group/endpoint-authentification
    }

    public function getAccessToken(): ?string
    {
        if ($this->accessToken && $this->tokenFetchedAt && (time() - $this->tokenFetchedAt < $this->tokenTTL)) {
            return $this->accessToken;
        }
        return $this->fetchAccessToken();
    }

    private function fetchAccessToken(): ?string
    {
        $body = json_encode([
            'access_key' => $this->accessKey,
            'secret_key' => $this->secretKey,
        ]);
        $ch = curl_init($this->authUri . self::V3_3_AUTH_ENDPOINT);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201 && $response) {
            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                $this->tokenFetchedAt = time();
                $this->logger->info('RpcApiAuthenticator: access_token fetched');
                return $this->accessToken;
            }
        }

        $this->logger->error('RpcApiAuthenticator: failed to fetch access_token, code '.$httpCode);
        return null;
    }
}
