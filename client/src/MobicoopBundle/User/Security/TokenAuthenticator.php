<?php

namespace Mobicoop\Bundle\MobicoopBundle\User\Security;

use Exception;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{
    public const USER_LOGIN_ROUTE = 'user_login';                              // default login route
    public const USER_LOGIN_DELEGATE_ROUTE = 'user_login_delegate';            // login delegate
    public const USER_LOGIN_RESULT_ROUTE = 'user_login_result';                // login then redirect to result
    public const USER_LOGIN_EVENT_ROUTE = 'user_login_event';                  // login then redirect to publish for event
    public const USER_LOGIN_COMMUNITY_ROUTE = 'user_login_community';          // login then redirect to community
    public const USER_LOGIN_PUBLISH_ROUTE = 'user_login_publish';              // login then redirect to publish
    public const USER_LOGIN_TOKEN_ROUTE = 'user_login_token';                  // login using a token
    public const USER_LOGIN_TOKEN_ROUTE_EMAIL = 'user_login_token_email';      // login after email validation
    public const USER_SIGN_UP_VALIDATION_ROUTE = 'user_sign_up_validation';    // signup validation
    public const USER_EMAIL_VALIDATION_ROUTE = 'user_email_form_validation';   // email validation form
    public const USER_LOGIN_SSO_ROUTE = 'user_login_sso';                      // login using sso

    private $dataProvider;
    private $router;

    /**
     * Constructor.
     */
    public function __construct(DataProvider $dataProvider, RouterInterface $router)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(User::class);
        $this->router = $router;
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
    public function supports(Request $request): bool
    {
        switch ($request->get('_route')) {
            case self::USER_LOGIN_ROUTE:
            case self::USER_LOGIN_RESULT_ROUTE:
            case self::USER_LOGIN_EVENT_ROUTE:
            case self::USER_LOGIN_COMMUNITY_ROUTE:
            case self::USER_LOGIN_PUBLISH_ROUTE:
                if (
                    $request->isMethod('POST')
                    && '' != $request->get('email') && '' != $request->get('password')
                ) {
                    $this->dataProvider->setPassword($request->get('password'));
                    $this->dataProvider->setUsername($request->get('email'));

                    return true;
                }
                // no break
            case self::USER_LOGIN_DELEGATE_ROUTE:
                if (
                    $request->isMethod('POST')
                    && '' != $request->get('email') && '' != $request->get('emailDelegate') && '' != $request->get('password')
                ) {
                    $this->dataProvider->setPassword($request->get('password'));
                    $this->dataProvider->setUsername($request->get('email'));
                    $this->dataProvider->setUsernameDelegate($request->get('emailDelegate'));

                    return true;
                }
                // no break
            case self::USER_LOGIN_TOKEN_ROUTE:
            case self::USER_LOGIN_TOKEN_ROUTE_EMAIL:
                if (
                    $request->isMethod('POST')
                    && '' != $request->get('emailToken') && '' != $request->get('email')
                ) {
                    $this->dataProvider->setUsername($request->get('email'));
                    $this->dataProvider->setEmailToken($request->get('emailToken'));

                    return true;
                }
                // no break
            case self::USER_SIGN_UP_VALIDATION_ROUTE:
            case self::USER_EMAIL_VALIDATION_ROUTE:
                if (('' != $request->attributes->get('email') && '' != $request->attributes->get('token'))) {
                    $this->dataProvider->setUsername($request->attributes->get('email'));
                    $this->dataProvider->setEmailToken($request->attributes->get('token'));

                    return true;
                }
                // no break
            case self::USER_LOGIN_SSO_ROUTE:
                if (('' != $request->get('ssoId') && '' != $request->get('ssoProvider'))) {
                    $this->dataProvider->setSsoId($request->get('ssoId'));
                    $this->dataProvider->setSsoProvider($request->get('ssoProvider'));
                    $this->dataProvider->setBaseSiteUri($request->get('baseSiteUri'));

                    return true;
                }
        }

        return false;
    }

    public function authenticate(Request $request): Passport
    {
        if ($user = $this->getUser($request)) {
            return new SelfValidatingPassport(new UserBadge($user->getUsername()), []);
        }

        throw new CustomUserMessageAuthenticationException('Wrong email or password token');
    }

    public function getUser($request)
    {
        // We set the dataProvider to private => will discard the current JWT token
        $this->dataProvider->setPrivate(true);

        try {
            $response = $this->dataProvider->getSpecialCollection('me');
        } catch (Exception $e) {
            if (self::USER_LOGIN_SSO_ROUTE == $request->get('_route')) {
                return new User();
            }

            return null;
        }

        if (null === $response) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            return null;
        }

        if (200 == $response->getCode()) {
            $userData = $response->getValue();

            if (is_array($userData->getMember()) && 1 == count($userData->getMember())) {
                return $userData->getMember()[0];
            }
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        switch ($request->get('_route')) {
            case self::USER_LOGIN_ROUTE:
            case self::USER_LOGIN_DELEGATE_ROUTE:
                if ($targetPath = $request->getSession()->get('_security.main.target_path')) {
                    return new RedirectResponse($targetPath);
                }

                return new RedirectResponse($this->router->generate('home'));

            case self::USER_LOGIN_SSO_ROUTE:
                if (!is_null($token->getUser()->getId())) {
                    return new RedirectResponse($this->router->generate('home'));
                }

                    return new RedirectResponse($this->router->generate('user_login_sso_failed', ['service' => $request->get('ssoProviderName')]));

            case self::USER_LOGIN_RESULT_ROUTE:
                return new RedirectResponse($this->router->generate('carpool_ad_results_after_authentication', ['id' => $request->get('id')]));

            case self::USER_LOGIN_EVENT_ROUTE:
                return new RedirectResponse($this->router->generate('carpool_ad_post_event', ['eventId' => $request->get('eventId')]));

            case self::USER_LOGIN_COMMUNITY_ROUTE:
                return new RedirectResponse($this->router->generate('community_show', ['id' => $request->get('communityId')]));

            case self::USER_LOGIN_PUBLISH_ROUTE:
                return new RedirectResponse($this->router->generate('carpool_ad_post_search'));

            case self::USER_EMAIL_VALIDATION_ROUTE:
                return new RedirectResponse($this->router->generate('user_profile_update', ['tabDefault' => 'mon-profil']));

            default:
                return new RedirectResponse($this->router->generate('carpool_first_ad_post'));
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set('bad-credentials', 'Bad credentials.');

        return null;
    }
}
