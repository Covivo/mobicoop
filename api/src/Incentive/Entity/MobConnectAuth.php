<?php

namespace App\Incentive\Entity;

use App\DataProvider\Entity\MobConnect\OpenIdSsoProvider;
use App\User\Entity\SsoUser;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Mob Connect user authentication.
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="mobconnect__auth")
 *
 * @ORM\HasLifecycleCallbacks
 */
class MobConnectAuth
{
    /**
     * @var int The user subscription ID
     *
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"readAdminSubscription"})
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="\App\User\Entity\User", inversedBy="mobConnectAuth")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"readAdminSubscription"})
     */
    private $accessToken;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"readAdminSubscription"})
     */
    private $accessTokenExpiresDate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $authorizationCode;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"readAdminSubscription"})
     */
    private $refreshToken;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"readAdminSubscription"})
     */
    private $refreshTokenExpiresDate;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $updatedAt;

    public function __construct(User $user, SsoUser $ssoUser)
    {
        $this->setUser($user);
        $this->setAuthorizationCode($user->getSsoAccount(OpenIdSsoProvider::SSO_PROVIDER_MOBCONNECT)->getSsoId());
        $this->setAccessToken($ssoUser->getAccessToken());
        $this->setAccessTokenExpiresDate($ssoUser->getAccessTokenExpiresDuration());
        $this->setRefreshToken($ssoUser->getRefreshToken());
        $this->setRefreshTokenExpiresDate($ssoUser->getRefreshTokenExpiresDuration());
    }

    public function updateTokens(array $tokens)
    {
        $this->setAccessToken($tokens['access_token']);
        $this->setAccessTokenExpiresDate($tokens['expires_in']);
        $this->setRefreshToken($tokens['refresh_token']);
        $this->setRefreshTokenExpiresDate($tokens['refresh_expires_in']);
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now');
    }

    /**
     * @ORM\PrePersist
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Get the user subscription ID.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of user.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set the value of user.
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of accessToken.
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Set the value of accessToken.
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get the value of accessTokenExpiresDate.
     */
    public function getAccessTokenExpiresDate(): ?\DateTime
    {
        return $this->accessTokenExpiresDate;
    }

    /**
     * Set the value of accessTokenExpiresDate.
     */
    public function setAccessTokenExpiresDate(int $accessTokenExpiresDuration): self
    {
        $this->accessTokenExpiresDate = $this->getExpirationDateFromDuration($accessTokenExpiresDuration);

        return $this;
    }

    /**
     * Get the value of authorizationCode.
     */
    public function getAuthorizationCode(): string
    {
        return $this->authorizationCode;
    }

    /**
     * Set the value of authorizationCode.
     */
    public function setAuthorizationCode(string $authorizationCode): self
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    /**
     * Get the value of refreshToken.
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * Set the value of refreshToken.
     */
    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Get the value of refreshTokenExpiresDate.
     */
    public function getRefreshTokenExpiresDate(): \DateTime
    {
        return $this->refreshTokenExpiresDate;
    }

    public function hasAuthenticationExpired(): bool
    {
        return $this->getRefreshTokenExpiresDate() < new \DateTime('now');
    }

    /**
     * Set the value of refreshTokenExpiresDate.
     */
    public function setRefreshTokenExpiresDate(int $refreshTokenExpiresDuration): self
    {
        $this->refreshTokenExpiresDate = $this->getExpirationDateFromDuration($refreshTokenExpiresDuration);

        return $this;
    }

    /**
     * Get the value of createdAt.
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Get the value of updatedAt.
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Returns if it is possible to get an access_token from the refresh_token.
     */
    public function isValid(): bool
    {
        return
            !is_null($this->getRefreshToken())
            && !is_null($this->getRefreshTokenExpiresDate())
            && $this->getRefreshTokenExpiresDate() > new \DateTime();
    }

    private function getExpirationDateFromDuration(int $duration): \DateTime
    {
        $now = new \DateTime('now');
        $expirationDate = clone $now;

        if ($duration > 0) {
            $duration -= 15;

            return $expirationDate->add(new \DateInterval('PT'.$duration.'S'));
        }

        return $expirationDate->add(new \DateInterval('P2Y'));
    }
}
