<?php

namespace App\User\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A delegate authentication .
 *
 * @ORM\Entity
 * @ORM\Table(name="user__delegate_authentication")
 * @ORM\HasLifecycleCallbacks
 */
class AuthenticationDelegation
{
    /**
     * @var int the id of this user
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $userByDelegation;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $delegateUser;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
     */
    private $delegationDate;

    public function __construct(User $userByDelegation, User $delegateUser)
    {
        $this->setUserByDelegation($userByDelegation);
        $this->setDelegateUser($delegateUser);
    }

    /**
     * Get the id of this user.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of user.
     */
    public function getUserByDelegation(): User
    {
        return $this->userByDelegation;
    }

    /**
     * Set the value of user.
     *
     * @param mixed $user
     */
    public function setUserByDelegation(User $userByDelegation): self
    {
        $this->userByDelegation = $userByDelegation;

        return $this;
    }

    /**
     * Get the value of delegateUser.
     */
    public function getDelegateUser(): User
    {
        return $this->delegateUser;
    }

    /**
     * Set the value of delegateUser.
     *
     * @param mixed $delegateUser
     */
    public function setDelegateUser($delegateUser): self
    {
        $this->delegateUser = $delegateUser;

        return $this;
    }

    /**
     * Get the value of delegationDate.
     */
    public function getDelegationDate(): ?\DateTime
    {
        return $this->delegationDate;
    }

    /**
     * Set the value of delegationDate.
     *
     * @ORM\PrePersist
     */
    public function setDelegationDate(): self
    {
        $this->delegationDate = new \DateTime('now');

        return $this;
    }
}
