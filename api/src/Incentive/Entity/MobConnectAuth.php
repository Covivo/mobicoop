<?php

namespace App\Incentive\Entity;

use App\User\Entity\SsoUser;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mob Connect user authentication.
 *
 * @ORM\Entity
 * @ORM\Table(name="mobconnect__auth")
 * @ORM\HasLifecycleCallbacks
 */
class MobConnectAuth
{
    /**
     * @var int The user subscription ID
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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
     */
    private $accessToken;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
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
     */
    private $refreshToken;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
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
        $this->setAuthorizationCode($user->getSsoId());
        $this->setAccessToken($ssoUser->getAccessToken());
        $this->setAccessTokenExpiresDate($ssoUser->getAccessTokenExpiresDuration());
        $this->setRefreshToken($ssoUser->getRefreshToken());
        $this->setRefreshTokenExpiresDate($ssoUser->getRefreshTokenExpiresDuration());
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
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Get the user subscription ID.
     *
     * @return int
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

    private function getExpirationDateFromDuration(int $duration): \DateTime
    {
        $duration += 15;

        $now = new \DateTime('now');
        $expirationDate = clone $now;

        return $expirationDate->sub(new \DateInterval('PT'.$duration.'S'));
    }
}
