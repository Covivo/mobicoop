<?php

namespace App\Incentive\Interfaces;

interface EecProviderInterface
{
    public function __construct(array $provider);

    public function getAppId(): string;

    public function getAppSecret(): string;

    public function getApiUri(): string;

    public function getAuthenticationUri(): string;

    public function getAutoCreateAccount(): bool;

    public function getClientId(): string;

    public function getClientSecret(): string;

    public function getCodeVerifier(): ?string;

    public function getLogoutRedirectUri(): string;

    public function getName(): string;
}
