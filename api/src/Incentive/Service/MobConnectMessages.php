<?php

namespace App\Incentive\Service;

abstract class MobConnectMessages
{
    public const MOB_CONFIG_UNAVAILABLE = 'The sso service configuration ({SERVICE_NAME}) is unavailable!';
    public const MOB_CONNECTION_ERROR = 'User cannot yet be automatically identified to MobConnect! He must first identify himself manually!';
    public const PAYMENT_DATE_MISSING = 'Trip payment date could not be NULL';
    public const SUBSCRIPTION_PARAMETER_MISSING = 'The subscription ID in route parameter must be inserted but it is not defined!';
    public const USER_DRIVING_LICENCE_MISSING = 'The user must have provided their driver\'s license number before they can continue with the CEE subscription process!';
    public const USER_NOT_CARPOOL_DRIVER = 'The given user is not the carpool driver!';
    public const USER_SHORT_DISTANCE_SUBSCRIPTION_MISSING = 'The user short distance subscription is missing';

    public const APP_AUTHENTICATION = 'We encountered a problem while executing the query to get app token.';
    public const USER_AUTHENTICATION_MISSING = 'The user does not have any authentication parameters';
    public const USER_AUTHENTICATION_EXPIRED = 'The user refresh token has expired. User must be reassociated.';
    public const USER_AUTHENTICATION_REFRESH = 'We encountered a problem while executing the token update query.';
    public const TOKEN_MISSING = 'JWT token is not available';

    public const HTTP_CREATION_REQUEST_ERROR = 'The http request to create a subscription of type [TYPE] for user [USER] was unsuccessful and returned the error message: ';
}
