<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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
 */

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Gamification\Entity\GamificationEntity;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\I18n\Entity\Language;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\Mass;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A user.
 */
class User extends GamificationEntity implements ResourceInterface, UserInterface, EquatableInterface, \JsonSerializable
{
    public const MAX_DEVIATION_TIME = 600;
    public const MAX_DEVIATION_DISTANCE = 10000;

    public const STATUS_ACTIVE = 1;
    public const STATUS_DISABLED = 2;
    public const STATUS_ANONYMIZED = 3;

    public const GENDER_FEMALE = 1;
    public const GENDER_MALE = 2;
    public const GENDER_OTHER = 3;

    public const GENDERS = [
        'gender.choice.female' => self::GENDER_FEMALE,
        'gender.choice.male' => self::GENDER_MALE,
        'gender.choice.nc' => self::GENDER_OTHER,
    ];

    public const PHONE_DISPLAY_RESTRICTED = 1;
    public const PHONE_DISPLAY_ALL = 2;

    public const HOME_ADDRESS_NAME = 'homeAddress';

    /**
     * @var int the id of this user
     */
    private $id;

    /**
     * @var null|string the iri of this user
     *
     * @Groups({"post","put","password"})
     */
    private $iri;

    /**
     * @var int user status (1 = active; 2 = disabled; 3 = anonymized)
     */
    private $status;

    /**
     * @var null|string the first name of the user
     *
     * @Groups({"get","post","put"})
     */
    private $givenName;

    /**
     * @var null|string the family name of the user
     *
     * @Groups({"post","put"})
     */
    private $familyName;

    /**
     * @var null|string the shorten family name of the user
     */
    private $shortFamilyName;

    /**
     * @var string the email of the user
     *
     * @Groups({"post","put","checkValidationToken","passwordUpdateRequest"})
     *
     * @Assert\NotBlank(groups={"signUp","update"})
     * @Assert\Email()
     */
    private $email;

    /**
     * @var null|string the encoded password of the user
     *
     * @Groups({"post","put","password","passwordUpdate"})
     *
     * @Assert\NotBlank(groups={"signUp","password"})
     */
    private $password;

    /**
     * @var null|int the gender of the user
     *
     * @Groups({"post","put"})
     */
    private $gender;

    /**
     * @var null|string the nationality of the user
     *
     * @Groups({"post","put"})
     */
    private $nationality;

    /**
     * @var null|\DateTimeInterface the birth date of the user
     *
     * @Groups({"post","put"})
     */
    private $birthDate;

    /**
     * @var null|string the telephone number of the user
     *
     * @Groups({"post","put","checkPhoneToken"})
     */
    private $telephone;

    /**
     * @var int phone display configuration (1 = restricted; 2 = all)
     *
     * @Assert\NotBlank
     * @Groups({"post","put"})
     */
    private $phoneDisplay;

    /**
     * @var null|int the maximum deviation time (in seconds) as a driver to accept a request proposal
     *
     * @Groups({"post","put"})
     */
    private $maxDeviationTime;

    /**
     * @var null|int the maximum deviation distance (in metres) as a driver to accept a request proposal
     *
     * @Groups({"post","put"})
     */
    private $maxDeviationDistance;

    /**
     * @var bool the user accepts any route as a passenger from its origin to the destination
     *
     * @Groups({"post","put"})
     */
    private $anyRouteAsPassenger;

    /**
     * @var null|int Smoking preferences.
     *               0 = i don't smoke
     *               1 = i don't smoke in car
     *               2 = i smoke
     *
     * @Groups({"post","put"})
     */
    private $smoke;

    /**
     * @var null|bool Music preferences.
     *                0 = no music
     *                1 = i listen to music or radio
     *
     * @Groups({"post","put"})
     */
    private $music;

    /**
     * @var null|string music favorites
     *
     * @Groups({"post","put"})
     */
    private $musicFavorites;

