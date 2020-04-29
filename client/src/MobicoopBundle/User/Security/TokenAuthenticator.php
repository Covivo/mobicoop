<?php

namespace Mobicoop\Bundle\MobicoopBundle\User\Security;

use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Mobicoop\Bundle\MobicoopBundle\User\Service;

use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    const USER_LOGIN_ROUTE = "user_login";
    const USER_LOGIN_TOKEN_ROUTE = "user_login_token";
    const USER_SIGN_UP_VALIDATION = "user_sign_up_validation";
    const USER_UPDATE_PASSWORD_RESET = "user_update_password_reset";

    private $dataProvider;
    private $router;
    private $flash;

    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider, RouterInterface $router, FlashBagInterface $flash)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(User::class);
        $this->router = $router;
        $this->flash = $flash;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for tuse App\Entity\User;he request. Returning `false` will cause this authenticator
     * to be skipped.
     *
     * Here, we check if we want to log with login route or login token or password reset AND if POST is not empty
     * Cond 1 : come from login, or login token with POST
     * Cond 2 : come from check validation token with GET : when user click on link in email
     */
    public function supports(Request $request)
    {
        return ((in_array($request->get('_route'), [self::USER_LOGIN_ROUTE,self::USER_LOGIN_TOKEN_ROUTE,self::USER_UPDATE_PASSWORD_RESET ]) && $request->isMethod('POST'))
        || ($request->get('_route') == self::USER_SIGN_UP_VALIDATION  && ($request->attributes->get('email') != '' &&  $request->attributes->get('token') != ''))) ? true : false;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     *
     * We passe request
     */
    public function getCredentials(Request $request)
    {
        return $request;
    }

    public function getUser($request, UserProviderInterface $userProvider)
    {



        // We want to login, we set the credentials for the dataProvider
        if ($request->get('_route') == self::USER_LOGIN_ROUTE && $request->get('email') && $request->get('password')) {
            $this->dataProvider->setUsername($request->get('email'));
            $this->dataProvider->setPassword($request->get('password'));

        // We want to login with the token from email, we set the credentials for the dataProvider
        } elseif (($request->get('_route') == self::USER_LOGIN_TOKEN_ROUTE && $request->get('emailToken')) || $request->get('_route') == self::USER_SIGN_UP_VALIDATION) {
            $email =  $request->get('_route') == self::USER_LOGIN_TOKEN_ROUTE ? $request->get('email') : $request->attributes->get('email');
            $emailToken =  $request->get('_route') == self::USER_LOGIN_TOKEN_ROUTE ? $request->get('emailToken') : $request->attributes->get('token');

            $this->dataProvider->setPassword(null);
            $this->dataProvider->setUsername($email);
            $this->dataProvider->setEmailToken($emailToken);

        // We want to login with just the reset password token, we set the credentials for the dataProvider
        } elseif ($request->get('_route') == self::USER_UPDATE_PASSWORD_RESET && $request->attributes->get('token') != '') {
            $this->dataProvider->setPassword(null);
            $this->dataProvider->setPasswordToken($request->attributes->get('token'));
        }
        
        // We set the dataProvider to private => will discard the current JWT token
        $this->dataProvider->setPrivate(true);

        $response = $this->dataProvider->getSpecialCollection("me");

        if (null === $response) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            return null;
        }

        if ($response->getCode() == 200) {
            $userData = $response->getValue();

            if (is_array($userData->getMember()) && count($userData->getMember())==1) {
                return $userData->getMember()[0];
            }
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {

        // Check credentials - e.g. make sure the password is valid.
        // In case of an API token, no credential check is needed.

        // Return `true` to cause authentication success

        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $redirectTo = $request->get('_route') == self::USER_LOGIN_ROUTE ? 'home'  : 'carpool_first_ad_post';
        //If it's a reset password we return the User in json, fit with ajax
        if ($request->get('_route') == self::USER_UPDATE_PASSWORD_RESET) {
            return new JsonResponse($token->getUser());
        }

        return new RedirectResponse($this->router->generate($redirectTo));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->flash->add('notice', 'bad-credentials-api');

        return null;
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            // you might translate this message
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
