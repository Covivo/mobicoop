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
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\Mass;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ask;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use DateTime;

/**
 * A user.
 */
class User implements ResourceInterface, UserInterface, EquatableInterface, \JsonSerializable
{
    const MAX_DEVIATION_TIME = 600;
    const MAX_DEVIATION_DISTANCE = 10000;
    
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;
    const STATUS_ANONYMIZED = 3;

    const GENDER_FEMALE = 1;
    const GENDER_MALE = 2;
    const GENDER_OTHER = 3;

    const GENDERS = [
        'gender.choice.female'  => self::GENDER_FEMALE,
        'gender.choice.male'    => self::GENDER_MALE,
        'gender.choice.nc'      => self::GENDER_OTHER
    ];

    const HOME_ADDRESS_NAME = 'homeAddress';
    
    /**
     * @var int The id of this user.
     */
    private $id;
    
    /**
     * @var string|null The iri of this user.
     *
     * @Groups({"post","put","password"})
     */
    private $iri;
    
    /**
     * @var int User status (1 = active; 2 = disabled; 3 = anonymized).
     */
    private $status;
    
    /**
     * @var string|null The first name of the user.
     *
     * @Groups({"get","post","put"})
     */
    private $givenName;
    
    /**
     * @var string|null The family name of the user.
     *
     * @Groups({"post","put"})
     */
    private $familyName;
    
    /**
     * @var string The email of the user.
     *
     * @Groups({"post","put"})
     *
     * @Assert\NotBlank(groups={"signUp","update"})
     * @Assert\Email()
     */
    private $email;
    
    /**
     * @var string|null The encoded password of the user.
     *
     * @Groups({"post","put","password"})
     *
     * @Assert\NotBlank(groups={"signUp","password"})
     */
    private $password;
    
    /**
     * @var int|null The gender of the user.
     *
     * @Groups({"post","put"})
     */
    private $gender;
    
    /**
     * @var string|null The nationality of the user.
     *
     * @Groups({"post","put"})
     */
    private $nationality;
    
    /**
     * @var \DateTimeInterface|null The birth date of the user.
     *
     * @Groups({"post","put"})
     *
     */
    private $birthDate;
    
    /**
     * @var string|null The telephone number of the user.
     *
     * @Groups({"post","put"})
     */
    private $telephone;
    
    /**
     * @var int|null The maximum deviation time (in seconds) as a driver to accept a request proposal.
     *
     * @Groups({"post","put"})
     */
    private $maxDeviationTime;
    
    /**
     * @var int|null The maximum deviation distance (in metres) as a driver to accept a request proposal.
     *
     * @Groups({"post","put"})
     */
    private $maxDeviationDistance;
    
    /**
     * @var boolean The user accepts any route as a passenger from its origin to the destination.
     *
     * @Groups({"post","put"})
     */
    private $anyRouteAsPassenger;

    /**
     * @var int|null Smoking preferences.
     * 0 = i don't smoke
     * 1 = i don't smoke in car
     * 2 = i smoke
     *
     * @Groups({"post","put"})
     */
    private $smoke;

    /**
     * @var boolean|null Music preferences.
     * 0 = no music
     * 1 = i listen to music or radio
     *
     * @Groups({"post","put"})
     */
    private $music;

    /**
     * @var string|null Music favorites.
     *
     * @Groups({"post","put"})
     */
    private $musicFavorites;

    /**
     * @var boolean|null Chat preferences.
     * 0 = no chat
     * 1 = chat
     *
     * @Groups({"post","put"})
     */
    private $chat;

    /**
     * @var string|null Chat favorite subjects.
     *
     * @Groups({"post","put"})
     */
    private $chatFavorites;

    /**
     * @var boolean|null The user accepts to receive news about the platform.
     *
     *@Groups({"post","put"})
     */
    private $newsSubscription;

    /**
     * @var \DateTimeInterface Validation date of the user.
     *
     * @Groups({"post","put"})
     */
    private $validatedDate;

    /**
     * @var string|null Token for account validation by email
     *
     * @Groups({"post","put"})
     */
    private $validatedDateToken;
    
    /**
     * @var boolean The user accepts any transportation mode.
     *
     * @Groups({"post","put"})
     */
    private $multiTransportMode;
    
    /**
     * @var Address[]|null A user may have many addresses.
     *
     * @Groups({"post","put"})
     */
    private $addresses;
    
    /**
     * @var Car[]|null A user may have many cars.
     */
    private $cars;

    /**
     * @var Proposal[]|null The proposals made by this user.
     */
    private $proposals;

    /**
     * @var Ask[]|null The asks made by this user.
     */
    private $asks;

    /**
    * @var array|null User notification alert preferences.
    * @Groups({"put"})
    */
    private $alerts;

    /**
     * @var int|null The birth year of the user.
     */
    private $birthYear;

    /**
     * @var int Validation of conditions
     * @Assert\NotBlank(groups={"signUp"})
     */
    private $conditions;

    /**
     * @var Mass[]|null The mass import files of the user.
     */
    private $masses;

    /**
     * @var Address[]|null A user have only one homeAddress.
     */
    private $homeAddress;

