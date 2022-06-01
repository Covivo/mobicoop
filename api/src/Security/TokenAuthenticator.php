<?php

namespace App\Security;

use App\Action\Event\ActionEvent;
use App\Action\Repository\ActionRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{
    private $em;
    private $jwtTokenManagerInterface;
    private $refreshTokenManager;
    private $params;
    private $actionRepository;
    private $eventDispatcher;

    public function __construct(EntityManagerInterface $em, JWTTokenManagerInterface $jwtTokenManagerInterface, RefreshTokenManagerInterface $refreshTokenManager, ParameterBagInterface $params, ActionRepository $actionRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->jwtTokenManagerInterface = $jwtTokenManagerInterface;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->params = $params;
        $this->actionRepository = $actionRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): bool
    {
        return count($this->getCredentials($request)) > 0;
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $this->getCredentials($request);

        if ($user = isset($credentials['emailToken'])
            ? $this->em->getRepository(User::class)->findOneBy(['email' => $credentials['email'], 'emailToken' => $credentials['emailToken']])
            : $this->em->getRepository(User::class)->findOneBy(['pwdToken' => $credentials['passwordToken']])) {
            return new SelfValidatingPassport(new UserBadge($user->getUsername()), []);
        }

        throw new CustomUserMessageAuthenticationException('Wrong email or password token');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /**
         * @var User $user
         */
        $user = $token->getUser();

        // Time for valid refresh token, define in gesdinet_jwt_refresh_token, careful to let this value in secondes
        $addTime = 'PT'.$this->params->get('gesdinet_jwt_refresh_token.ttl').'S';

        $now = new \DateTime('now');
        $now->add(new \DateInterval($addTime));

        $refreshToken = $this->refreshTokenManager->create();
        $refreshToken->setUsername($user->getRefresh());
        $refreshToken->setRefreshToken();
        $refreshToken->setValid($now);

        $this->refreshTokenManager->save($refreshToken);

        // Email token is not null = we activate account from email -> we set token at null and the validated date at today
        if (null != $user->getEmailToken()) {
            $user->setValidatedDate(new \DateTime('now'));
            $user->setEmailToken(null);

            //  we dispatch the gamification event associated
            $action = $this->actionRepository->findOneBy(['name' => 'user_mail_validation']);
            $actionEvent = new ActionEvent($action, $user);
            $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);

        // Password token is not null = we reset password -> we set password token and the asking reset date at null
        } elseif (null != $user->getPwdToken()) {
            $user->setPwdToken(null);
            $user->setPwdTokenDate(null);
        }
        $this->em->persist($user);
        $this->em->flush();

        // on success, let the request continue
        return new JsonResponse([
            'token' => $this->jwtTokenManagerInterface->create($token->getUser()),
            'refreshToken' => $refreshToken->getRefreshToken(),
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    private function getCredentials(Request $request): array
    {
        $decodeRequest = json_decode($request->getContent());
        if (isset($decodeRequest->emailToken) && !empty($decodeRequest->emailToken)) {
            return [
                'email' => $decodeRequest->email,
                'emailToken' => $decodeRequest->emailToken,
            ];
        }
        if (isset($decodeRequest->passwordToken) && !empty($decodeRequest->passwordToken)) {
            return ['passwordToken' => $decodeRequest->passwordToken];
        }

        return [];
    }
}
