<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\Community\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use App\User\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Community\Controller\JoinAction;
use App\Community\Controller\LeaveCommunityAction;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * A member of a community.
 * Additional properties could be added so we need this entity (could be useless without extra properties => if so it would be a 'classic' manytomany relation)
 *
 * @ORM\Entity
 * @UniqueEntity(
 *     fields={"community", "user"},
 *     errorPath="user",
 *     message="This user already asked to join this community."
 * )
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readCommunityUser"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security_post_denormalize"="is_granted('community_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Communities"}
 *              }
 *          },
 *          "post"={
 *              "security_post_denormalize"="is_granted('community_join',object)",
 *              "swagger_context" = {
 *                  "tags"={"Communities"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/communities/{id}/members",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_community_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/community_members",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_community_membership',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_associate_campaign"={
 *              "path"="/admin/community_members/associate-campaign",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_community_membership',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_send_campaign"={
 *              "path"="/admin/community_members/send-campaign",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_community_membership',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "add"={
 *              "method"="POST",
 *              "path"="/community_users/add",
 *              "security_post_denormalize"="is_granted('community_membership',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Communities"}
 *              }
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "security"="is_granted('community_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Communities"}
 *              }
 *          },
 *          "delete"={
 *              "controller"=LeaveCommunityAction::class,
 *              "swagger_context" = {
 *                  "tags"={"Communities"}
 *              }
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/community_members/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_community_member_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      }
 * )
 * @ApiFilter(NumericFilter::class, properties={"user.id","community.id","status"})
 * @ApiFilter(SearchFilter::class, properties={"community":"exact","user":"exact"})
 * @ApiFilter(OrderFilter::class, properties={"id","status","givenName","familyName","user.givenName","acceptedDate","createdDate","refusedDate"}, arguments={"orderParameterName"="order"})
 */
class CommunityUser
{
    const STATUS_PENDING = 0;
    const STATUS_ACCEPTED_AS_MEMBER = 1;
    const STATUS_ACCEPTED_AS_MODERATOR = 2;
    const STATUS_REFUSED = 3;

    /**
     * @var int The id of this community user.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aRead","readCommunity","readCommunityUser","readUserAdmin","readUser"})
     */
    private $id;

    /**
     * @var Community The community.
     *
     * @ApiProperty(push=true)
     * @ORM\ManyToOne(targetEntity="\App\Community\Entity\Community", inversedBy="communityUsers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"aWrite","readCommunity","readCommunityUser","write","results","existsCommunity","communities","readCommunityPublic","readUserAdmin"})
     * @MaxDepth(1)
     * @Assert\NotBlank
     */
    private $community;

    /**
     * @var User The user.
     *
     * @ApiProperty(push=true)
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"aWrite","readCommunity","readCommunityUser","write","results","existsCommunity","communities","readUserAdmin"})
     * @MaxDepth(1)
     * @Assert\NotBlank
     */
    private $user;

    /**
     * @var int The status of the membership.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","aWrite","readCommunity","readCommunityUser","write","readUserAdmin"})
     */
    private $status;

    /**
     * @var User The user that validates/invalidates the registration.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User")
     * @Groups({"readCommunityUser","write"})
     * @MaxDepth(1)
     */
    private $admin;

    /**
    * @var \DateTimeInterface Creation date of the community user.
    *
    * @ORM\Column(type="datetime")
    * @Groups({"aRead","readCommunityUser","write"})
    */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the community user.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
    * @var \DateTimeInterface Accepted date.
    *
    * @ORM\Column(type="datetime", nullable=true)
    * @Groups({"aRead","readCommunity","readCommunityUser","write"})
    */
    private $acceptedDate;

    /**
    * @var \DateTimeInterface Refusal date.
    *
    * @ORM\Column(type="datetime", nullable=true)
    * @Groups({"aRead","readCommunityUser","write"})
    */
    private $refusedDate;

    /**
     * @var string The login to join the community if the community is secured.
     * @Groups("write")
     */
    private $login;