    /**
     * @var string|null Token for password modification.
     *  @Groups({"post","put", "password_token"})
     */
    private $pwdToken;

    /**
     * @var DateTime|null Date of token password modification.
     * @Groups({"post","put", "password_token"})
     */
    private $pwdTokenDate;

    /**
     * @var string|null Token for geographic authorization.
     *  @Groups({"post","put"})
     */
    private $geoToken;

    /**
     * @var string|null Token for phone validation.
     * @Groups({"post","put"})
     */
    private $phoneToken;

    /**
     * @var string|null iOS app ID.
     * @Groups({"post","put"})
     */
    private $iosAppId;

    /**
     * @var string|null Android app ID.
     * @Groups({"post","put"})
     */
    private $androidAppId;
 
    /**
     * Language de l'utilisateur.
     *
     * @var string $language
     * @Groups({"put","post", "api"})
     */
    private $language= 'fr_FR';
        
    /**
     * @var string|null Facebook ID of the user
     * @Groups({"post"})
     */
    private $facebookId;

    public function __construct($id=null, $status=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/users/".$id);
        }
        $this->addresses = new ArrayCollection();
        $this->cars = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->asks = new ArrayCollection();
        $this->masses = new ArrayCollection();
        $this->userNotifications = new ArrayCollection();
        if (is_null($status)) {
            $status = self::STATUS_ACTIVE;
        }
        $this->setStatus($status);
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
    
    public function getMaxDeviationTime(): int
    {
        return (!is_null($this->maxDeviationTime) ? $this->maxDeviationTime : self::MAX_DEVIATION_TIME);
    }
    
    public function setMaxDeviationTime(?int $maxDeviationTime): self
    {
        $this->maxDeviationTime = $maxDeviationTime;
        
        return $this;
    }
    
    public function getMaxDeviationDistance(): int
    {
        return (!is_null($this->maxDeviationDistance) ? $this->maxDeviationDistance : self::MAX_DEVIATION_DISTANCE);
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
    
    public function getValidatedDateToken(): ?string
    {
        return $this->validatedDateToken;
    }

    public function setValidatedDateToken(?string $validatedDateToken): self
    {
        $this->validatedDateToken = $validatedDateToken;
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
    
    public function getAsks(): Collection
    {
        return $this->asks;
    }
    
    public function addAsk(Ask $ask): self
    {
        if (!$this->asks->contains($ask)) {
            $this->asks->add($ask);
            $ask->setUser($this);
        }
        
        return $this;
    }
    
    public function removeAsk(Ask $ask): self
    {
        if ($this->asks->contains($ask)) {
            $this->asks->removeElement($ask);
            // set the owning side to null (unless already changed)
            if ($ask->getUser() === $this) {
                $ask->setUser(null);
            }
        }
        
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
        return $this->birthDate ? (int)$this->birthDate->format('Y') : null;
    }

    public function setBirthYear(?int $birthYear)
    {
        $this->birthYear = $birthYear;
        $this->birthDate = DateTime::createFromFormat('Y-m-d', $birthYear . '-1-1');
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
        return ($this->getGender() ? array_search($this->getGender(), self::GENDERS) : null);
    }



    public function getRoles()
    {
        return array('ROLE_USER');
    }


    public function getSalt()
    {
        return  null;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
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
     * @param Address[]|null $homeAddress
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
     * @param string|null $token
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
     *
     * @param DateTime|null $pwdTokenDate
     */
    public function setPwdTokenDate(?DateTime $pwdTokenDate)
    {
        $this->pwdTokenDate = $pwdTokenDate;
        return $this;
    }

    public function getGeoToken()
    {
        return $this->geoToken;
    }

    public function setGeoToken(?string $geoToken)
    {
        $this->geoToken = $geoToken;
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

    public function getIosAppId(): ?string
    {
        return $this->iosAppId;
    }

    public function setIosAppId(?string $iosAppId): self
    {
        $this->iosAppId = $iosAppId;
        return $this;
    }

    public function getAndroidAppId(): ?string
    {
        return $this->androidAppId;
    }

    public function setAndroidAppId(?string $androidAppId): self
    {
        $this->androidAppId = $androidAppId;
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

    /**
     * get the native language of the client.
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }
        
    /**
     * Set the native language of the client.
     *
     * @param string $language
     */
    public function setLanguage(string $language)
    {
        $this->language = $language;
    }
    
    // If you want more info from user you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        return
        [
            'id'             => $this->getId(),
            'givenName'      => $this->getGivenName(),
            'familyName'     => $this->getFamilyName(),
            'gender'         => $this->getGender(),
            'status'         => $this->getStatus(),
            'email'          => $this->getEmail(),
            'telephone'      => $this->getTelephone(),
            'geoToken'       => $this->getGeoToken(),
            'birthYear'      => $this->getBirthYear(),
            'homeAddress'    => $this->getHomeAddress(),
            'smoke'          => $this->getSmoke(),
            'chat'           => $this->hasChat(),
            'chatFavorites'  => $this->getChatFavorites(),
            'music'          => $this->hasMusic(),
            'musicFavorites' => $this->getMusicFavorites()
        ];
    }
}
