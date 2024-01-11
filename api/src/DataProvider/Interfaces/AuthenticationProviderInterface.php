<?php

namespace App\DataProvider\Interfaces;

use App\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;

interface AuthenticationProviderInterface
{
    /**
     * @return bool|string
     */
    public function getToken(?User $user);

    public function getResponse(): Response;
}
