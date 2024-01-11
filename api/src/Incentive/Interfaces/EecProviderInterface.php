<?php

namespace App\Incentive\Interfaces;

interface EecProviderInterface
{
    public function __construct(array $provider);

    public function getName(): string;

    public function getApiUri(): string;

    public function getAuthenticationUri(): string;

    public function getClientId(): string;

    public function geAppId(): string;

    public function geAppSecret(): string;
}