    /**
     * @var null|bool Chat preferences.
     *                0 = no chat
     *                1 = chat
     *
     * @Groups({"post","put"})
     */
    private $chat;

    /**
     * @var null|bool Gamification preferences.
     *                0 = no Gamification tracking
     *                1 = Gamification tracking
     *
     * @Groups({"post","put"})
     */
    private $gamification;

    /**
     * @var null|string chat favorite subjects
     *
     * @Groups({"post","put"})
     */
    private $chatFavorites;

    /**
     * @var null|bool the user accepts to receive news about the platform
     *
     *@Groups({"post","put"})
     */
    private $newsSubscription;

    /**
     * @var \DateTimeInterface validation date of the user
     *
     * @Groups({"post","put"})
     */
    private $validatedDate;

    /**
     * @var null|string Token for account validation by email
     *
     * @Groups({"post","put","checkValidationToken"})
     */
    private $emailToken;

    /**
     * @var bool the user accepts any transportation mode
     *
     * @Groups({"post","put"})
     */
    private $multiTransportMode;

    /**
     * @var null|Address[] a user may have many addresses
     *
     * @Groups({"post","put"})
     */
    private $addresses;

    /**
     * @var null|Car[] a user may have many cars
     */
    private $cars;

    /**
     * @var null|Proposal[] the proposals made by this user
     */
    private $proposals;

    /**
     * @var null|Image[] the images of the user
     *
     * @Groups({"post","put"})
     */
    private $images;

    /**
     * @var null|array the images of the user
     */
    private $avatars;

    /**
     * @var null|array the images of the user
     */
    private $avatar;

    /**
     * @var null|array user notification alert preferences
     * @Groups({"put"})
     */
    private $alerts;

    /**
     * @var null|int the birth year of the user
     */
    private $birthYear;

    /**
     * @var int Validation of conditions
     * @Assert\NotBlank(groups={"signUp"})
     */
    private $conditions;

    /**
     * @var null|Mass[] the mass import files of the user
     */
    private $masses;

    /**
     * @var null|Address[] a user have only one homeAddress
     */
    private $homeAddress;

    /**
     * @var null|string token for password modification
     * @Groups({"post","put", "password_token", "passwordUpdate"})
     */
    private $pwdToken;

    /**
     * @var null|DateTime date of token password modification
     * @Groups({"post","put", "password_token"})
     */
    private $pwdTokenDate;

    /**
     * @var null|string token for direct api auth
     * @Groups({"post","put"})
     */
    private $token;

    /**
     * @var null|string token for phone validation
     * @Groups({"post","put","checkPhoneToken"})
     */
    private $phoneToken;

    /**
     * @var null|\DateTimeInterface validation date of the phone number
     * @Groups({"post","put"})
     */
    private $phoneValidatedDate;

    /**
     * @var null|bool mobile user
     * @Groups({"post","put"})
     */
    private $mobile;

    /**
     * @var null|Language the language of the user
     *
     * @Groups({"post","api","language"})
     */
    private $language;

    /**
     * @var null|string Facebook ID of the user
     * @Groups({"post"})
     */
    private $facebookId;

    /**
     * @var null|int Community choose by a user
     * @Groups({"post"})
     */
    private $communityId;

    /**
     * @var null|string the unsubscribe message we return by api
     * @Groups({"post","put"})
     */
    private $unsubscribeMessage;

    /**
     * @var null|bool used for community member list to know who is the referrer
     */
    private $isCommunityReferrer;

    /**
     * @var null|bool used for community member list to know who is the referrer
     */
    private $isCommunityModerator;

    /**
     * @var null|array BankAccounts of a User
     */
    private $bankAccounts;

    /**
     * @var null|array Wallets of a User
     */
    private $wallets;

    /**
     * @var null|string CarpoolExport of a User
     */
    private $carpoolExport;

    /**
     * @var null|bool If the User can receive a review from the current User (used in Carpool Results)
     */
    private $canReceiveReview;