    /**
     * @var string The password to join the community if the community is secured.
     * @Groups("write")
     */
    private $password;

    /**
     * @var boolean If the user is also the creator of the community.
     * @Groups("readCommunityUser")
     */
    private $creator;

    /**
     * @var string The username of the member
     * @Groups("aRead")
     */
    private $username;

    /**
     * @var string The givenName of the member
     * @Groups("aRead")
     */
    private $givenName;

    /**
     * @var string The familyName of the member
     * @Groups("aRead")
     */
    private $familyName;

    /**
     * @var string|null The member avatar
     * @Groups({"aRead"})
     */
    private $avatar;

    /**
     * @var bool The member accepts emailing
     * @Groups("aRead")
     */
    private $newsSubscription;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    public function setCommunity(?Community $community): self
    {
        $this->community = $community;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status)
    {
        $this->status = $status;
    }

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(?User $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    public function getAcceptedDate(): ?\DateTimeInterface
    {
        return $this->acceptedDate;
    }

    public function setAcceptedDate(?\DateTimeInterface $acceptedDate): self
    {
        $this->acceptedDate = $acceptedDate;

        return $this;
    }

    public function getRefusedDate(): ?\DateTimeInterface
    {
        return $this->refusedDate;
    }

    public function setRefusedDate(?\DateTimeInterface $refusedDate): self
    {
        $this->refusedDate = $refusedDate;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(?string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function isCreator(): ?bool
    {
        return $this->creator;
    }

    public function setCreator(?bool $isCreator): self
    {
        $this->creator = boolval($isCreator);

        return $this;
    }

    public function getUsername(): ?string
    {
        return ucfirst(strtolower($this->getUser()->getGivenName())) . " " . $this->getUser()->getShortFamilyName();
    }

    public function getGivenName(): ?string
    {
        return ucfirst(strtolower($this->getUser()->getGivenName()));
    }
    
    public function getFamilyName(): ?string
    {
        return ucfirst(strtolower($this->getUser()->getFamilyName()));
    }

    public function hasNewsSubscription()
    {
        return $this->getUser()->hasNewsSubscription();
    }

    public function getAvatar(): ?string
    {
        if (count($this->getUser()->getAvatars())>0) {
            return $this->getUser()->getAvatars()[0];
        }
        return null;
    }

    // DOCTRINE EVENTS

    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \Datetime());
        $this->setAutoAcceptedOrRefusedDate();
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }

    /**
     * Default status.
     *
     * @ORM\PrePersist
     */
    public function setAutoStatus()
    {
        if ($this->getUser()->getId() == $this->getCommunity()->getUser()->getId()) {
            $this->setStatus(self::STATUS_ACCEPTED_AS_MODERATOR);
        } elseif ($this->getStatus() != self::STATUS_ACCEPTED_AS_MODERATOR && $this->getCommunity()->getValidationType() != Community::MANUAL_VALIDATION) {
            $this->setStatus(self::STATUS_ACCEPTED_AS_MEMBER);
        } elseif ($this->getStatus() != self::STATUS_ACCEPTED_AS_MODERATOR) {
            $this->setStatus(self::STATUS_PENDING);
        }
        $this->setAutoAcceptedOrRefusedDate();
    }

    /**
     * Accepted / refused date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoAcceptedOrRefusedDate()
    {
        if ($this->status == self::STATUS_ACCEPTED_AS_MEMBER && is_null($this->acceptedDate)) {
            $this->setAcceptedDate(new \Datetime());
            $this->setRefusedDate(null);
        } elseif ($this->status == self::STATUS_ACCEPTED_AS_MODERATOR && is_null($this->acceptedDate)) {
            $this->setAcceptedDate(new \Datetime());
            $this->setRefusedDate(null);
        } elseif ($this->status == self::STATUS_REFUSED && is_null($this->refusedDate)) {
            $this->setRefusedDate(new \Datetime());
            $this->setAcceptedDate(null);
        } elseif ($this->status == self::STATUS_PENDING) {
            $this->setAcceptedDate(null);
            $this->setRefusedDate(null);
        }
    }
}
