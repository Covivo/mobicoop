<?php

namespace App\DataProvider\Entity\MobConnect\AuthenticationProvider;

use App\DataProvider\Interfaces\AuthenticationProviderInterface;
use App\Incentive\Interfaces\EecProviderInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @var EecProviderInterface
     */
    protected $_provider;

    public function getResponse(): Response
    {
        return $this->response;
    }
}
