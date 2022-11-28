<?php

namespace App\Incentive\Entity;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $authorizationCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $refreshToken;

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

    public function __construct(User $user, string $authorizationCode)
    {
        $this->setUser($user);
        $this->setAuthorizationCode($authorizationCode);
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
     * Get the value of createdAt.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Get the value of updatedAt.
     *
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }
}