    /**
     * @var null|bool If the Reviews are enable on this instance
     */
    private $userReviewsActive;

    /**
     * @var null|bool If the User is an experienced carpooler
     */
    private $experienced;

    /**
     * @var null|int Number of unread carpool messages
     */
    private $unreadCarpoolMessageNumber;

    /**
     * @var null|int Number of unread direct messages
     */
    private $unreadDirectMessageNumber;

    /**
     * @var null|int Number of unread solidary messages
     */
    private $unreadSolidaryMessageNumber;

    /**
     * @var null|int The savedCo2 of this user in grams
     */
    private $savedCo2;

    /**
     * @var null|int Number of badges earned by the user
     */
    private $numberOfBadges;

    public function __construct($id = null, $status = null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri('/users/'.$id);
        }
        $this->addresses = new ArrayCollection();
        $this->cars = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->asks = new ArrayCollection();
        $this->asksRelated = new ArrayCollection();
        $this->masses = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->userNotifications = new ArrayCollection();
        if (is_null($status)) {
            $status = self::STATUS_ACTIVE;
        }
        $this->setStatus($status);
        $this->unreadMessageNumber = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getIri()
    {
        return $this->iri;
    }

    public function setIri($iri)
    {
        $this->iri = $iri;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getShortFamilyName(): ?string
    {
        return $this->shortFamilyName;
    }

    public function setShortFamilyName(?string $shortFamilyName): self
    {
        $this->shortFamilyName = $shortFamilyName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

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

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(?int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPhoneDisplay(): ?int
    {
        return $this->phoneDisplay;
    }

    public function setPhoneDisplay(?int $phoneDisplay): self
    {
        $this->phoneDisplay = $phoneDisplay;

        return $this;
    }

    public function getMaxDeviationTime(): int
    {
        return !is_null($this->maxDeviationTime) ? $this->maxDeviationTime : self::MAX_DEVIATION_TIME;
    }

    public function setMaxDeviationTime(?int $maxDeviationTime): self
    {
        $this->maxDeviationTime = $maxDeviationTime;

        return $this;
    }

    public function getMaxDeviationDistance(): int
    {
        return !is_null($this->maxDeviationDistance) ? $this->maxDeviationDistance : self::MAX_DEVIATION_DISTANCE;
    }

    public function setMaxDeviationDistance(?int $maxDeviationDistance): self
    {
        $this->maxDeviationDistance = $maxDeviationDistance;

        return $this;
    }

    public function getAnyRouteAsPassenger(): bool
    {
        return !is_null($this->anyRouteAsPassenger) ? $this->anyRouteAsPassenger : false;
    }

    public function setAnyRouteAsPassenger(bool $anyRouteAsPassenger): self
    {
        $this->anyRouteAsPassenger = $anyRouteAsPassenger;

        return $this;
    }

    public function getSmoke(): ?int
    {
        return $this->smoke;
    }

    public function setSmoke(?int $smoke): self
    {
        $this->smoke = $smoke;

        return $this;
    }

    public function hasMusic(): ?bool
    {
        return $this->music;
    }

    public function setMusic(?bool $music): self
    {
        $this->music = $music;

        return $this;
    }

    public function getMusicFavorites(): ?string
    {
        return $this->musicFavorites;
    }

    public function setMusicFavorites(?string $musicFavorites): self
    {
        $this->musicFavorites = $musicFavorites;

        return $this;
    }

    public function hasChat(): ?bool
    {
        return $this->chat;
    }

    public function setChat(?bool $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getChatFavorites(): ?string
    {
        return $this->chatFavorites;
    }

    public function setChatFavorites(?string $chatFavorites): self
    {
        $this->chatFavorites = $chatFavorites;

        return $this;
    }

    public function hasGamification(): ?bool
    {
        return $this->gamification;
    }

    public function setGamification(?bool $gamification): self
    {
        $this->gamification = $gamification;

        return $this;
    }

    public function hasNewsSubscription(): ?bool
    {
        return $this->newsSubscription;
    }

    public function setNewsSubscription(?bool $newsSubscription): self
    {
        $this->newsSubscription = $newsSubscription;

        return $this;
    }

    public function getValidatedDate(): ?\DateTimeInterface
    {
        return $this->validatedDate;
    }

    public function setValidatedDate(\DateTimeInterface $validatedDate): self
    {
        $this->validatedDate = $validatedDate;

        return $this;
    }

    public function getEmailToken(): ?string
    {
        return $this->emailToken;
    }

    public function setEmailToken(?string $emailToken): self
    {
        $this->emailToken = $emailToken;

        return $this;
    }

    public function getMultiTransportMode(): bool
    {
        return !is_null($this->multiTransportMode) ? $this->multiTransportMode : false;
    }

    public function setMultiTransportMode(bool $multiTransportMode): self
    {
        $this->multiTransportMode = $multiTransportMode;

        return $this;
    }

    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setUser($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            // set the owning side to null (unless already changed)
            if ($address->getUser() === $this) {
                $address->setUser(null);
            }
        }

        return $this;
    }

    public function getCars(): Collection
    {
        return $this->cars;
    }

    public function addCar(Car $car): self
    {
        if (!$this->cars->contains($car)) {
            $this->cars->add($car);
            $car->setUser($this);
        }

        return $this;
    }

    public function removeCar(Car $car): self
    {
        if ($this->cars->contains($car)) {
            $this->cars->removeElement($car);
            // set the owning side to null (unless already changed)
            if ($car->getUser() === $this) {
                $car->setUser(null);
            }
        }

        return $this;
    }

    public function getProposals(): Collection
    {
        return $this->proposals;
    }

    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals->add($proposal);
            $proposal->setUser($this);
        }

        return $this;
    }

    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
            // set the owning side to null (unless already changed)
            if ($proposal->getUser() === $this) {
                $proposal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages()
    {
        return $this->images->getValues();
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setUser($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getUser() === $this) {
                $image->setUser(null);
            }
        }

        return $this;
    }

    public function getAvatars(): ?array
    {
        return $this->avatars;
    }

    public function setAvatars(?array $avatars): self
    {
        $this->avatars = $avatars;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAlerts()
    {
        return $this->alerts;
    }

    public function setAlerts(?array $alerts): self
    {
        $this->alerts = $alerts;

        return $this;
    }

    public function getBirthYear(): ?int
    {
        return $this->birthYear;
    }

    public function setBirthYear(?int $birthYear)
    {
        $this->birthYear = $birthYear;
        //$this->birthDate = DateTime::createFromFormat('Y-m-d', $birthYear . '-1-1');
    }

    public function getConditions(): ?int
    {
        return $this->conditions;
    }

    public function setConditions(?int $conditions)
    {
        $this->conditions = $conditions;
    }

    public function getGenderString(): ?string
    {
        return $this->getGender() ? array_search($this->getGender(), self::GENDERS) : null;
    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->email !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    public function getMasses(): Collection
    {
        return $this->masses;
    }

    public function addMass(Mass $mass): self
    {
        if (!$this->masses->contains($mass)) {
            $this->masses->add($mass);
            $mass->setUser($this);
        }

        return $this;
    }

    public function getHomeAddress(): ?Address
    {
        // return $this->homeAddress;
        foreach ($this->addresses as $address) {
            if ($address->isHome()) {
                return $address;
            }
        }

        return null;
    }

    /**
     * @param null|Address[] $homeAddress
     */
    public function setHomeAddress(?Address $homeAddress)
    {
        $this->homeAddress = $homeAddress;
    }

    /**
     * Return the Token of password mofification.
     *
     * @return string
     */
    public function getPwdToken()
    {
        return $this->pwdToken;
    }

    /**
     * Set the Token of password mofification.
     *
     * @param null|string $pwdtoken
     */
    public function setPwdToken(?string $pwdToken)
    {
        $this->pwdToken = $pwdToken;

        return $this;
    }

    /**
     * Return the date of password mofification.
     *
     * @return DateTime
     */
    public function getPwdTokenDate()
    {
        return $this->pwdTokenDate;
    }

    /**
     * Set the date of password mofification.
     */
    public function setPwdTokenDate(?DateTime $pwdTokenDate)
    {
        $this->pwdTokenDate = $pwdTokenDate;

        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken(?string $token)
    {
        $this->token = $token;

        return $this;
    }

    public function getPhoneToken(): ?string
    {
        return $this->phoneToken;
    }

    public function setPhoneToken(?string $phoneToken): self
    {
        $this->phoneToken = $phoneToken;

        return $this;
    }

    public function getPhoneValidatedDate(): ?\DateTimeInterface
    {
        return $this->phoneValidatedDate;
    }

    public function setPhoneValidatedDate(?\DateTimeInterface $phoneValidatedDate): ?self
    {
        $this->phoneValidatedDate = $phoneValidatedDate;

        return $this;
    }

    public function hasMobile(): ?bool
    {
        return $this->mobile ? true : false;
    }

    public function setMobile(?bool $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    public function setFacebookId(?string $facebookId): self
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    public function getCommunityId(): ?int
    {
        return $this->communityId;
    }

    public function setCommunityId($communityId)
    {
        $this->communityId = $communityId;
    }

    /**
     * get the native language of the client.
     *
     * @return Language
     */
    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    /**
     * Set the native language of the client.
     *
     * @param Language $language
     */
    public function setLanguage(?Language $language)
    {
        $this->language = $language;
    }

    public function getUnsubscribeMessage(): ?string
    {
        return $this->unsubscribeMessage;
    }

    public function setUnsubscribeMessage(?string $unsubscribeMessage): self
    {
        $this->unsubscribeMessage = $unsubscribeMessage;

        return $this;
    }

    public function getIsCommunityReferrer(): ?bool
    {
        return $this->isCommunityReferrer;
    }

    public function setIsCommunityReferrer(?bool $isCommunityReferrer): User
    {
        $this->isCommunityReferrer = $isCommunityReferrer;

        return $this;
    }

    public function getIsCommunityModerator(): ?bool
    {
        return $this->isCommunityModerator;
    }

    public function setIsCommunityModerator(?bool $isCommunityModerator): User
    {
        $this->isCommunityModerator = $isCommunityModerator;

        return $this;
    }

    public function getBankAccounts(): ?array
    {
        return $this->bankAccounts;
    }

    public function setBankAccounts(?array $bankAccounts)
    {
        $this->bankAccounts = $bankAccounts;
    }

    public function getWallets(): ?array
    {
        return $this->wallets;
    }

    public function setWallets(?array $wallets)
    {
        $this->wallets = $wallets;
    }

    public function getCarpoolExport(): ?string
    {
        return $this->carpoolExport;
    }

    public function setCarpoolExport(?string $carpoolExport): self
    {
        $this->carpoolExport = $carpoolExport;

        return $this;
    }

    public function getCanReceiveReview(): ?bool
    {
        return $this->canReceiveReview;
    }

    public function setCanReceiveReview(?bool $canReceiveReview): self
    {
        $this->canReceiveReview = $canReceiveReview;

        return $this;
    }

    public function isUserReviewsActive(): ?bool
    {
        return $this->userReviewsActive;
    }

    public function setUserReviewsActive(?bool $userReviewsActive): self
    {
        $this->userReviewsActive = $userReviewsActive;

        return $this;
    }

    public function isExperienced(): ?bool
    {
        return $this->experienced;
    }

    public function setExperienced(?bool $experienced): self
    {
        $this->experienced = $experienced;

        return $this;
    }

    public function getUnreadCarpoolMessageNumber(): ?int
    {
        return $this->unreadCarpoolMessageNumber;
    }

    public function setUnreadCarpoolMessageNumber(?int $unreadCarpoolMessageNumber): self
    {
        $this->unreadCarpoolMessageNumber = $unreadCarpoolMessageNumber;

        return $this;
    }

    public function getUnreadDirectMessageNumber(): ?int
    {
        return $this->unreadDirectMessageNumber;
    }

    public function setUnreadDirectMessageNumber(?int $unreadDirectMessageNumber): self
    {
        $this->unreadDirectMessageNumber = $unreadDirectMessageNumber;

        return $this;
    }

    public function getUnreadSolidaryMessageNumber(): ?int
    {
        return $this->unreadSolidaryMessageNumber;
    }

    public function setUnreadSolidaryMessageNumber(?int $unreadSolidaryMessageNumber): self
    {
        $this->unreadSolidaryMessageNumber = $unreadSolidaryMessageNumber;

        return $this;
    }

    public function getSavedCo2(): ?int
    {
        return $this->savedCo2;
    }

    public function setSavedCo2(?int $savedCo2): self
    {
        $this->savedCo2 = $savedCo2;

        return $this;
    }

    public function getNumberOfBadges(): ?int
    {
        return $this->numberOfBadges;
    }

    public function setNumberOfBadges(?int $numberOfBadges): self
    {
        $this->numberOfBadges = $numberOfBadges;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    // If you want more info from user you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        $userSerialized = [
            'id' => $this->getId(),
            'givenName' => $this->getGivenName(),
            'familyName' => $this->getFamilyName(),
            'shortFamilyName' => $this->getShortFamilyName(),
            'gender' => $this->getGender(),
            'status' => $this->getStatus(),
            'email' => $this->getEmail(),
            'telephone' => $this->getTelephone(),
            'token' => $this->getToken(),
            'birthYear' => $this->getBirthYear(),
            'birthDate' => $this->getBirthDate(),
            'homeAddress' => $this->getHomeAddress(),
            'images' => $this->getImages(),
            'avatars' => $this->getAvatars(),
            'smoke' => $this->getSmoke(),
            'chat' => $this->hasChat(),
            'chatFavorites' => $this->getChatFavorites(),
            'music' => $this->hasMusic(),
            'musicFavorites' => $this->getMusicFavorites(),
            'gamification' => $this->hasGamification(),
            'newsSubscription' => $this->hasNewsSubscription(),
            'phoneDisplay' => $this->getPhoneDisplay(),
            'phoneValidatedDate' => $this->getPhoneValidatedDate(),
            'phoneToken' => $this->getPhoneToken(),
            'unsubscribeMessage' => $this->getUnsubscribeMessage(),
            'communityId' => $this->getCommunityId(),
            'bankAccounts' => $this->getBankAccounts(),
            'carpoolExport' => $this->getCarpoolExport(),
            'canReceiveReview' => $this->getCanReceiveReview(),
            'userReviewsActive' => $this->isUserReviewsActive(),
            'experienced' => $this->isExperienced(),
            'validatedDate' => $this->getValidatedDate(),
            'unreadCarpoolMessageNumber' => $this->getUnreadCarpoolMessageNumber(),
            'unreadDirectMessageNumber' => $this->getUnreadDirectMessageNumber(),
            'unreadSolidaryMessageNumber' => $this->getUnreadSolidaryMessageNumber(),
            'savedCo2' => $this->getSavedCo2(),
            'language' => $this->getLanguage(),
            'gamificationNotifications' => $this->getGamificationNotifications(),
            'numberOfBadges' => $this->getNumberOfBadges(),
        ];

        if (!is_null($this->getIsCommunityReferrer())) {
            $userSerialized['isCommunityReferrer'] = $this->getIsCommunityReferrer();
        }
        if (!is_null($this->getIsCommunityModerator())) {
            $userSerialized['isCommunityModerator'] = $this->getIsCommunityModerator();
        }

        return $userSerialized;
    }
}
