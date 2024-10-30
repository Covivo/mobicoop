<?php

namespace App\Incentive\Validator;

use App\User\Entity\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class APIAuthenticationValidator
{
    private const ERROR_PATTERN_VALUES = [
        'Access denied',
        'Authorization header not found or invalid',
        'Error verifying token',
        'Invalid refresh token',
        'invalid_grant',
    ];

    public static function isAuthenticationValid(User $user): bool
    {
        return $user->getMobConnectAuth() && $user->getMobConnectAuth()->getValidity();
    }

    public static function isApiAuthenticationError(HttpException $exception): bool
    {
        $pattern = '/('.join(')|(', self::ERROR_PATTERN_VALUES).')/';

        return (bool) preg_match($pattern, $exception);
    }
}
