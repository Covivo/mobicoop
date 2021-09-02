<?php

namespace Mobicoop\Bundle\MobicoopBundle\User\Security;

use Exception;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    const USER_LOGIN_ROUTE = "user_login";
    const USER_LOGIN_DELEGATE_ROUTE = "user_login_delegate";
    const USER_LOGIN_RESULT_ROUTE = "user_login_result";
    const USER_LOGIN_EVENT_ROUTE = "user_login_event";
    const USER_LOGIN_TOKEN_ROUTE = "user_login_token";
    const USER_LOGIN_TOKEN_ROUTE_EMAIL = "user_login_token_email";
    const USER_SIGN_UP_VALIDATION_ROUTE = "user_sign_up_validation";
    const USER_EMAIL_VALIDATION_ROUTE = "user_email_form_validation";
    const USER_LOGIN_SSO_ROUTE = "user_login_sso";

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
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     *
     * Here, we check if we want to log with login route or login token or password reset AND if POST is not empty
     * Cond 1 : come from login, or login token with POST
     * Cond 2 : come from check validation token with GET : when user click on link in email
     */
    public function supports(Request $request)
    {
        switch ($request->get('_route')) {
            case self::USER_LOGIN_ROUTE:
            case self::USER_LOGIN_RESULT_ROUTE:
                if (
                    $request->isMethod('POST') &&
                    $request->get('email') != '' && $request->get('password') != ''
                ) {
                    $this->dataProvider->setPassword($request->get('password'));
                    $this->dataProvider->setUsername($request->get('email'));
                    return true;
                }
                // no break
            case self::USER_LOGIN_EVENT_ROUTE:
                if (
                    $request->isMethod('POST') &&
                    $request->get('email') != '' && $request->get('password') != ''
                ) {
                    $this->dataProvider->setPassword($request->get('password'));
                    $this->dataProvider->setUsername($request->get('email'));
                    return true;
                }
                // no break
            case self::USER_LOGIN_DELEGATE_ROUTE:
                if (
                    $request->isMethod('POST') &&
                    $request->get('email') != '' && $request->get('emailDelegate') != '' && $request->get('password') != ''
                ) {
                    $this->dataProvider->setPassword($request->get('password'));
                    $this->dataProvider->setUsername($request->get('email'));
                    $this->dataProvider->setUsernameDelegate($request->get('emailDelegate'));
                    return true;
                }
                // no break
            case self::USER_LOGIN_TOKEN_ROUTE:
                if (
                    $request->isMethod('POST') &&
                    $request->get('emailToken') != '' && $request->get('email') != ''
                ) {
                    $this->dataProvider->setUsername($request->get('email'));
                    $this->dataProvider->setEmailToken($request->get('emailToken'));
                    return true;
                }
                // no break
            case self::USER_LOGIN_TOKEN_ROUTE_EMAIL:
                if (
                    $request->isMethod('POST') &&
                    $request->get('emailToken') != '' && $request->get('email') != ''
                ) {
                    $this->dataProvider->setUsername($request->get('email'));
                    $this->dataProvider->setEmailToken($request->get('emailToken'));
                    return true;
                }
                // no break
            case self::USER_SIGN_UP_VALIDATION_ROUTE:
                if (($request->attributes->get('email') != '' &&  $request->attributes->get('token') != '')) {
                    $this->dataProvider->setUsername($request->attributes->get('email'));
                    $this->dataProvider->setEmailToken($request->attributes->get('token'));
                    return true;
                }
                // no break
            case self::USER_EMAIL_VALIDATION_ROUTE:
                if (($request->attributes->get('email') != '' &&  $request->attributes->get('token') != '')) {
                    $this->dataProvider->setUsername($request->attributes->get('email'));
                    $this->dataProvider->setEmailToken($request->attributes->get('token'));
                    return true;
                }
                // no break
            case self::USER_LOGIN_SSO_ROUTE:
                if (($request->get('ssoId') != '' &&  $request->get('ssoProvider') != '')) {
                    $this->dataProvider->setSsoId($request->get('ssoId'));
                    $this->dataProvider->setSsoProvider($request->get('ssoProvider'));
                    $this->dataProvider->setBaseSiteUri($request->get('baseSiteUri'));
                    return true;
                }
        }
        return false;
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
        // We set the dataProvider to private => will discard the current JWT token
        $this->dataProvider->setPrivate(true);

        try {
            $response = $this->dataProvider->getSpecialCollection("me");
        } catch (Exception $e) {
            return null;
        }

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
        switch ($request->get('_route')) {
            case self::USER_LOGIN_ROUTE:
            case self::USER_LOGIN_DELEGATE_ROUTE:
                if ($targetPath = $request->getSession()->get('_security.main.target_path')) {
                    return new RedirectResponse($targetPath);
                }
                return new RedirectResponse($this->router->generate('home'));
            case self::USER_LOGIN_SSO_ROUTE:
                return new RedirectResponse($this->router->generate('home'));
            case self::USER_LOGIN_RESULT_ROUTE:
                return new RedirectResponse($this->router->generate('carpool_ad_results_after_authentication', ['id'=>$request->get('proposalId')]));
            case self::USER_LOGIN_EVENT_ROUTE:
                return new RedirectResponse($this->router->generate('carpool_ad_post_search', ['eventId'=>$request->get('eventId')]));
            case self::USER_EMAIL_VALIDATION_ROUTE:
                return new RedirectResponse($this->router->generate('user_profile_update', ['tabDefault'=>'mon-profil']));
            default:
                return new RedirectResponse($this->router->generate('carpool_first_ad_post'));
        }
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
